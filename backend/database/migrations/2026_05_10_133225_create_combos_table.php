<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('combos', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('thumbnail', 255)->nullable();
            $table->enum('combo_type', ['fixed_combo', 'buy_get', 'bundle']);
            $table->enum('discount_type', ['percentage', 'fixed_price', 'bundle_price'])->default('percentage');
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('status')->default(true);
            $table->integer('display')->default(0);
            $table->timestamps();

            $table->index('combo_type');
            $table->index('status');
            $table->index('starts_at');
            $table->index('ends_at');
            $table->index(['status', 'starts_at', 'ends_at'], 'combos_status_dates_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('combos');
    }
};
