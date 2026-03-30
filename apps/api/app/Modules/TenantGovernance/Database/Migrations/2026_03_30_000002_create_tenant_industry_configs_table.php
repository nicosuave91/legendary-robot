<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tenant_industry_configs', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('industry')->index();
            $table->string('version')->index();
            $table->string('status')->default('draft')->index();
            $table->boolean('is_active')->default(false)->index();
            $table->json('capabilities');
            $table->text('notes')->nullable();
            $table->string('created_by')->nullable()->index();
            $table->timestampTz('published_at')->nullable();
            $table->timestampTz('activated_at')->nullable();
            $table->timestampsTz();

            $table->unique(['tenant_id', 'industry', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_industry_configs');
    }
};
