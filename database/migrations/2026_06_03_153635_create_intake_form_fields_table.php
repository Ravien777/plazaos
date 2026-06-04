<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intake_form_fields', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intake_form_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('field_type');
            $table->boolean('required')->default(false);
            $table->json('options')->nullable();
            $table->string('placeholder')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intake_form_fields');
    }
};
