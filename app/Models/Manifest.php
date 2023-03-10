<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;
use App\Models\Invoice;

class Manifest extends Model
{
    use HasFactory;
    
    protected $table = "manifest";
    protected $fillable = [
        'id',
        'manifest_id',
        'tipe',
        'client',
        'done_date',
        'total',
        'timestamps'
    ];
}
