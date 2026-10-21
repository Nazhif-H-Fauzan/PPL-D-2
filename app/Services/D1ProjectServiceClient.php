<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class D1ServiceUnavailableException extends Exception
{
    protected $message = 'Layanan eksternal D-1 tidak dapat dihubungi';
}

class D1ProjectServiceClient
{
    /**
     * Data mock internal untuk integrasi pengujian Modul D-1 sesuai OpenAPI.
     */
    protected array $mockProjects = [
        'prj-demo-001' => [
            'projectId' => 'prj-demo-001',
            'projectName' => 'Implementasi ERP Komersial',
            'poReceived' => true,
            'poStatus' => 'PO_ADA',
            'isTemporaryClient' => false,
            'clientId' => 'cli-demo-001',
            'status' => 'ACTIVE',
        ],
        'prj-demo-002' => [
            'projectId' => 'prj-demo-002',
            'projectName' => 'Revitalisasi Infrastruktur Jaringan',
            'poReceived' => false,
            'poStatus' => 'PO_TIDAK_ADA',
            'isTemporaryClient' => false,
            'clientId' => 'cli-demo-002',
            'status' => 'ACTIVE',
        ],
        'prj-demo-temp' => [
            'projectId' => 'prj-demo-temp',
            'projectName' => 'Pengadaan Lisensi Sementara',
            'poReceived' => true,
            'poStatus' => 'PO_ADA',
            'isTemporaryClient' => true,
            'clientId' => null,
            'status' => 'ACTIVE',
        ],
    ];

    /**
     * Mengambil informasi proyek dari Modul D-1.
     *
     * @param string $projectId
     * @return array|null
     * @throws D1ServiceUnavailableException
     */
    public function getProject(string $projectId): ?array
    {
        // Skenario simulasi kegagalan koneksi eksternal D-1 (503)
        if ($projectId === 'prj-d1-down' || request()->header('X-Simulate-D1-Down') === 'true') {
            throw new D1ServiceUnavailableException('Layanan eksternal D-1 tidak dapat dihubungi');
        }

        $baseUrl = config('services.modul_d1.base_url');

        if (!empty($baseUrl)) {
            try {
                $response = Http::timeout(3)->get("{$baseUrl}/api/v1/projects/{$projectId}");
                if ($response->status() === 404) {
                    return null;
                }
                if ($response->failed()) {
                    throw new D1ServiceUnavailableException('Layanan eksternal D-1 mengalami gangguan HTTP ' . $response->status());
                }
                $data = $response->json('data') ?? $response->json();
                $poReceived = (bool) ($data['poReceived'] ?? false);
                return [
                    'projectId' => $data['projectId'] ?? $projectId,
                    'projectName' => $data['projectName'] ?? 'Proyek D-1',
                    'poReceived' => $poReceived,
                    'poStatus' => $poReceived ? 'PO_ADA' : 'PO_TIDAK_ADA',
                    'isTemporaryClient' => (bool) ($data['isTemporaryClient'] ?? false),
                    'clientId' => $data['clientId'] ?? null,
                    'status' => $data['status'] ?? 'ACTIVE',
                ];
            } catch (Exception $e) {
                if ($e instanceof D1ServiceUnavailableException) {
                    throw $e;
                }
                Log::warning("Gagal menghubungi layanan D-1 pada {$baseUrl}: " . $e->getMessage());
                throw new D1ServiceUnavailableException('Layanan eksternal D-1 tidak dapat dihubungi: ' . $e->getMessage());
            }
        }

        // Gunakan registry simulasi jika URL eksternal belum dikonfigurasi
        if (isset($this->mockProjects[$projectId])) {
            return $this->mockProjects[$projectId];
        }

        return null;
    }

    /**
     * Mendaftarkan atau menimpa data mock proyek (berguna saat unit testing).
     */
    public function registerMockProject(string $projectId, array $data): void
    {
        $this->mockProjects[$projectId] = $data;
    }
}
