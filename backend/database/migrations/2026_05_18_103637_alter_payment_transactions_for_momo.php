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
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->string('request_id', 50)->nullable()->after('order_id');
            $table->string('transaction_id', 50)->nullable()->after('request_id');
            $table->string('order_id_momo', 50)->nullable()->after('transaction_id');
            $table->string('result_code', 10)->nullable()->after('amount');
            $table->string('message', 255)->nullable()->after('result_code');

            $table->index('request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropIndex(['request_id']);
            $table->dropColumn([
                'request_id',
                'transaction_id',
                'order_id_momo',
                'result_code',
                'message'
            ]);
        });
    }
};
