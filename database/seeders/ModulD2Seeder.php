<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\Invoice;
use App\Models\InvoiceHistory;
use App\Models\TaxParameter;
use App\Models\Termin;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ModulD2Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Tax Parameters (Default PPN 11%, PPh 2% berlaku mulai 2026-01-01)
        TaxParameter::updateOrCreate(
            ['tax_param_id' => 'pjk-demo-001'],
            [
                'contract_id' => null,
                'ppn_rate' => 11.00,
                'pph_rate' => 2.00,
                'effective_from' => '2026-01-01',
                'effective_to' => null,
            ]
        );

        // 2. Contracts (KTR/2026/001 & KTR/2026/004)
        $contract1 = Contract::updateOrCreate(
            ['contract_id' => 'ktr-demo-001'],
            [
                'project_id' => 'prj-demo-001',
                'contract_number' => 'KTR/2026/001',
                'contract_value' => 500000000.00,
                'created_by' => 'emp-demo-007',
                'created_at' => Carbon::parse('2026-10-08 03:15:00'),
            ]
        );

        $contract4 = Contract::updateOrCreate(
            ['contract_id' => 'ktr-demo-004'],
            [
                'project_id' => 'prj-demo-002',
                'contract_number' => 'KTR/2026/004',
                'contract_value' => 80000000.00,
                'created_by' => 'emp-demo-007',
                'created_at' => Carbon::parse('2026-10-08 03:15:00'),
            ]
        );

        // 3. Termins (Sesuai contoh di OpenAPI & termins_get_success.json)
        // trm-demo-001: Siap Ditagih (PO_ADA)
        Termin::updateOrCreate(
            ['termin_id' => 'trm-demo-001'],
            [
                'contract_id' => 'ktr-demo-001',
                'termin_number' => 1,
                'termin_value' => 100000000.00,
                'due_date' => '2026-10-15',
                'status' => 'SIAP_DITAGIH',
            ]
        );

        // trm-demo-002: Siap Ditagih (PO_ADA)
        Termin::updateOrCreate(
            ['termin_id' => 'trm-demo-002'],
            [
                'contract_id' => 'ktr-demo-001',
                'termin_number' => 2,
                'termin_value' => 150000000.00,
                'due_date' => '2026-12-15',
                'status' => 'SIAP_DITAGIH',
            ]
        );

        // trm-demo-003: Siap Ditagih tetapi PO_TIDAK_ADA pada prj-demo-002
        Termin::updateOrCreate(
            ['termin_id' => 'trm-demo-003'],
            [
                'contract_id' => 'ktr-demo-004',
                'termin_number' => 1,
                'termin_value' => 80000000.00,
                'due_date' => '2026-10-20',
                'status' => 'SIAP_DITAGIH',
            ]
        );

        // Sample Termin & Invoice yang sudah diterbitkan untuk pengujian GET /api/v1/invoices/{invoiceId} langsung
        $sampleContract = Contract::updateOrCreate(
            ['contract_id' => 'ktr-demo-sample'],
            [
                'project_id' => 'prj-demo-001',
                'contract_number' => 'KTR/2026/SAMPLE',
                'contract_value' => 200000000.00,
                'created_by' => 'emp-demo-007',
                'created_at' => Carbon::parse('2026-10-01 00:00:00'),
            ]
        );

        $sampleTermin = Termin::updateOrCreate(
            ['termin_id' => 'trm-demo-sample'],
            [
                'contract_id' => 'ktr-demo-sample',
                'termin_number' => 1,
                'termin_value' => 100000000.00,
                'due_date' => '2026-10-10',
                'status' => 'SUDAH_DITAGIHKAN',
            ]
        );

        $sampleInvoice = Invoice::updateOrCreate(
            ['invoice_id' => 'inv-demo-sample'],
            [
                'invoice_number' => 'INV-2026-SAMPLE',
                'termin_id' => 'trm-demo-sample',
                'tax_param_id' => 'pjk-demo-001',
                'invoice_date' => '2026-10-11',
                'dpp_amount' => 100000000.00,
                'ppn_amount' => 11000000.00,
                'pph_amount' => 2000000.00,
                'total_amount' => 109000000.00,
                'status' => 'TERTAGIH',
                'created_by' => 'emp-demo-012',
                'created_at' => Carbon::parse('2026-10-11 02:00:00'),
            ]
        );

        InvoiceHistory::updateOrCreate(
            [
                'invoice_id' => 'inv-demo-sample',
                'status' => 'TERTAGIH',
            ],
            [
                'changed_at' => Carbon::parse('2026-10-11 02:00:00'),
                'changed_by' => 'emp-demo-012',
                'note' => 'Invoice diterbitkan oleh petugas keuangan',
            ]
        );
    }
}
