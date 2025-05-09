<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    use HasFactory;
    protected $table = 'jadwal';
    protected $fillable = [
        'pesanan_id',
        'instruktur_id',
        'tanggal',
        'waktu_mulai',
        'waktu_selesai',
        'status',
    ];

    public $timestamps = true;

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class);
    }

    public function instruktur()
    {
        return $this->belongsTo(User::class, 'instruktur_id');
    }
}
