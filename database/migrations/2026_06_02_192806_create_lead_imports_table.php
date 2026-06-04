<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_imports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('filename');
            $table->string('filepath');
            $table->json('column_mapping')->nullable();
            $table->string('duplicate_strategy')->default('skip');
            $table->integer('total_rows')->default(0);
            $table->integer('processed')->default(0);
            $table->integer('failed')->default(0);
            $table->json('errors')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_imports');
    }
};
