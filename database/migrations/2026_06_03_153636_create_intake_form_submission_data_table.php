<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intake_form_submission_data', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intake_form_submission_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('intake_form_field_id')->constrained()->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intake_form_submission_data');
    }
};
