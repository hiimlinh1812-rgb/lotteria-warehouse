<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chi_tiet_phieu_xuat', function (Blueprint $table) {
            $table->string('MaPhieuXuat', 10);
            $table->string('MaLoHang', 10);
            $table->integer('SoLuongXuat');     // số lượng quản lý yêu cầu
            $table->integer('SoLuongThucLay')->nullable(); // nhân viên điền sau

            $table->primary(['MaPhieuXuat', 'MaLoHang']);
            $table->foreign('MaPhieuXuat')->references('MaPhieuXuat')->on('phieu_xuat_kho');
            $table->foreign('MaLoHang')->references('MaLoHang')->on('lo_hang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chi_tiet_phieu_xuat');
    }
};
