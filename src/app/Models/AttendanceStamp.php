<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceStamp extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'type',       // work_start / work_end / break_start / break_end
        'stamped_at',
    ];

    /**
     * この打刻が属する勤怠
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
