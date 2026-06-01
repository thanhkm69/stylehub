<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Chuyển cột status từ boolean (0/1) sang tinyInteger (0/1/2)
     * 0 = Đã ẩn, 1 = Hiển thị, 2 = Chờ duyệt
     */
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->unsignedTinyInteger('status')->default(1)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->boolean('status')->default(true)->change();
        });
    }
};
