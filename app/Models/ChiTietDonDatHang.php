<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChiTietDonDatHang extends Model
{
    protected $table = 'tblChiTietDonDatHang';
    public $incrementing = false;
    public $timestamps = false;

    protected $primaryKey = null; // composite key

    protected $fillable = ['MaDonDatHang', 'MaNguyenLieu', 'SoLuongDat'];

    public function donDatHang()
    {
        return $this->belongsTo(DonDatHang::class, 'MaDonDatHang', 'MaDonDatHang');
    }

    public function nguyenLieu()
    {
        return $this->belongsTo(NguyenLieu::class, 'MaNguyenLieu', 'MaNguyenLieu');
    }
}
