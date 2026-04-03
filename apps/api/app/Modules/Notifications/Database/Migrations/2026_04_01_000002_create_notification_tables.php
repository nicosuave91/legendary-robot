<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('target_user_id')->nullable()->index();
            $table->string('audience_scope')->default('tenant');
            $table->string('notification_type');
            $table->string('category')->default('system');
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('tone')->default('info');
            $table->string('action_url')->nullable();
            $table->string('source_event_type');
            $table->string('source_event_id')->nullable();
            $table->json('payload_snapshot')->nullable();
            $table->timestampTz('emitted_at');
            $table->timestampsTz();

            $table->index(['tenant_id', 'emitted_at']);
            $table->index(['tenant_id', 'target_user_id', 'emitted_at']);
        });

        Schema::create('notification_reads', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('notification_id')->index();
            $table->string('user_id')->index();
            $table->timestampTz('read_at');
            $table->timestampsTz();

            $table->unique(['notification_id', 'user_id']);
        });

        Schema::create('toast_dismissals', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('notification_id')->index();
            $table->string('user_id')->index();
            $table->string('surface')->default('toast');
            $table->timestampTz('dismissed_at');
            $table->timestampsTz();

            $table->unique(['notification_id', 'user_id', 'surface']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('toast_dismissals');
        Schema::dropIfExists('notification_reads');
        Schema::dropIfExists('notifications');
    }
};
