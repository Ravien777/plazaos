<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntakeFormSubmissionData extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'intake_form_submission_id',
        'intake_form_field_id',
        'value',
        'file_path',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(IntakeFormSubmission::class, 'intake_form_submission_id');
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(IntakeFormField::class, 'intake_form_field_id');
    }
}
