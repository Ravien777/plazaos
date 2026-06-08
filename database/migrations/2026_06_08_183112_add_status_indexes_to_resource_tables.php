<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->index('status');
        });
        Schema::table('clients', function (Blueprint $table) {
            $table->index('status');
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->index('status');
        });
        Schema::table('meetings', function (Blueprint $table) {
            $table->index('status');
        });
        Schema::table('tickets', function (Blueprint $table) {
            $table->index('status');
            $table->index('priority');
            $table->index('category');
        });
        Schema::table('emails', function (Blueprint $table) {
            $table->index('status');
        });
        Schema::table('lead_sources', function (Blueprint $table) {
            $table->index('type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['priority']);
            $table->dropIndex(['category']);
        });
        Schema::table('emails', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
        Schema::table('lead_sources', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropIndex(['is_active']);
        });
    }
};
