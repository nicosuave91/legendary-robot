<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('communication_attachments', function (Blueprint $table): void {
            $table->timestampTz('scan_requested_at')->nullable()->after('scan_status')->index();
            $table->timestampTz('scanned_at')->nullable()->after('scan_requested_at')->index();
            $table->string('scan_engine')->nullable()->after('scanned_at');
            $table->text('scan_result_detail')->nullable()->after('scan_engine');
            $table->string('quarantine_reason')->nullable()->after('scan_result_detail');
            $table->string('scan_updated_by')->nullable()->after('quarantine_reason')->index();
        });
    }

    public function down(): void
    {
        Schema::table('communication_attachments', function (Blueprint $table): void {
            $table->dropColumn(['scan_requested_at','scanned_at','scan_engine','scan_result_detail','quarantine_reason','scan_updated_by']);
        });
    }
};
