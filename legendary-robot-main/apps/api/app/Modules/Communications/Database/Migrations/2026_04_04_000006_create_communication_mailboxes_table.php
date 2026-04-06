<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('communication_mailboxes', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('client_id')->index();
            $table->string('communication_thread_id')->index();
            $table->string('provider_name')->index();
            $table->string('alias_local_part');
            $table->string('inbound_address');
            $table->string('label')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->json('metadata')->nullable();
            $table->string('created_by')->nullable()->index();
            $table->string('updated_by')->nullable()->index();
            $table->timestampsTz();

            $table->unique(['tenant_id', 'provider_name', 'communication_thread_id'], 'communication_mailboxes_thread_unique');
            $table->unique(['tenant_id', 'provider_name', 'alias_local_part'], 'communication_mailboxes_alias_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_mailboxes');
    }
};
