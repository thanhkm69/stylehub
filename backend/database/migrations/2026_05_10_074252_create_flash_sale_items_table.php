<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flash_sale_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('flash_sale_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_variant_id')->nullable();
            $table->enum('discount_type', ['percentage', 'fixed_price'])->default('percentage');
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('original_price', 15, 2);
            $table->decimal('sale_price', 15, 2);
            $table->boolean('status')->default(true);
            $table->integer('display')->default(0);
            $table->timestamps();

            $table->foreign('flash_sale_id')->references('id')->on('flash_sales')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('product_variant_id')->references('id')->on('product_variants')->onDelete('cascade');

            $table->unique(['flash_sale_id', 'product_id', 'product_variant_id'], 'fsi_unique');

            $table->index('flash_sale_id');
            $table->index('product_id');
            $table->index('product_variant_id');
            $table->index('status');
            $table->index(['flash_sale_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flash_sale_items');
    }
};
