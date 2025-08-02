<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/BarangKeluar.php
class BarangKeluar extends Model
{
    protected $table = 'keluar'; // Nama tabel di DB
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['idbarang', 'tgl', 'qty', 'hrg', 'updated'];
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'idbarang');
    }
}

