<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TruyVetDonDatHang', function (Blueprint $table) {
            $table->id();
            $table->string('MaDonDatHang', 10)->index();
            $table->string('HanhDong', 100);
            $table->string('TrangThaiTruoc', 50)->nullable();
            $table->string('TrangThaiSau', 50)->nullable();
            $table->string('MaTaiKhoan', 10)->nullable();
            $table->string('NoiDung', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TruyVetDonDatHang');
    }
};
