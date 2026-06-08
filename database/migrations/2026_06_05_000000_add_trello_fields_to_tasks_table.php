<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('trello_card_id')->nullable()->unique()->after('order');
            $table->string('trello_url')->nullable()->after('trello_card_id');
            $table->dateTime('due_date')->nullable()->after('trello_url');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['trello_card_id', 'trello_url', 'due_date']);
        });
    }
};
