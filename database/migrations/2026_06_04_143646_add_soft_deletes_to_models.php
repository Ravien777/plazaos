<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'leads',
        'clients',
        'projects',
        'meetings',
        'tickets',
        'ticket_replies',
        'documents',
        'notes',
        'emails',
        'email_templates',
        'lead_sources',
        'lead_imports',
        'intake_forms',
        'intake_form_submissions',
        'users',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function ($table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function ($table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
