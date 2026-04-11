<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id')->index();
            $table->string('actor_id')->nullable()->index();
            $table->string('action')->index();
            $table->string('subject_type');
            $table->string('subject_id')->nullable();
            $table->uuid('correlation_id')->nullable()->index();
            $table->json('before_summary')->nullable();
            $table->json('after_summary')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
