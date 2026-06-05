<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhieuNhanHang extends Model
{
    protected $table = 'tblPhieuNhanHang';
    protected $primaryKey = 'MaPhieuNhan';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['MaPhieuNhan', 'NgayNhan', 'TrangThai', 'GhiChu', 'MaTaiKhoan', 'MaDonDatHang'];

    // Trạng thái hợp lệ
    const TRANG_THAI_CHO_NHAN = 'Chờ nhận hàng';
    const TRANG_THAI_DA_NHAN  = 'Đã nhận hàng';
    const TRANG_THAI_CHO_XU_LY = 'Chờ xử lý';
    const TRANG_THAI_DANG_DOI_TRA = 'Đang xử lý đổi/trả';
    const TRANG_THAI_HOAN_TAT = 'Hoàn tất';

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'MaTaiKhoan', 'MaTaiKhoan');
    }

    public function donDatHang()
    {
        return $this->belongsTo(DonDatHang::class, 'MaDonDatHang', 'MaDonDatHang');
    }

    public function loHangs()
    {
        return $this->hasMany(LoHang::class, 'MaPhieuNhan', 'MaPhieuNhan');
    }

    public function phieuDoiTras()
    {
        return $this->hasMany(PhieuDoiTra::class, 'MaPhieuNhan', 'MaPhieuNhan');
    }

    public function phieuNhapKho()
    {
        return $this->hasOne(PhieuNhapKho::class, 'MaPhieuNhan', 'MaPhieuNhan');
    }
}
