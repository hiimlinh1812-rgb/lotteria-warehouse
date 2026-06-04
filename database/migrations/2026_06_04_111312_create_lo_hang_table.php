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
        Schema::create('lo_hang', function (Blueprint $table) {
            $table->string('MaLoHang', 10)->primary();
            $table->date('NgaySanXuat');
            $table->date('HanSuDung');
            $table->integer('SoLuongNhap');
            $table->integer('SoLuongConLai');
            $table->string('TrangThai', 50); // 'Còn hạn', 'Sắp hết hạn', 'Hết hạn'
            $table->string('MaNguyenLieu', 10);
            $table->string('MaPhieuNhan', 10)->nullable();
            $table->string('MaPhieuDoiTra', 10)->nullable();
            $table->string('MaPhieuNhap', 10)->nullable();

            $table->foreign('MaNguyenLieu')->references('MaNguyenLieu')->on('nguyen_lieu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lo_hang');
    }
};
