<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('application_rule_applications', function (Blueprint $table): void {
            $table->string('rule_id')->nullable()->after('application_id')->index();
            $table->string('rule_version_id')->nullable()->after('rule_id')->index();
            $table->string('rule_name_snapshot')->nullable()->after('rule_version');
        });
    }

    public function down(): void
    {
        Schema::table('application_rule_applications', function (Blueprint $table): void {
            $table->dropColumn(['rule_id', 'rule_version_id', 'rule_name_snapshot']);
        });
    }
};