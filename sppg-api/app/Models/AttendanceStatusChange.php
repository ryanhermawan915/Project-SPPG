<?php

namespace App\Models;

use App\Models\AttendanceStatusChange;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceStatusChange extends Model
{
    protected $fillable = [
        'attendance_record_id',
        'admin_id',
        'old_status',
        'new_status',
        'reason',
    ];

    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
