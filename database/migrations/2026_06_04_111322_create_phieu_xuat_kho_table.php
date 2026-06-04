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
        Schema::create('phieu_xuat_kho', function (Blueprint $table) {
            $table->string('MaPhieuXuat', 10)->primary();
            $table->date('NgayXuat');
            $table->string('TrangThai', 50)->default('Chờ xuất'); // 'Chờ xuất', 'Hoàn tất'
            $table->string('MaTaiKhoan', 10)->nullable();

            $table->foreign('MaTaiKhoan')->references('MaTaiKhoan')->on('tai_khoan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phieu_xuat_kho');
    }
};
