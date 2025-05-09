<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    use HasFactory;

    protected $table = 'pesanan'; // pastikan ini benar
    protected $primaryKey = 'id';

    protected $fillable = [
        'paket_id',
        'user_id',
        'mobil',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function paket()
    {
        return $this->belongsTo(Paket::class);
    }

    public function jadwal()
    {
        return $this->hasMany(Jadwal::class);
    }
}
