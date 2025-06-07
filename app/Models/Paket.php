<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paket extends Model
{
    use HasFactory;
    protected $table = 'paket';
    protected $primaryKey = 'id';
    protected $fillable = [
        'nama_paket',
        'jumlah_jam',
        'no_rekening',
        'deskripsi',
        'harga',
    ];

    public function pesanan()
    {
        return $this->hasMany(Pesanan::class);
    }
}