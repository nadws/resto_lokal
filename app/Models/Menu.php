<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;
    protected $table = 'tb_menu';
    protected $fillable = [
        'id_kategori','id_station', 'kd_menu', 'nm_menu', 'tipe', 'jenis', 'lokasi', 'image', 'aktif', 'tgl_sold', 'id_menu','id_handicap'
    ];
}
