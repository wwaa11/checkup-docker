<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NumberMaster extends Model
{
    protected $fillable = [
        'date',
        'hn',
        'name',
        'type',
        'prefer_english',
        'line',
    ];

    protected $casts = [
        'date'           => 'date',
        'checkin'        => 'datetime',
        'call'           => 'datetime',
        'success'        => 'datetime',
        'prefer_english' => 'boolean',
    ];

    protected $appends = [
        'waitingTime',
        'checkinTime',
        'callTime',
        'successTime',
    ];

    public function getCheckinTimeAttribute()
    {

        return $this->checkin ? $this->checkin->format('H:i') : null;
    }
    public function getCallTimeAttribute()
    {
        return $this->call ? $this->call->format('H:i') : null;
    }
    public function getSuccessTimeAttribute()
    {
        return $this->success ? $this->success->format('H:i') : null;
    }

    public function getWaitingTimeAttribute()
    {
        if ($this->call == null && $this->checkin !== null) {
            return number_format($this->checkin->diffInMinutes(now()), 0);
        } elseif ($this->call !== null) {
            return number_format($this->call->diffInMinutes(now()), 0);
        } elseif ($this->success !== null && $this->call !== null) {
            return number_format($this->success->diffInMinutes($this->call), 0);
        }

        return null;
    }

}
