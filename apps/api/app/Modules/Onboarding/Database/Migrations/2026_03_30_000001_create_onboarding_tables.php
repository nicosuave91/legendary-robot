<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('user_id')->unique()->index();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->date('birthday')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state_code', 2)->nullable();
            $table->string('postal_code', 12)->nullable();
            $table->timestampTz('profile_confirmed_at')->nullable();
            $table->timestampsTz();
        });

        Schema::create('onboarding_states', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('user_id')->unique()->index();
            $table->string('state')->index();
            $table->timestampTz('required_at')->nullable();
            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('completed_at')->nullable();
            $table->timestampTz('exempted_at')->nullable();
            $table->timestampTz('reset_at')->nullable();
            $table->timestampsTz();
        });

        Schema::create('user_industry_assignments', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('user_id')->unique()->index();
            $table->string('industry');
            $table->string('config_version')->default('v1');
            $table->timestampTz('assigned_at')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_industry_assignments');
        Schema::dropIfExists('onboarding_states');
        Schema::dropIfExists('user_profiles');
    }
};
