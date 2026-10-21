<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateContractRequest;
use App\Models\Contract;
use App\Services\D1ProjectServiceClient;
use App\Services\D1ServiceUnavailableException;
use Illuminate\Http\JsonResponse;

class ContractController extends Controller
{
    /**
     * API-K02-01: Mencatat kontrak komersial baru yang terikat ke proyek kanonis D-1.
     */
    public function store(CreateContractRequest $request, D1ProjectServiceClient $d1Client): JsonResponse
    {
        $projectId = $request->input('projectId');

        // Validasi projectId ke layanan D-1
        try {
            $project = $d1Client->getProject($projectId);
        } catch (D1ServiceUnavailableException $e) {
            return response()->json([
                'status' => 'error',
                'code' => 'DEPENDENCY_UNAVAILABLE',
                'message' => 'Layanan eksternal D-1 tidak dapat dihubungi saat memvalidasi proyek',
            ], 503);
        }

        if (!$project || ($project['status'] ?? 'ACTIVE') !== 'ACTIVE') {
            return response()->json([
                'status' => 'error',
                'code' => 'INVALID_PROJECT',
                'message' => 'ProjectId tidak sah atau tidak aktif di D-1',
            ], 422);
        }

        $contractId = 'ktr-' . uniqid();
        $employeeId = $request->attributes->get('auth_employee_id', 'emp-demo-007');

        $contract = Contract::create([
            'contract_id' => $contractId,
            'project_id' => $projectId,
            'contract_number' => $request->input('contractNumber'),
            'contract_value' => (float) $request->input('contractValue'),
            'created_by' => $employeeId,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'contractId' => $contract->contract_id,
                'projectId' => $contract->project_id,
                'contractNumber' => $contract->contract_number,
                'contractValue' => number_format((float) $contract->contract_value, 2, '.', ''),
                'createdBy' => $contract->created_by,
                'createdAt' => $contract->created_at->toISOString(),
            ],
        ], 201);
    }
}
