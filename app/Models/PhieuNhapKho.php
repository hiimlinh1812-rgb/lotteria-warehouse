<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhieuNhapKho extends Model
{
    protected $table = 'tblPhieuNhapKho';
    protected $primaryKey = 'MaPhieuNhap';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'MaPhieuNhap', 'NgayNhap', 'GhiChu', 'TrangThai',
        'MaTaiKhoan', 'MaPhieuNhan'
    ];

    const TRANG_THAI_CHO_NHAP = 'Chờ nhập';
    const TRANG_THAI_DA_NHAP  = 'Đã nhập';
    const TRANG_THAI_HOAN_TAT = 'Hoàn tất';

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'MaTaiKhoan', 'MaTaiKhoan');
    }

    public function phieuNhanHang()
    {
        return $this->belongsTo(PhieuNhanHang::class, 'MaPhieuNhan', 'MaPhieuNhan');
    }

    public function loHangs()
    {
        return $this->hasMany(LoHang::class, 'MaPhieuNhap', 'MaPhieuNhap');
    }
}
