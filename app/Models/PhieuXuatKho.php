<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhieuXuatKho extends Model
{
    protected $table = 'phieuxuatkho';
    protected $primaryKey = 'MaPhieuXuat';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'MaPhieuXuat',
        'NgayXuat',
        'TrangThai',
        'MaTaiKhoan'
    ];
}
