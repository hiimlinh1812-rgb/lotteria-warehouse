<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhieuKiemKe extends Model
{
    public $timestamps = false;
    protected $table = 'phieukiemke';
    protected $primaryKey = 'MaPhieuKiemKe';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaPhieuKiemKe',
        'NgayKiemKe',
        'LoaiKiemKe',
        'TrangThai',
        'GhiChu',
        'MaTaiKhoan'
    ];
}
