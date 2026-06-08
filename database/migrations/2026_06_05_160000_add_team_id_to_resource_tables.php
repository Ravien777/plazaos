<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'leads',
        'clients',
        'projects',
        'meetings',
        'tickets',
        'tasks',
        'documents',
        'notes',
        'activities',
        'emails',
        'comments',
        'email_templates',
        'lead_sources',
        'lead_imports',
        'intake_forms',
        'testimonials',
        'ticket_replies',
        'intake_form_submissions',
        'webhooks',
        'user_settings',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasColumn($table, 'team_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->foreignUuid('team_id')
                        ->nullable()
                        ->constrained()
                        ->cascadeOnDelete();
                });
            }
        }

        $this->backfillTeamId();
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasColumn($table, 'team_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropForeign(['team_id']);
                    $table->dropColumn('team_id');
                });
            }
        }
    }

    private function backfillTeamId(): void
    {
        if (DB::table('teams')->count() !== 1) {
            return;
        }

        $teamId = DB::table('teams')->value('id');

        foreach ($this->tables as $table) {
            DB::table($table)
                ->whereNull('team_id')
                ->update(['team_id' => $teamId]);
        }
    }
};
