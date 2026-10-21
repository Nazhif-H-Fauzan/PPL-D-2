<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ModulD2AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null $requiredRole
     */
    public function handle(Request $request, Closure $next, ?string $requiredRole = null): Response
    {
        $authHeader = $request->header('Authorization');

        // Pengecekan ketiadaan atau ketidakvalidan token Bearer
        if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json([
                'status' => 'error',
                'code' => 'UNAUTHORIZED',
                'message' => 'Token autentikasi tidak ada atau tidak valid',
            ], 401);
        }

        $token = trim(substr($authHeader, 7));

        // Simulasi penolakan token jika bernilai 'invalid', 'expired', atau kosong
        if (empty($token) || in_array(strtolower($token), ['invalid', 'expired', 'invalid_token', 'expired_token'])) {
            return response()->json([
                'status' => 'error',
                'code' => 'UNAUTHORIZED',
                'message' => 'Token tidak valid atau telah kadaluarsa',
            ], 401);
        }

        // Ekstraksi data pengguna & peran dari token atau simulasi payload
        $employeeId = 'emp-demo-012';
        $userRole = $request->header('X-User-Role', 'petugas_keuangan');

        // Jika token berupa JWT sederhana (base64 payload), coba parse klaimnya
        $parts = explode('.', $token);
        if (count($parts) === 3) {
            $payload = json_decode(base64_decode($parts[1]), true);
            if (is_array($payload)) {
                if (isset($payload['employeeId'])) {
                    $employeeId = $payload['employeeId'];
                }
                if (isset($payload['role']) && !$request->hasHeader('X-User-Role')) {
                    $userRole = $payload['role'];
                }
                if (isset($payload['roles']) && is_array($payload['roles']) && !$request->hasHeader('X-User-Role')) {
                    $userRole = in_array('petugas_keuangan', $payload['roles']) ? 'petugas_keuangan' : ($payload['roles'][0] ?? 'guest');
                }
            }
        } elseif (str_starts_with($token, 'emp-')) {
            $employeeId = $token;
        }

        // Validasi Otorisasi Berdasarkan Peran
        if (!empty($requiredRole)) {
            $allowedRoles = array_map('trim', explode('|', $requiredRole));

            if (!in_array($userRole, $allowedRoles)) {
                $roleLabel = ($requiredRole === 'petugas_keuangan') ? 'Petugas Keuangan' : $requiredRole;
                return response()->json([
                    'status' => 'error',
                    'code' => 'FORBIDDEN',
                    'message' => "Akses ditolak: Anda tidak memiliki kewenangan sebagai {$roleLabel}",
                    'details' => [
                        'requiredRole' => $requiredRole,
                        'currentUserRole' => $userRole,
                    ],
                ], 403);
            }
        }

        // Tambahkan context autentikasi ke request
        $request->attributes->set('auth_employee_id', $employeeId);
        $request->attributes->set('auth_user_role', $userRole);

        return $next($request);
    }
}
