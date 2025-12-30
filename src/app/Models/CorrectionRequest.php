<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'work_start_time',
        'work_end_time',
        'note',
        'status',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
