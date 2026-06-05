<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhieuXuatHuy extends Model
{
    public $timestamps = false;
    protected $table = 'phieuxuathuy';
    protected $primaryKey = 'MaPhieuHuy';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaPhieuHuy',
        'NgayTao',
        'LyDoHuy',
        'TrangThai',
        'MaTaiKhoan',
        'MaPhieuKiemKe'
    ];
}
