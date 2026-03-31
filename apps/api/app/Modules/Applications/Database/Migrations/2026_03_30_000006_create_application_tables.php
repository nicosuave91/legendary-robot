<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('client_id')->index();
            $table->string('application_number')->index();
            $table->string('owner_user_id')->nullable()->index();
            $table->string('product_type')->index();
            $table->string('external_reference')->nullable()->index();
            $table->decimal('amount_requested', 12, 2)->nullable();
            $table->string('status')->default('draft')->index();
            $table->timestampTz('submitted_at')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->string('created_by')->nullable()->index();
            $table->string('updated_by')->nullable()->index();
            $table->timestampsTz();
            $table->unique(['tenant_id', 'application_number']);
            $table->index(['tenant_id', 'client_id', 'created_at']);
        });

        Schema::create('application_status_history', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('application_id')->index();
            $table->string('actor_user_id')->nullable()->index();
            $table->string('from_status')->nullable();
            $table->string('to_status')->index();
            $table->text('reason')->nullable();
            $table->timestampTz('occurred_at')->index();
            $table->json('metadata')->nullable();
            $table->timestampsTz();
        });

        Schema::create('application_rule_applications', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('application_id')->index();
            $table->string('rule_key')->index();
            $table->string('rule_version');
            $table->string('trigger_event')->index();
            $table->string('outcome')->index();
            $table->string('title');
            $table->text('note_body');
            $table->boolean('is_blocking')->default(false)->index();
            $table->json('evidence')->nullable();
            $table->timestampTz('applied_at')->index();
            $table->timestampsTz();
            $table->index(['tenant_id', 'application_id', 'applied_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_rule_applications');
        Schema::dropIfExists('application_status_history');
        Schema::dropIfExists('applications');
    }
};
