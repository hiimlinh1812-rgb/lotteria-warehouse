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
        Schema::create('tai_khoans', function (Blueprint $table) {
        $table->string('MaTaiKhoan', 10)->primary(); 
        $table->string('HoTen', 100); 
        $table->string('MatKhau', 255); 
        $table->string('SoDienThoai', 10)->unique(); 
        $table->string('VaiTro', 50); 
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tai_khoans');
    }
};
