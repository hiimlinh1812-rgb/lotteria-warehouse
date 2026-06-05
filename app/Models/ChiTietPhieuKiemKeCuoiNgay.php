<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChiTietPhieuKiemKeCuoiNgay extends Model
{
    public $timestamps = false;
    protected $table = 'chitietphieukiemkecuoingay';
    public $incrementing = false;

    protected $fillable = [
        'MaPhieuKiemKe',
        'MaNguyenLieu',
        'SoLuongHeThong',
        'SoLuongThucTe',
        'ChenhLech',
        'TinhTrang'
    ];
}
