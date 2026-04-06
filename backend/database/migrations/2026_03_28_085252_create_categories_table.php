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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name', 100);
            $table->string('slug', 150)->unique();
            $table->string('image', 255)->nullable();
            $table->text('description')->nullable();
            $table->integer('display')->default(0);
            $table->boolean('status')->default(true);
            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('set null')->onUpdate('cascade');
            $table->index(['parent_id', 'status', 'display']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
