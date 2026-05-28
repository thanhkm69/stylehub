<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_comments', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('user_id')->constrained('post_comments')->cascadeOnDelete();
            $table->string('moderation_status', 20)->default('not_checked')->after('status');
            $table->text('moderation_reason')->nullable()->after('moderation_status');
            $table->json('moderation_categories')->nullable()->after('moderation_reason');
            $table->timestamp('moderated_at')->nullable()->after('moderation_categories');
        });
    }

    public function down(): void
    {
        Schema::table('post_comments', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn([
                'parent_id',
                'moderation_status',
                'moderation_reason',
                'moderation_categories',
                'moderated_at',
            ]);
        });
    }
};
