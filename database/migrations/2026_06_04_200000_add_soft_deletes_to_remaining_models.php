<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('team_invitations', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('client_users', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('team_invitations', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('client_users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
