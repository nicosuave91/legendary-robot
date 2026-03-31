<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->index(['tenant_id', 'created_at'], 'audit_logs_tenant_created_idx');
            $table->index(['tenant_id', 'action', 'created_at'], 'audit_logs_tenant_action_created_idx');
            $table->index(['tenant_id', 'subject_type', 'subject_id', 'created_at'], 'audit_logs_tenant_subject_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->dropIndex('audit_logs_tenant_created_idx');
            $table->dropIndex('audit_logs_tenant_action_created_idx');
            $table->dropIndex('audit_logs_tenant_subject_created_idx');
        });
    }
};
