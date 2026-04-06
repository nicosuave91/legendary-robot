<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('client_id')->nullable()->index();
            $table->string('owner_user_id')->nullable()->index();
            $table->string('created_by')->nullable()->index();
            $table->string('updated_by')->nullable()->index();
            $table->string('title')->index();
            $table->text('description')->nullable();
            $table->string('event_type')->index();
            $table->string('status')->default('scheduled')->index();
            $table->timestampTz('starts_at')->index();
            $table->timestampTz('ends_at')->nullable()->index();
            $table->boolean('is_all_day')->default(false);
            $table->string('location')->nullable();
            $table->json('metadata')->nullable();
            $table->timestampsTz();

            $table->index(['tenant_id', 'starts_at']);
            $table->index(['tenant_id', 'client_id', 'starts_at']);
            $table->index(['tenant_id', 'owner_user_id', 'starts_at']);
        });

        Schema::create('event_tasks', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('event_id')->index();
            $table->string('assigned_user_id')->nullable()->index();
            $table->string('created_by')->nullable()->index();
            $table->string('updated_by')->nullable()->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('open')->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->timestampTz('due_at')->nullable()->index();
            $table->timestampTz('completed_at')->nullable();
            $table->text('blocked_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestampsTz();

            $table->index(['tenant_id', 'event_id', 'sort_order']);
            $table->index(['tenant_id', 'status', 'due_at']);
        });

        Schema::create('task_status_history', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('event_task_id')->index();
            $table->string('event_id')->index();
            $table->string('actor_user_id')->nullable()->index();
            $table->string('from_status')->nullable();
            $table->string('to_status')->index();
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestampTz('occurred_at')->index();
            $table->timestampsTz();

            $table->index(['tenant_id', 'event_task_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_status_history');
        Schema::dropIfExists('event_tasks');
        Schema::dropIfExists('events');
    }
};
