<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('maroni_client_id')->nullable()->unique()->after('portal_token_expires_at');
            $table->string('maroni_sync_status')->nullable()->after('maroni_client_id');
            $table->timestamp('last_maroni_synced_at')->nullable()->after('maroni_sync_status');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['maroni_client_id', 'maroni_sync_status', 'last_maroni_synced_at']);
        });
    }
};
