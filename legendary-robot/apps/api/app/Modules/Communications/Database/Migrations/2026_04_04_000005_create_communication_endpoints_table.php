<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('communication_endpoints', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('channel')->index();
            $table->string('provider_name')->index();
            $table->string('endpoint_kind')->default('phone_number');
            $table->string('address_display');
            $table->string('address_normalized')->index();
            $table->string('label')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_default_outbound')->default(false)->index();
            $table->json('metadata')->nullable();
            $table->string('created_by')->nullable()->index();
            $table->string('updated_by')->nullable()->index();
            $table->timestampsTz();

            $table->unique(['tenant_id', 'provider_name', 'channel', 'address_normalized'], 'communication_endpoint_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_endpoints');
    }
};
