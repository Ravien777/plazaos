<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emails', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('emailable');
            $table->string('from');
            $table->string('to');
            $table->string('subject');
            $table->text('body');
            $table->string('template')->nullable();
            $table->json('template_data')->nullable();
            $table->string('status');
            $table->string('external_id')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emails');
    }
};
