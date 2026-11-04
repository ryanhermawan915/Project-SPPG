<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\AttendanceRecord;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil pengguna yang sedang login
        $user = $request->user();

        // 2. Pastikan pengguna adalah karyawan aktif
        if (
            $user->role !== 'employee' ||
            $user->account_status !== 'active'
        ) {
            return response()->json([
                'message' => 'Akun tidak memiliki akses dashboard.',
            ], 403);
        }

        // 3. Validasi filter periode akademik
        $validated = $request->validate([
            'academic_year' => [
                'nullable',
                'string',
                'max:20',
            ],
            'semester' => [
                'nullable',
                'in:odd,even',
            ],
        ]);

        // 4. Cari periode akademik
        $periodQuery = AcademicPeriod::query();

        if (
            !empty($validated['academic_year']) &&
            !empty($validated['semester'])
        ) {
            $periodQuery
                ->where('academic_year', $validated['academic_year'])
                ->where('semester', $validated['semester']);
        } else {
            $periodQuery->where('is_active', true);
        }

        $academicPeriod = $periodQuery->first();

        // 5. Pastikan periode ditemukan
        if (!$academicPeriod) {
            return response()->json([
                'message' => 'Periode akademik tidak ditemukan.',
            ], 404);
        }

        // 6. Ambil semua data presensi dalam periode akademik
        // 6. Tentukan batas tanggal perhitungan
        $today = Carbon::today();

        $calculationEndDate = $academicPeriod->end_date->lt($today)
            ? $academicPeriod->end_date
            : $today;

        // Ambil data presensi sampai batas tanggal perhitungan
        $attendanceRecords = AttendanceRecord::where(
            'user_id',
            $user->id
        )
        ->whereBetween('attendance_date', [
            $academicPeriod->start_date,
            $calculationEndDate,
        ])
        ->get();

        // 7. Hitung jumlah setiap status presensi
        $presentCount = $attendanceRecords
            ->where('status', 'present')
            ->count();

        $lateCount = $attendanceRecords
            ->where('status', 'late')
            ->count();

        $permissionCount = $attendanceRecords
            ->where('status', 'permission')
            ->count();

        $sickCount = $attendanceRecords
            ->where('status', 'sick')
            ->count();

        $absentCount = $attendanceRecords
            ->where('status', 'absent')
            ->count();

        // 8. Hitung jumlah hari kerja dalam periode
        // 8. Ambil hari kerja aktif dari database
        $activeWorkDays = WorkSchedule::where('is_active', true)
            ->pluck('day_of_week')
            ->map(fn ($day) => (int) $day)
            ->toArray();

        // Hitung jumlah hari kerja sampai tanggal perhitungan
        $workingDays = 0;

        $datePeriod = CarbonPeriod::create(
            $academicPeriod->start_date,
            $calculationEndDate
        );

        foreach ($datePeriod as $date) {
            if (in_array($date->dayOfWeekIso, $activeWorkDays)) {
                $workingDays++;
            }
        }

        // 9. Hitung persentase kehadiran
        $attendancePercentage = $workingDays > 0
            ? round(
                (($presentCount + $lateCount) / $workingDays) * 100,
                2
            )
            : 0;

        // 10. Kirim respons dashboard
        return response()->json([
            'message' => 'Dashboard berhasil diambil.',
            'data' => [
                'academic_period' => [
                    'id' => $academicPeriod->id,
                    'academic_year' => $academicPeriod->academic_year,
                    'semester' => $academicPeriod->semester,
                    'start_date' => $academicPeriod->start_date
                        ->format('Y-m-d'),
                    'end_date' => $academicPeriod->end_date
                        ->format('Y-m-d'),
                    'is_active' => $academicPeriod->is_active,
                ],

                'summary' => [
                    'present' => $presentCount,
                    'late' => $lateCount,
                    'permission' => $permissionCount,
                    'sick' => $sickCount,
                    'absent' => $absentCount,
                    'working_days' => $workingDays,
                    'attendance_percentage' => $attendancePercentage,
                ],
            ],
        ], 200);
    }

    public function monthlyAttendance(Request $request)
    {
        $user = $request->user();

        // Pastikan akun merupakan employee aktif
        if (
            $user->role !== 'employee' ||
            $user->account_status !== 'active'
        ) {
            return response()->json([
                'message' => 'Akun tidak memiliki akses dashboard.',
            ], 403);
        }

        // Validasi filter periode akademik
        $validated = $request->validate([
            'academic_year' => [
                'nullable',
                'required_with:semester',
                'string',
                'max:20',
            ],
            'semester' => [
                'nullable',
                'required_with:academic_year',
                'in:odd,even',
            ],
        ]);

        // Ambil periode akademik
        $periodQuery = AcademicPeriod::query();

        if (!empty($validated['academic_year'])) {
            $periodQuery
                ->where('academic_year', $validated['academic_year'])
                ->where('semester', $validated['semester']);
        } else {
            $periodQuery->where('is_active', true);
        }

        $academicPeriod = $periodQuery->first();

        if (!$academicPeriod) {
            return response()->json([
                'message' => 'Periode akademik tidak ditemukan.',
            ], 404);
        }

        // Tentukan batas tanggal perhitungan
        $today = Carbon::today();

        $calculationEndDate = $academicPeriod->end_date->lt($today)
            ? $academicPeriod->end_date
            : $today;

        // Ambil jadwal kerja aktif
        $activeWorkDays = WorkSchedule::where('is_active', true)
            ->pluck('day_of_week')
            ->map(fn ($day) => (int) $day)
            ->toArray();

        // Ambil seluruh presensi employee dalam periode
        $attendanceRecords = AttendanceRecord::where(
            'user_id',
            $user->id
        )
            ->whereBetween('attendance_date', [
                $academicPeriod->start_date,
                $calculationEndDate,
            ])
            ->get();

        $monthlyStatistics = [];

        // Jika periode belum dimulai, tidak ada statistik
        if ($calculationEndDate->gte($academicPeriod->start_date)) {

            // Kelompokkan data presensi berdasarkan bulan
            $recordsByMonth = $attendanceRecords->groupBy(
                fn ($record) => $record->attendance_date->format('Y-m')
            );

            // Iterasi setiap bulan dalam periode
            $currentMonth = $academicPeriod->start_date
                ->copy()
                ->startOfMonth();

            $lastMonth = $calculationEndDate
                ->copy()
                ->startOfMonth();

            while ($currentMonth->lte($lastMonth)) {

                $monthStart = $currentMonth->greaterThan(
                    $academicPeriod->start_date
                )
                    ? $currentMonth->copy()
                    : $academicPeriod->start_date->copy();

                $monthEnd = $currentMonth->copy()->endOfMonth();

                if ($monthEnd->gt($calculationEndDate)) {
                    $monthEnd = $calculationEndDate->copy();
                }

                $monthKey = $currentMonth->format('Y-m');

                $records = $recordsByMonth->get($monthKey, collect());

                // Hitung hari kerja pada bulan tersebut
                $workingDays = 0;

                $datePeriod = CarbonPeriod::create(
                    $monthStart,
                    $monthEnd
                );

                foreach ($datePeriod as $date) {
                    if (in_array(
                        $date->dayOfWeekIso,
                        $activeWorkDays
                    )) {
                        $workingDays++;
                    }
                }

                // Hitung jumlah setiap status
                $presentCount = $records->where('status', 'present')->count();
                $lateCount = $records->where('status', 'late')->count();
                $permissionCount = $records->where('status', 'permission')->count();
                $sickCount = $records->where('status', 'sick')->count();
                $absentCount = $records->where('status', 'absent')->count();

                // Hitung persentase kehadiran bulanan
                $attendancePercentage = $workingDays > 0
                    ? round(
                        (($presentCount + $lateCount) / $workingDays) * 100,
                        2
                    )
                    : 0;

                $monthlyStatistics[] = [
                    'month' => $monthKey,
                    'present' => $presentCount,
                    'late' => $lateCount,
                    'permission' => $permissionCount,
                    'sick' => $sickCount,
                    'absent' => $absentCount,
                    'working_days' => $workingDays,
                    'attendance_percentage' => $attendancePercentage,
                ];

                $currentMonth->addMonth();
            }
        }

        return response()->json([
            'message' => 'Statistik presensi bulanan berhasil diambil.',
            'data' => [
                'academic_period' => [
                    'academic_year' => $academicPeriod->academic_year,
                    'semester' => $academicPeriod->semester,
                ],
                'monthly_statistics' => $monthlyStatistics,
            ],
        ], 200);
    }
}