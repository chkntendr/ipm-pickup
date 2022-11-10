<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pickup extends Model
{
    use HasFactory;

    protected $table = "data_pickup";
    protected $fillable = ['id', 'tipe_id', 'client_id', 'jumlah', 'berat', 'tanggal', 'driver_id', 'created_at', 'updated_at'];

    public function client() {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function tipe() {
        return $this->belongsTo(Tipe::class, 'tipe_id', 'id');
    }

    public function driver() {
        return $this->belongsTo(Driver::class, 'driver_id', 'id');
    }
}
