<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\WorkSchedule;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AttendanceRecord;
use App\Models\AcademicPeriod;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil pengguna yang sedang login
        $admin = $request->user();

        // 2. Pastikan pengguna adalah admin aktif
        if (
            $admin->role !== 'admin' ||
            $admin->account_status !== 'active'
        ) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses dashboard admin.',
            ], 403);
        }

        // 3. Tentukan tanggal hari ini
        $today = Carbon::today();

        // 4. Ambil seluruh pegawai aktif
        $employees = User::where('role', 'employee')
            ->where('account_status', 'active');

        $totalEmployees = (clone $employees)->count();

        // 5. Ambil data presensi hari ini
        $attendanceRecords = AttendanceRecord::whereDate(
                'attendance_date',
                $today
            )->whereIn(
                'user_id',
                (clone $employees)->select('id')
            );

        // 6. Hitung jumlah setiap status presensi
        $presentCount = (clone $attendanceRecords)
            ->where('status', 'present')
            ->count();

        $lateCount = (clone $attendanceRecords)
            ->where('status', 'late')
            ->count();

        $permissionCount = (clone $attendanceRecords)
            ->where('status', 'permission')
            ->count();

        $sickCount = (clone $attendanceRecords)
            ->where('status', 'sick')
            ->count();

        $absentCount = (clone $attendanceRecords)
            ->where('status', 'absent')
            ->count();

        // 7. Hitung jumlah pegawai yang belum memiliki catatan presensi
        // Periksa apakah hari ini merupakan hari kerja
        $hasWorkScheduleToday = WorkSchedule::where(
            'day_of_week',
            $today->dayOfWeek
        )
            ->where('is_active', true)
            ->exists();
            
        $recordedCount = (clone $attendanceRecords)
            ->whereIn('user_id', (clone $employees)->select('id'))
            ->distinct('user_id')
            ->count('user_id');

        $notRecordedCount = $hasWorkScheduleToday
            ? max(0, $totalEmployees - $recordedCount)
            : 0;

        // 8. Hitung persentase kehadiran
        // Hitung jumlah pegawai yang wajib melakukan presensi hari ini
        $expectedEmployees = $hasWorkScheduleToday
            ? $totalEmployees
            : 0;

        // Hitung pegawai yang belum memiliki catatan presensi
        $notRecordedCount = max(
            0,
            $expectedEmployees - $recordedCount
        );

        // Hitung persentase kehadiran
        $attendancePercentage = $expectedEmployees > 0
            ? round(
                (($presentCount + $lateCount) / $expectedEmployees) * 100,
                2
            )
            : 0;


        // 9. Kirim respons dashboard
        return response()->json([
            'message' => 'Dashboard admin berhasil diambil.',
            'data' => [
                'date' => $today->format('Y-m-d'),

                'employees' => [
                    'total_active' => $totalEmployees,
                    'expected_attendance' => $expectedEmployees,
                ],

                'attendance' => [
                    'present' => $presentCount,
                    'late' => $lateCount,
                    'permission' => $permissionCount,
                    'sick' => $sickCount,
                    'absent' => $absentCount,
                    'not_recorded' => $notRecordedCount,
                    'attendance_percentage' => $attendancePercentage,
                    'is_working_day' => $hasWorkScheduleToday,
                ],
            ],
        ], 200);
    }


    /**
     * Menampilkan statistik presensi bulanan seluruh pegawai aktif.
     */
    public function monthlyAttendance(Request $request)
    {
        $admin = $request->user();

        // Pastikan hanya admin aktif yang dapat mengakses.
        if (
            $admin->role !== 'admin' ||
            $admin->account_status !== 'active'
        ) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses dashboard admin.',
            ], 403);
        }

        // Validasi filter periode akademik.
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

        // Ambil periode akademik.
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

        // Tentukan batas tanggal perhitungan.
        $today = Carbon::today();

        $calculationEndDate = $academicPeriod->end_date->lt($today)
            ? $academicPeriod->end_date->copy()
            : $today->copy();

        // Ambil jumlah seluruh pegawai aktif.
        $totalEmployees = User::where('role', 'employee')
            ->where('account_status', 'active')
            ->count();

        // Ambil hari kerja aktif.
        $activeWorkDays = WorkSchedule::where('is_active', true)
            ->pluck('day_of_week')
            ->map(fn ($day) => (int) $day)
            ->toArray();

        // Ambil data presensi seluruh pegawai aktif.
        $attendanceRecords = AttendanceRecord::whereBetween(
            'attendance_date',
            [
                $academicPeriod->start_date,
                $calculationEndDate,
            ]
        )
            ->whereIn('user_id', function ($query) {
                $query->select('id')
                    ->from('users')
                    ->where('role', 'employee')
                    ->where('account_status', 'active');
            })
            ->get();

        $monthlyStatistics = [];

        // Proses jika periode akademik sudah dimulai.
        if ($calculationEndDate->gte($academicPeriod->start_date)) {

            // Kelompokkan presensi berdasarkan bulan.
            $recordsByMonth = $attendanceRecords->groupBy(
                fn ($record) => $record->attendance_date->format('Y-m')
            );

            // Tentukan bulan pertama dan terakhir.
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

                // Hitung hari kerja.
                $workingDays = 0;

                $datePeriod = CarbonPeriod::create(
                    $monthStart,
                    $monthEnd
                );

                foreach ($datePeriod as $date) {
                    if (in_array($date->dayOfWeek, $activeWorkDays)) {
                        $workingDays++;
                    }
                }

                // Hitung jumlah setiap status presensi.
                $presentCount = $records->where('status', 'present')->count();
                $lateCount = $records->where('status', 'late')->count();
                $permissionCount = $records->where('status', 'permission')->count();
                $sickCount = $records->where('status', 'sick')->count();
                $absentCount = $records->where('status', 'absent')->count();

                // Hitung total kewajiban presensi.
                $expectedAttendance = $workingDays * $totalEmployees;

                // Hitung jumlah catatan presensi.
                $recordedCount = $records
                    ->unique(fn ($record) => $record->user_id . '-' . $record->attendance_date->format('Y-m-d'))
                    ->count();

                // Hitung presensi yang belum tercatat.
                $notRecordedCount = max(
                    0,
                    $expectedAttendance - $recordedCount
                );

                // Hitung persentase kehadiran.
                $attendancePercentage = $expectedAttendance > 0
                    ? round(
                        (($presentCount + $lateCount) / $expectedAttendance) * 100,
                        2
                    )
                    : 0;

                $monthlyStatistics[] = [
                    'month' => $monthKey,
                    'total_active_employees' => $totalEmployees,
                    'working_days' => $workingDays,
                    'expected_attendance' => $expectedAttendance,
                    'present' => $presentCount,
                    'late' => $lateCount,
                    'permission' => $permissionCount,
                    'sick' => $sickCount,
                    'absent' => $absentCount,
                    'not_recorded' => $notRecordedCount,
                    'attendance_percentage' => $attendancePercentage,
                ];

                $currentMonth->addMonth();
            }
        }

        return response()->json([
            'message' => 'Statistik presensi bulanan admin berhasil diambil.',
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
