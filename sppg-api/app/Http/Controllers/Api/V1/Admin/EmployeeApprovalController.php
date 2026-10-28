<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeApprovalController extends Controller
{
    /**
     * Memastikan hanya admin yang dapat mengakses fitur ini.
     */
    private function ensureAdmin(Request $request): ?JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Akses ditolak. Hanya admin yang diizinkan.',
            ], 403);
        }

        return null;
    }

    /**
     * Menampilkan daftar pegawai yang menunggu persetujuan.
     */
    public function index(Request $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $employees = User::with('employeeProfile')
            ->where('role', 'employee')
            ->where('account_status', 'pending')
            ->orderBy('created_at')
            ->paginate(10);

        return response()->json([
            'message' => 'Daftar pendaftaran pegawai berhasil diambil.',
            'data' => $employees,
        ]);
    }

    /**
     * Menyetujui pendaftaran pegawai.
     */
    public function approve(Request $request, int $userId): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $employee = User::where('role', 'employee')
            ->findOrFail($userId);

        if ($employee->account_status !== 'pending') {
            return response()->json([
                'message' => 'Hanya akun pending yang dapat disetujui.',
            ], 422);
        }

        $employee->update([
            'account_status' => 'active',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return response()->json([
            'message' => 'Pendaftaran pegawai berhasil disetujui.',
            'data' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
                'role' => $employee->role,
                'account_status' => $employee->account_status,
                'approved_by' => $employee->approved_by,
                'approved_at' => $employee->approved_at,
            ],
        ]);
    }

    /**
     * Menolak pendaftaran pegawai.
     */
    public function reject(Request $request, int $userId): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $employee = User::where('role', 'employee')
            ->findOrFail($userId);

        if ($employee->account_status !== 'pending') {
            return response()->json([
                'message' => 'Hanya akun pending yang dapat ditolak.',
            ], 422);
        }

        $employee->update([
            'account_status' => 'rejected',
        ]);

        return response()->json([
            'message' => 'Pendaftaran pegawai berhasil ditolak.',
            'data' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
                'account_status' => $employee->account_status,
            ],
        ]);
    }
}
