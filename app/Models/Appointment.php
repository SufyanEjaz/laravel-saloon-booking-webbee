<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'start_time',
        'duration',
        'num_clients'
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
