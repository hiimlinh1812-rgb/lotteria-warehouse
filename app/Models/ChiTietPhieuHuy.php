<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChiTietPhieuHuy extends Model
{
    public $timestamps = false;
    protected $table = 'chitietphieuhuy';
    public $incrementing = false;

    protected $fillable = [
        'MaPhieuHuy',
        'MaNguyenLieu',
        'SoLuongHuy'
    ];
}
