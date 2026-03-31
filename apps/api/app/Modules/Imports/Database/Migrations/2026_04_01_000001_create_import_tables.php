<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('imports', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('import_type');
            $table->string('file_format', 20)->default('csv');
            $table->string('status')->index();
            $table->string('uploaded_by_user_id')->nullable()->index();
            $table->string('validated_by_user_id')->nullable()->index();
            $table->string('committed_by_user_id')->nullable()->index();
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('storage_disk');
            $table->string('storage_path');
            $table->string('storage_reference');
            $table->string('mime_type');
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('checksum_sha256', 64)->nullable();
            $table->string('parser_version')->nullable();
            $table->unsignedInteger('row_count')->default(0);
            $table->unsignedInteger('valid_row_count')->default(0);
            $table->unsignedInteger('invalid_row_count')->default(0);
            $table->unsignedInteger('committed_row_count')->default(0);
            $table->json('summary_snapshot')->nullable();
            $table->json('failure_summary')->nullable();
            $table->uuid('last_correlation_id')->nullable()->index();
            $table->timestampTz('validated_at')->nullable();
            $table->timestampTz('committed_at')->nullable();
            $table->timestampsTz();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'created_at']);
        });

        Schema::create('import_rows', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('import_id')->index();
            $table->unsignedInteger('row_number');
            $table->string('row_status')->index();
            $table->json('raw_payload');
            $table->json('normalized_payload')->nullable();
            $table->string('target_subject_type')->nullable();
            $table->string('target_subject_id')->nullable();
            $table->json('failure_summary')->nullable();
            $table->timestampTz('validated_at')->nullable();
            $table->timestampTz('committed_at')->nullable();
            $table->timestampsTz();

            $table->unique(['import_id', 'row_number']);
            $table->index(['tenant_id', 'import_id', 'row_status']);
        });

        Schema::create('import_errors', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('import_id')->index();
            $table->string('import_row_id')->nullable()->index();
            $table->unsignedInteger('row_number');
            $table->string('field_name')->nullable();
            $table->string('error_code');
            $table->string('severity')->default('error');
            $table->text('message');
            $table->json('context_snapshot')->nullable();
            $table->timestampsTz();

            $table->index(['tenant_id', 'import_id', 'row_number']);
            $table->index(['tenant_id', 'severity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_errors');
        Schema::dropIfExists('import_rows');
        Schema::dropIfExists('imports');
    }
};
