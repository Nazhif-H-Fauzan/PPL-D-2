<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Termin;
use Database\Seeders\ModulD2Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModulD2ApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Jalankan seeder modul D-2 sebelum setiap pengujian
        $this->seed(ModulD2Seeder::class);
    }

    protected function validAuthHeaders(string $role = 'petugas_keuangan'): array
    {
        return [
            'Authorization' => 'Bearer emp-demo-012',
            'X-User-Role' => $role,
            'Accept' => 'application/json',
        ];
    }

    // ==========================================
    // 1. PENGUJIAN AUTENTIKASI & OTORISASI (401 & 403)
    // ==========================================

    public function test_unauthenticated_request_returns_401(): void
    {
        // Tanpa header Authorization
        $response = $this->getJson('/api/v1/termins');

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'code' => 'UNAUTHORIZED',
            ]);
    }

    public function test_invalid_bearer_token_returns_401(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid_token',
            'Accept' => 'application/json',
        ])->getJson('/api/v1/termins');

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'code' => 'UNAUTHORIZED',
            ]);
    }

    public function test_forbidden_role_returns_403(): void
    {
        // Bukan petugas keuangan (misal staf_operasional)
        $response = $this->withHeaders($this->validAuthHeaders('staf_operasional'))
            ->getJson('/api/v1/termins');

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'code' => 'FORBIDDEN',
                'details' => [
                    'requiredRole' => 'petugas_keuangan',
                    'currentUserRole' => 'staf_operasional',
                ],
            ]);
    }

    // ==========================================
    // 2. PENGUJIAN ENDPOINT TERMIN (API-K02-02)
    // ==========================================

    public function test_get_termins_success_with_po_status(): void
    {
        $response = $this->withHeaders($this->validAuthHeaders())
            ->getJson('/api/v1/termins');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Daftar termin siap ditagih berhasil dimuat',
            ])
            ->assertJsonStructure([
                'data' => [
                    'items' => [
                        '*' => [
                            'terminId',
                            'terminNumber',
                            'terminValue',
                            'dueDate',
                            'status',
                            'contractId',
                            'contractNumber',
                            'projectId',
                            'poStatus',
                        ],
                    ],
                    'page',
                    'pageSize',
                    'totalItems',
                ],
            ]);

        // Verifikasi pengayaan status PO
        $items = $response->json('data.items');
        $this->assertNotEmpty($items);

        // Cari trm-demo-001 (harus PO_ADA)
        $trm1 = collect($items)->firstWhere('terminId', 'trm-demo-001');
        $this->assertNotNull($trm1);
        $this->assertEquals('PO_ADA', $trm1['poStatus']);

        // Cari trm-demo-003 (harus PO_TIDAK_ADA)
        $trm3 = collect($items)->firstWhere('terminId', 'trm-demo-003');
        $this->assertNotNull($trm3);
        $this->assertEquals('PO_TIDAK_ADA', $trm3['poStatus']);
    }

    public function test_get_termins_validation_error_400(): void
    {
        $response = $this->withHeaders($this->validAuthHeaders())
            ->getJson('/api/v1/termins?pageSize=999');

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'code' => 'VALIDATION_ERROR',
            ]);
    }

    public function test_get_termins_project_not_found_404(): void
    {
        $response = $this->withHeaders($this->validAuthHeaders())
            ->getJson('/api/v1/termins?projectId=prj-non-existent');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'code' => 'PROJECT_NOT_FOUND',
            ]);
    }

    // ==========================================
    // 3. PENGUJIAN PENERBITAN INVOICE (API-K02-03)
    // ==========================================

    public function test_create_invoice_validation_error_400(): void
    {
        // Request dengan DPP negatif dan tanggal kosong
        $payload = [
            'terminId' => 'trm-demo-001',
            'invoiceDate' => '',
            'dppAmount' => '-1000',
        ];

        $response = $this->withHeaders($this->validAuthHeaders())
            ->postJson('/api/v1/invoices', $payload);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'code' => 'VALIDATION_ERROR',
            ])
            ->assertJsonValidationErrors(['invoiceDate', 'dppAmount']);
    }

    public function test_create_invoice_termin_not_found_404(): void
    {
        $payload = [
            'terminId' => 'trm-tidak-ada',
            'invoiceDate' => '2026-11-01',
            'dppAmount' => '100000000.00',
        ];

        $response = $this->withHeaders($this->validAuthHeaders())
            ->postJson('/api/v1/invoices', $payload);

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'code' => 'TERMIN_NOT_FOUND',
            ]);
    }

    public function test_create_invoice_blocked_when_po_not_available_422(): void
    {
        // trm-demo-003 terkait ke prj-demo-002 yang poStatus = 'PO_TIDAK_ADA'
        $payload = [
            'terminId' => 'trm-demo-003',
            'invoiceDate' => '2026-11-01',
            'dppAmount' => '80000000.00',
        ];

        $response = $this->withHeaders($this->validAuthHeaders())
            ->postJson('/api/v1/invoices', $payload);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'code' => 'PO_NOT_FOUND_OR_BLOCKED',
                'message' => 'Penagihan diblokir: PO belum tersedia',
                'details' => [
                    'terminId' => 'trm-demo-003',
                    'projectId' => 'prj-demo-002',
                    'poStatus' => 'PO_TIDAK_ADA',
                ],
            ]);
    }

    public function test_create_invoice_blocked_when_temporary_client_422(): void
    {
        // Buat termin baru yang terikat ke prj-demo-temp (klien sementara)
        $contractTemp = Contract::create([
            'contract_id' => 'ktr-temp-client',
            'project_id' => 'prj-demo-temp',
            'contract_number' => 'KTR/TEMP/001',
            'contract_value' => 50000000.00,
            'created_by' => 'emp-demo-007',
        ]);

        $terminTemp = Termin::create([
            'termin_id' => 'trm-temp-client',
            'contract_id' => 'ktr-temp-client',
            'termin_number' => 1,
            'termin_value' => 50000000.00,
            'due_date' => '2026-11-10',
            'status' => 'SIAP_DITAGIH',
        ]);

        $payload = [
            'terminId' => 'trm-temp-client',
            'invoiceDate' => '2026-11-01',
            'dppAmount' => '50000000.00',
        ];

        $response = $this->withHeaders($this->validAuthHeaders())
            ->postJson('/api/v1/invoices', $payload);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'code' => 'TEMPORARY_CLIENT_BLOCKED',
                'message' => 'Penagihan diblokir: Klien proyek masih sementara',
                'details' => [
                    'terminId' => 'trm-temp-client',
                    'projectId' => 'prj-demo-temp',
                    'isTemporaryClient' => true,
                ],
            ]);
    }

    public function test_create_invoice_success_tax_calculation_and_status_transition(): void
    {
        // Menguji alur sukses, kalkulasi pajak otomatis (PPN 11%, PPh 2%), dan transisi status termin
        $termin = Termin::find('trm-demo-001');
        $this->assertEquals('SIAP_DITAGIH', $termin->status);

        $payload = [
            'terminId' => 'trm-demo-001',
            'invoiceDate' => '2026-11-01',
            'dppAmount' => '100000000.00',
        ];

        $response = $this->withHeaders($this->validAuthHeaders())
            ->postJson('/api/v1/invoices', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'terminId' => 'trm-demo-001',
                    'invoiceDate' => '2026-11-01',
                    'dppAmount' => '100000000.00',
                    'ppnAmount' => '11000000.00',  // 11% dari 100jt
                    'pphAmount' => '2000000.00',   // 2% dari 100jt
                    'totalAmount' => '109000000.00', // 100jt + 11jt - 2jt
                    'taxParamId' => 'pjk-demo-001',
                    'status' => 'TERTAGIH',
                    'createdBy' => 'emp-demo-012',
                ],
            ]);

        // Verifikasi perubahan status otomatis di database: SIAP_DITAGIH -> SUDAH_DITAGIHKAN
        $termin->refresh();
        $this->assertEquals('SUDAH_DITAGIHKAN', $termin->status);

        // Verifikasi pencatatan riwayat audit invoice
        $invoiceId = $response->json('data.invoiceId');
        $invoice = Invoice::with('histories')->find($invoiceId);
        $this->assertNotNull($invoice);
        $this->assertCount(1, $invoice->histories);
        $this->assertEquals('TERTAGIH', $invoice->histories[0]->status);
        $this->assertEquals('emp-demo-012', $invoice->histories[0]->changed_by);
    }

    public function test_create_invoice_conflict_already_invoiced_409(): void
    {
        // Terbitkan invoice pertama
        $payload = [
            'terminId' => 'trm-demo-001',
            'invoiceDate' => '2026-11-01',
            'dppAmount' => '100000000.00',
        ];

        $this->withHeaders($this->validAuthHeaders())
            ->postJson('/api/v1/invoices', $payload)
            ->assertStatus(201);

        // Upaya kedua menerbitkan invoice pada termin yang sama harus menghasilkan 409 Conflict
        $response = $this->withHeaders($this->validAuthHeaders())
            ->postJson('/api/v1/invoices', $payload);

        $response->assertStatus(409)
            ->assertJson([
                'status' => 'error',
                'code' => 'TERMIN_ALREADY_INVOICED',
            ]);
    }

    public function test_create_invoice_external_service_unavailable_503(): void
    {
        // Simulasi layanan Modul D-1 down
        $payload = [
            'terminId' => 'trm-demo-001',
            'invoiceDate' => '2026-11-01',
            'dppAmount' => '100000000.00',
        ];

        $headers = array_merge($this->validAuthHeaders(), [
            'X-Simulate-D1-Down' => 'true',
        ]);

        $response = $this->withHeaders($headers)
            ->postJson('/api/v1/invoices', $payload);

        $response->assertStatus(503)
            ->assertJson([
                'status' => 'error',
                'code' => 'DEPENDENCY_UNAVAILABLE',
            ]);
    }

    // ==========================================
    // 4. PENGUJIAN DETAIL INVOICE (API-K02-05)
    // ==========================================

    public function test_get_invoice_detail_success(): void
    {
        $response = $this->withHeaders($this->validAuthHeaders())
            ->getJson('/api/v1/invoices/inv-demo-sample');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Detail invoice berhasil dimuat',
                'data' => [
                    'invoiceId' => 'inv-demo-sample',
                    'invoiceNumber' => 'INV-2026-SAMPLE',
                    'dppAmount' => '100000000.00',
                    'ppnAmount' => '11000000.00',
                    'pphAmount' => '2000000.00',
                    'totalAmount' => '109000000.00',
                    'status' => 'TERTAGIH',
                    'terkait' => [
                        'terminId' => 'trm-demo-sample',
                        'contractId' => 'ktr-demo-sample',
                        'projectId' => 'prj-demo-001',
                        'poStatus' => 'PO_ADA',
                    ],
                ],
            ])
            ->assertJsonStructure([
                'data' => [
                    'riwayat' => [
                        '*' => ['status', 'changedAt', 'changedBy', 'note'],
                    ],
                ],
            ]);
    }

    public function test_get_invoice_detail_not_found_404(): void
    {
        $response = $this->withHeaders($this->validAuthHeaders())
            ->getJson('/api/v1/invoices/inv-not-found');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'code' => 'INVOICE_NOT_FOUND',
            ]);
    }

    // ==========================================
    // 5. PENGUJIAN PARAMETER PAJAK (API-K02-04)
    // ==========================================

    public function test_get_applicable_tax_parameters_success(): void
    {
        $response = $this->withHeaders($this->validAuthHeaders())
            ->getJson('/api/v1/tax-parameters/applicable?date=2026-11-01');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'taxParamId' => 'pjk-demo-001',
                    'ppnRate' => '11.00',
                    'pphRate' => '2.00',
                    'effectiveFrom' => '2026-01-01',
                ],
            ]);
    }

    // ==========================================
    // 6. PENGUJIAN PENCATATAN KONTRAK (API-K02-01)
    // ==========================================

    public function test_create_contract_success(): void
    {
        $payload = [
            'projectId' => 'prj-demo-001',
            'contractNumber' => 'KTR/2026/TEST-NEW',
            'contractValue' => '300000000.00',
        ];

        $response = $this->withHeaders($this->validAuthHeaders('admin'))
            ->postJson('/api/v1/contracts', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'projectId' => 'prj-demo-001',
                    'contractNumber' => 'KTR/2026/TEST-NEW',
                    'contractValue' => '300000000.00',
                ],
            ]);
    }

    public function test_create_contract_invalid_project_422(): void
    {
        $payload = [
            'projectId' => 'prj-unknown',
            'contractNumber' => 'KTR/2026/FAIL',
            'contractValue' => '300000000.00',
        ];

        $response = $this->withHeaders($this->validAuthHeaders('admin'))
            ->postJson('/api/v1/contracts', $payload);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'code' => 'INVALID_PROJECT',
            ]);
    }
}
