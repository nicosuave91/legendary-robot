<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('rules', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('rule_key')->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('module_scope')->index();
            $table->string('subject_type')->index();
            $table->string('status')->default('draft')->index();
            $table->string('latest_published_version_id')->nullable()->index();
            $table->string('current_draft_version_id')->nullable()->index();
            $table->string('created_by')->nullable()->index();
            $table->string('updated_by')->nullable()->index();
            $table->timestampTz('retired_at')->nullable()->index();
            $table->timestampsTz();
            $table->unique(['tenant_id', 'rule_key']);
        });

        Schema::create('rule_versions', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('rule_id')->index();
            $table->unsignedInteger('version_number');
            $table->string('lifecycle_state')->default('draft')->index();
            $table->string('trigger_event')->index();
            $table->string('severity')->default('info')->index();
            $table->json('industry_scope')->nullable();
            $table->json('condition_definition');
            $table->json('action_definition');
            $table->string('execution_label')->nullable();
            $table->text('note_template')->nullable();
            $table->string('checksum');
            $table->timestampTz('published_at')->nullable()->index();
            $table->string('published_by')->nullable()->index();
            $table->string('supersedes_version_id')->nullable()->index();
            $table->string('created_by')->nullable()->index();
            $table->string('updated_by')->nullable()->index();
            $table->timestampsTz();
            $table->unique(['rule_id', 'version_number']);
        });

        Schema::create('rule_execution_logs', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('rule_id')->index();
            $table->string('rule_version_id')->index();
            $table->string('subject_type')->index();
            $table->string('subject_id')->index();
            $table->string('trigger_event')->index();
            $table->string('execution_source')->default('system')->index();
            $table->string('outcome')->index();
            $table->string('correlation_id')->nullable()->index();
            $table->string('actor_user_id')->nullable()->index();
            $table->json('context_snapshot')->nullable();
            $table->json('outcome_summary')->nullable();
            $table->timestampTz('executed_at')->index();
            $table->timestampsTz();
            $table->index(['tenant_id', 'subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rule_execution_logs');
        Schema::dropIfExists('rule_versions');
        Schema::dropIfExists('rules');
    }
};