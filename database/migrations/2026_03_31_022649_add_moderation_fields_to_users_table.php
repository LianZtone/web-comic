<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('warning_count')->default(0)->after('is_admin');
            $table->text('last_warning_reason')->nullable()->after('warning_count');
            $table->timestamp('last_warned_at')->nullable()->after('last_warning_reason');
            $table->boolean('hide_all_comments')->default(false)->after('last_warned_at');
            $table->timestamp('comments_hidden_at')->nullable()->after('hide_all_comments');
            $table->text('comments_hidden_reason')->nullable()->after('comments_hidden_at');
            $table->timestamp('suspended_until')->nullable()->after('comments_hidden_reason');
            $table->text('suspension_reason')->nullable()->after('suspended_until');
            $table->timestamp('banned_at')->nullable()->after('suspension_reason');
            $table->text('banned_reason')->nullable()->after('banned_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'warning_count',
                'last_warning_reason',
                'last_warned_at',
                'hide_all_comments',
                'comments_hidden_at',
                'comments_hidden_reason',
                'suspended_until',
                'suspension_reason',
                'banned_at',
                'banned_reason',
            ]);
        });
    }
};
