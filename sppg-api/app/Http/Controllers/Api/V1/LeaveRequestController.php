<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LeaveRequestController extends Controller
{
    // Mengajukan izin atau sakit
    public function store(Request $request)
    {
        // 1. Ambil pengguna yang sedang login
        $user = $request->user();

        // 2. Periksa status akun
        if (
            $user->role !== 'employee' ||
            $user->account_status !== 'active'
        ) {
            return response()->json([
                'message' => 'Akun tidak memiliki akses pengajuan.',
            ], 403);
        }

        // 3. Validasi data pengajuan
        $validated = $request->validate([
            'request_type' => [
                'required',
                'in:permission,sick',
            ],
            'start_date' => [
                'required',
                'date_format:Y-m-d',
            ],
            'end_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:start_date',
            ],
            'reason' => [
                'required',
                'string',
                'max:2000',
            ],
            'attachment' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],
        ]);

        // 4. Periksa bentrok tanggal pengajuan
        $hasConflict = LeaveRequest::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->whereDate('start_date', '<=', $validated['end_date'])
            ->whereDate('end_date', '>=', $validated['start_date'])
            ->exists();

        if ($hasConflict) {
            return response()->json([
                'message' => 'Tanggal pengajuan bertabrakan dengan pengajuan lain yang masih pending atau sudah disetujui.',
            ], 422);
        }

        // 5. Simpan lampiran jika tersedia
        $attachmentPath = null;

        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')
                ->store('leave-attachments', 'private');
        }

        // 6. Simpan pengajuan ke database
        $leaveRequest = DB::transaction(function () use (
            $user,
            $validated,
            $attachmentPath
        ) {
            return $user->leaveRequests()->create([
                'request_type' => $validated['request_type'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'reason' => $validated['reason'],
                'attachment_path' => $attachmentPath,
                'status' => 'pending',
            ]);
        });

        // 7. Kirim respons
        return response()->json([
            'message' => 'Pengajuan berhasil dikirim dan menunggu persetujuan admin.',
            'data' => $leaveRequest,
        ], 201);
    }

    
// Menampilkan riwayat pengajuan pegawai
public function index(Request $request)
{
    // 1. Ambil pengguna yang sedang login
    $user = $request->user();

    // 2. Periksa status akun
    if (
        $user->role !== 'employee' ||
        $user->account_status !== 'active'
    ) {
        return response()->json([
            'message' => 'Akun tidak memiliki akses riwayat pengajuan.',
        ], 403);
    }

    // 3. Validasi filter tanggal dan status
    $validated = $request->validate([
        'start_date' => ['nullable', 'date_format:Y-m-d'],
        'end_date' => [
            'nullable',
            'date_format:Y-m-d',
            'after_or_equal:start_date',
        ],
        'status' => [
            'nullable',
            'in:pending,approved,rejected',
        ],
    ]);

    // 4. Ambil pengajuan milik pengguna yang login
    $query = LeaveRequest::with('reviewer')
        ->where('user_id', $user->id);

    // 5. Filter tanggal mulai
    if (!empty($validated['start_date'])) {
        $query->whereDate(
            'start_date',
            '>=',
            $validated['start_date']
        );
    }

    // 6. Filter tanggal akhir
    if (!empty($validated['end_date'])) {
        $query->whereDate(
            'end_date',
            '<=',
            $validated['end_date']
        );
    }

    // 7. Filter status pengajuan
    if (!empty($validated['status'])) {
        $query->where(
            'status',
            $validated['status']
        );
    }

    // 8. Ambil data dengan pagination
    $leaveRequests = $query
        ->orderByDesc('created_at')
        ->paginate(10);

    // 9. Kirim respons
    return response()->json([
        'message' => 'Riwayat pengajuan berhasil diambil.',
        'data' => $leaveRequests->items(),
        'pagination' => [
            'current_page' => $leaveRequests->currentPage(),
            'last_page' => $leaveRequests->lastPage(),
            'per_page' => $leaveRequests->perPage(),
            'total' => $leaveRequests->total(),
        ],
    ], 200);
}


    // Menampilkan detail satu pengajuan
    public function show(Request $request, $id)
    {
        // 1. Ambil pengguna yang sedang login
        $user = $request->user();

        // 2. Periksa status akun
        if (
            $user->role !== 'employee' ||
            $user->account_status !== 'active'
        ) {
            return response()->json([
                'message' => 'Akun tidak memiliki akses detail pengajuan.',
            ], 403);
        }

        // 3. Cari pengajuan berdasarkan ID milik pengguna
        $leaveRequest = $user->leaveRequests()
            ->with('reviewer')
            ->findOrFail($id);

        // 4. Kirim detail pengajuan
        return response()->json([
            'message' => 'Detail pengajuan berhasil diambil.',
            'data' => $leaveRequest,
        ], 200);
    }

    // Mengunduh lampiran pengajuan khusus admin
    public function downloadAttachment(Request $request, $id)
    {
        // 1. Ambil pengguna yang sedang login
        $user = $request->user();

        // 2. Pastikan pengguna adalah admin aktif
        if (
            $user->role !== 'admin' ||
            $user->account_status !== 'active'
        ) {
            return response()->json([
                'message' => 'Hanya admin aktif yang dapat mengunduh lampiran.',
            ], 403);
        }

        // 3. Cari pengajuan berdasarkan ID
        $leaveRequest = LeaveRequest::findOrFail($id);

        // 4. Pastikan pengajuan memiliki lampiran
        if (!$leaveRequest->attachment_path) {
            return response()->json([
                'message' => 'Pengajuan ini tidak memiliki lampiran.',
            ], 404);
        }

        // 5. Pastikan file berada di folder lampiran dan tersedia
        $path = $leaveRequest->attachment_path;

        if (
            dirname($path) !== 'leave-attachments' ||
            basename($path) === '' ||
            !Storage::disk('private')->exists($path)
        ) {
            return response()->json([
                'message' => 'File lampiran tidak ditemukan.',
            ], 404);
        }

        // 6. Kirim file kepada admin
        return Storage::disk('private')->download($path);
    }
}

