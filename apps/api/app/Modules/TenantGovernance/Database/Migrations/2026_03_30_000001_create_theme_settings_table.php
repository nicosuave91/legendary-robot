<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('theme_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id')->unique();
            $table->string('primary_color')->default('#1d4ed8');
            $table->string('secondary_color')->default('#0f172a');
            $table->string('tertiary_color')->default('#64748b');
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_settings');
    }
};
