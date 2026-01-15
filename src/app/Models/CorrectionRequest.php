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
        'requested_start_time',
        'requested_end_time',
        'reason',
        'status',
    ];

    protected $casts = [
        'requested_start_time' => 'datetime',
        'requested_end_time' => 'datetime',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breakCorrections()
    {
        return $this->hasMany(BreakCorrection::class);
    }
}
