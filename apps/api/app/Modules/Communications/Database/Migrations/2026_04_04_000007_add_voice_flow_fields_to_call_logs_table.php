<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('call_logs', function (Blueprint $table): void {
            if (!Schema::hasColumn('call_logs', 'purpose_note')) {
                $table->string('purpose_note', 500)->nullable()->after('to_number');
            }

            if (!Schema::hasColumn('call_logs', 'idempotency_key')) {
                $table->string('idempotency_key')->nullable()->after('purpose_note')->index();
            }

            if (!Schema::hasColumn('call_logs', 'retry_of_call_log_id')) {
                $table->string('retry_of_call_log_id')->nullable()->after('idempotency_key')->index();
            }

            if (!Schema::hasColumn('call_logs', 'bridged_to_number')) {
                $table->string('bridged_to_number')->nullable()->after('retry_of_call_log_id');
            }

            if (!Schema::hasColumn('call_logs', 'answered_at')) {
                $table->timestampTz('answered_at')->nullable()->after('started_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('call_logs', function (Blueprint $table): void {
            foreach (['purpose_note', 'idempotency_key', 'retry_of_call_log_id', 'bridged_to_number', 'answered_at'] as $column) {
                if (Schema::hasColumn('call_logs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
