<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('workflows', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('workflow_key')->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('draft')->index();
            $table->string('latest_published_version_id')->nullable()->index();
            $table->string('current_draft_version_id')->nullable()->index();
            $table->string('created_by')->nullable()->index();
            $table->string('updated_by')->nullable()->index();
            $table->timestampTz('retired_at')->nullable()->index();
            $table->timestampsTz();
            $table->unique(['tenant_id', 'workflow_key']);
        });

        Schema::create('workflow_versions', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('workflow_id')->index();
            $table->unsignedInteger('version_number');
            $table->string('lifecycle_state')->default('draft')->index();
            $table->json('trigger_definition');
            $table->json('steps_definition');
            $table->string('checksum');
            $table->timestampTz('published_at')->nullable()->index();
            $table->string('published_by')->nullable()->index();
            $table->string('supersedes_version_id')->nullable()->index();
            $table->string('created_by')->nullable()->index();
            $table->string('updated_by')->nullable()->index();
            $table->timestampsTz();
            $table->unique(['workflow_id', 'version_number']);
        });

        Schema::create('workflow_runs', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('workflow_id')->index();
            $table->string('workflow_version_id')->index();
            $table->string('trigger_event')->index();
            $table->string('subject_type')->index();
            $table->string('subject_id')->index();
            $table->string('status')->default('queued')->index();
            $table->unsignedInteger('current_step_index')->nullable();
            $table->string('idempotency_key')->unique();
            $table->string('correlation_id')->nullable()->index();
            $table->json('trigger_payload_snapshot')->nullable();
            $table->json('runtime_context')->nullable();
            $table->timestampTz('queued_at')->index();
            $table->timestampTz('started_at')->nullable()->index();
            $table->timestampTz('completed_at')->nullable()->index();
            $table->timestampTz('failed_at')->nullable()->index();
            $table->json('failure_summary')->nullable();
            $table->timestampsTz();
            $table->index(['tenant_id', 'workflow_id', 'queued_at']);
        });

        Schema::create('workflow_run_logs', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('workflow_run_id')->index();
            $table->string('workflow_version_id')->index();
            $table->unsignedInteger('step_index')->nullable()->index();
            $table->string('log_type')->index();
            $table->text('message');
            $table->json('payload_snapshot')->nullable();
            $table->timestampTz('occurred_at')->index();
            $table->timestampsTz();
            $table->index(['tenant_id', 'workflow_run_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_run_logs');
        Schema::dropIfExists('workflow_runs');
        Schema::dropIfExists('workflow_versions');
        Schema::dropIfExists('workflows');
    }
};