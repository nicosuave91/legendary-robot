<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('communication_threads', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('client_id')->index();
            $table->string('channel')->index();
            $table->string('participant_key')->nullable()->index();
            $table->string('subject_hint')->nullable();
            $table->string('created_by')->nullable()->index();
            $table->timestampTz('last_activity_at')->nullable()->index();
            $table->timestampsTz();
            $table->index(['tenant_id', 'client_id', 'channel']);
        });

        Schema::create('communication_messages', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('client_id')->index();
            $table->string('communication_thread_id')->nullable()->index();
            $table->string('channel')->index();
            $table->string('direction')->index();
            $table->string('lifecycle_status')->index();
            $table->string('provider_name')->nullable()->index();
            $table->string('provider_message_id')->nullable()->index();
            $table->string('provider_status')->nullable()->index();
            $table->string('from_address')->nullable()->index();
            $table->string('to_address')->nullable()->index();
            $table->string('subject')->nullable();
            $table->longText('body_text')->nullable();
            $table->longText('body_html')->nullable();
            $table->string('idempotency_key')->nullable()->index();
            $table->string('correlation_key')->unique();
            $table->timestampTz('queued_at')->nullable()->index();
            $table->timestampTz('submitted_at')->nullable()->index();
            $table->timestampTz('finalized_at')->nullable()->index();
            $table->string('failure_code')->nullable();
            $table->text('failure_message')->nullable();
            $table->string('created_by')->nullable()->index();
            $table->timestampsTz();
            $table->index(['tenant_id', 'provider_name', 'provider_message_id']);
            $table->index(['tenant_id', 'client_id', 'created_at']);
        });

        Schema::create('communication_attachments', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('client_id')->index();
            $table->string('attachable_type')->index();
            $table->string('attachable_id')->index();
            $table->string('source_channel')->index();
            $table->string('provenance')->index();
            $table->string('storage_disk');
            $table->string('storage_path');
            $table->string('storage_reference');
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('mime_type');
            $table->unsignedBigInteger('size_bytes');
            $table->string('checksum_sha256')->nullable();
            $table->string('scan_status')->default('pending')->index();
            $table->string('provider_attachment_id')->nullable()->index();
            $table->string('uploaded_by')->nullable()->index();
            $table->timestampsTz();
            $table->index(['tenant_id', 'attachable_type', 'attachable_id'], 'communication_attachment_attachable_idx');
        });

        Schema::create('call_logs', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('client_id')->index();
            $table->string('direction')->index();
            $table->string('lifecycle_status')->index();
            $table->string('provider_name')->nullable()->index();
            $table->string('provider_call_id')->nullable()->index();
            $table->string('from_number')->nullable()->index();
            $table->string('to_number')->nullable()->index();
            $table->string('correlation_key')->unique();
            $table->timestampTz('queued_at')->nullable()->index();
            $table->timestampTz('started_at')->nullable()->index();
            $table->timestampTz('ended_at')->nullable()->index();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->string('failure_code')->nullable();
            $table->text('failure_message')->nullable();
            $table->string('initiated_by')->nullable()->index();
            $table->timestampsTz();
            $table->index(['tenant_id', 'provider_name', 'provider_call_id']);
        });

        Schema::create('email_logs', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('client_id')->index();
            $table->string('communication_message_id')->unique();
            $table->string('provider_name')->nullable()->index();
            $table->string('provider_message_id')->nullable()->index();
            $table->string('from_email')->nullable();
            $table->json('to_emails');
            $table->json('cc_emails')->nullable();
            $table->json('bcc_emails')->nullable();
            $table->string('reply_to_email')->nullable();
            $table->json('provider_metadata')->nullable();
            $table->timestampTz('last_provider_event_at')->nullable()->index();
            $table->timestampsTz();
            $table->index(['tenant_id', 'provider_name', 'provider_message_id']);
        });

        Schema::create('delivery_status_events', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('client_id')->nullable()->index();
            $table->string('subject_type')->index();
            $table->string('subject_id')->index();
            $table->string('provider_name')->nullable()->index();
            $table->string('provider_reference')->nullable()->index();
            $table->string('provider_event_id')->nullable()->index();
            $table->string('provider_event_type')->index();
            $table->string('provider_status')->nullable()->index();
            $table->timestampTz('occurred_at')->nullable()->index();
            $table->timestampTz('received_at')->index();
            $table->string('correlation_key')->nullable()->index();
            $table->boolean('signature_verified')->default(false);
            $table->string('dedupe_hash')->index();
            $table->json('raw_payload');
            $table->string('status_before')->nullable();
            $table->string('status_after')->nullable();
            $table->timestampsTz();
            $table->unique(['tenant_id', 'dedupe_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_status_events');
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('call_logs');
        Schema::dropIfExists('communication_attachments');
        Schema::dropIfExists('communication_messages');
        Schema::dropIfExists('communication_threads');
    }
};
