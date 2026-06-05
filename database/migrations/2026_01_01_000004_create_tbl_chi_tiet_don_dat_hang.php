<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tblChiTietDonDatHang', function (Blueprint $table) {
            $table->string('MaDonDatHang', 10);
            $table->string('MaNguyenLieu', 10);
            $table->integer('SoLuongDat');

            $table->primary(['MaDonDatHang', 'MaNguyenLieu']);

            $table->foreign('MaDonDatHang')
                  ->references('MaDonDatHang')
                  ->on('tblDonDatHang');

            $table->foreign('MaNguyenLieu')
                  ->references('MaNguyenLieu')
                  ->on('tblNguyenLieu');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tblChiTietDonDatHang');
    }
};
