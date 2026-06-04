<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoHang extends Model
{
    protected $table = 'lohang';
    protected $primaryKey = 'MaLoHang';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'MaLoHang',
        'NgaySanXuat',
        'HanSuDung',
        'SoLuongNhap',
        'SoLuongConLai',
        'TrangThai',
        'MaNguyenLieu',
        'MaPhieuNhan',
        'MaPhieuDoiTra',
        'MaPhieuNhap'
    ];
}
