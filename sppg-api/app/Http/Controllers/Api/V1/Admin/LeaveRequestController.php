<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    /**
     * Menampilkan seluruh pengajuan izin dan sakit.
     */
    public function index(Request $request)
    {
        $admin = $request->user();

        // Pastikan hanya admin aktif yang dapat mengakses.
        if (
            $admin->role !== 'admin' ||
            $admin->account_status !== 'active'
        ) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses.',
            ], 403);
        }

        // Validasi filter.
        $validated = $request->validate([
            'status' => 'nullable|in:pending,approved,rejected',
            'request_type' => 'nullable|in:permission,sick',
        ]);

        // Ambil seluruh pengajuan beserta data pegawai.
        $query = LeaveRequest::with([
            'user.employeeProfile',
            'reviewer',
        ]);

        // Filter berdasarkan status.
        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        // Filter berdasarkan jenis pengajuan.
        if (!empty($validated['request_type'])) {
            $query->where(
                'request_type',
                $validated['request_type']
            );
        }

        // Data terbaru ditampilkan lebih dahulu.
        $requests = $query
            ->latest()
            ->paginate(10);

        // Format respons API.
        $requests->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,

                'employee' => [
                    'id' => $item->user?->id,
                    'name' => $item->user?->name,
                    'email' => $item->user?->email,
                    'nip' => $item->user?->employeeProfile?->employee_number,
                    'position' => $item->user?->employeeProfile?->position,
                ],

                'request_type' => $item->request_type,
                'start_date' => $item->start_date?->format('Y-m-d'),
                'end_date' => $item->end_date?->format('Y-m-d'),
                'reason' => $item->reason,

                'has_attachment' => !empty($item->attachment_path),

                'status' => $item->status,
                'review_notes' => $item->review_notes,

                'reviewer' => $item->reviewer?->name,
                'reviewed_at' => $item->reviewed_at,

                'created_at' => $item->created_at,
            ];
        });

        return response()->json([
            'message' => 'Data pengajuan berhasil diambil.',
            'data' => $requests,
        ]);
    }
}
