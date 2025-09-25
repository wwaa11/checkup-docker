<?php
namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'userid',
        'name',
        'position',
        'department',
        'division',
        'station',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected $appends = [
        'stationRedirect',
    ];

    public function getStationRedirectAttribute()
    {
        $station = $this->station;
        switch ($station) {
            case 'vs':
                $link = route('stations.vs');
                break;
            case 'lab':
                $link = route('stations.lab');
                break;
            default:
                $link = route('stations.index');
                break;
        }

        return $link;
    }
}
