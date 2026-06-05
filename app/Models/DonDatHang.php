<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonDatHang extends Model
{
    protected $table = 'tblDonDatHang';
    protected $primaryKey = 'MaDonDatHang';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['MaDonDatHang', 'NgayDat', 'TrangThai', 'GhiChu', 'MaTaiKhoan'];

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'MaTaiKhoan', 'MaTaiKhoan');
    }

    public function chiTietDonDatHangs()
    {
        return $this->hasMany(ChiTietDonDatHang::class, 'MaDonDatHang', 'MaDonDatHang');
    }

    public function phieuNhanHangs()
    {
        return $this->hasMany(PhieuNhanHang::class, 'MaDonDatHang', 'MaDonDatHang');
    }

    public function nguyenLieus()
    {
        return $this->belongsToMany(
            NguyenLieu::class,
            'tblChiTietDonDatHang',
            'MaDonDatHang',
            'MaNguyenLieu'
        )->withPivot('SoLuongDat');
    }
}
