<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('combos', 'discount_type')) {
            DB::table('combos')
                ->where('discount_type', 'bundle_price')
                ->update(['discount_type' => 'fixed_price']);
        }

        if (Schema::hasColumn('combos', 'combo_type')) {
            Schema::table('combos', function (Blueprint $table) {
                $table->dropIndex(['combo_type']);
                $table->dropColumn('combo_type');
            });
        }

        if (Schema::hasColumn('combo_items', 'role')) {
            Schema::table('combo_items', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('combos', 'combo_type')) {
            Schema::table('combos', function (Blueprint $table) {
                $table->enum('combo_type', ['fixed_combo', 'buy_get', 'bundle'])->default('fixed_combo');
                $table->index('combo_type');
            });
        }

        if (! Schema::hasColumn('combo_items', 'role')) {
            Schema::table('combo_items', function (Blueprint $table) {
                $table->enum('role', ['main', 'gift', 'bundle'])->default('main');
            });
        }
    }
};
