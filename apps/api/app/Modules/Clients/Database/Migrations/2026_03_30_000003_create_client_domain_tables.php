<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('owner_user_id')->nullable()->index();
            $table->string('created_by')->nullable()->index();
            $table->string('display_name')->index();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('company_name')->nullable();
            $table->string('primary_email')->nullable()->index();
            $table->string('primary_phone')->nullable()->index();
            $table->string('preferred_contact_channel')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('status')->default('lead')->index();
            $table->timestampTz('last_activity_at')->nullable()->index();
            $table->timestampsTz();
        });

        Schema::create('client_addresses', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('client_id')->unique();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state_code', 2)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->timestampsTz();
        });

        Schema::create('client_notes', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('client_id')->index();
            $table->string('author_user_id')->index();
            $table->string('source_type')->default('user');
            $table->text('body');
            $table->boolean('is_editable')->default(true);
            $table->timestampsTz();
        });

        Schema::create('client_documents', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('client_id')->index();
            $table->string('uploaded_by_user_id')->index();
            $table->string('provenance')->default('manual_upload');
            $table->string('attachment_category')->nullable();
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('storage_disk');
            $table->string('storage_path');
            $table->string('storage_reference');
            $table->string('mime_type');
            $table->unsignedBigInteger('size_bytes');
            $table->string('checksum_sha256')->nullable();
            $table->timestampsTz();
        });

        Schema::create('client_status_history', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('client_id')->index();
            $table->string('actor_user_id')->nullable()->index();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->string('reason')->nullable();
            $table->timestampTz('occurred_at')->index();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_status_history');
        Schema::dropIfExists('client_documents');
        Schema::dropIfExists('client_notes');
        Schema::dropIfExists('client_addresses');
        Schema::dropIfExists('clients');
    }
};
