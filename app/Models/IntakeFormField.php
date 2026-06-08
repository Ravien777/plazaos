<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\IntakeFieldType;
use App\Models\Traits\BelongsToTeam;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntakeFormField extends Model
{
    use BelongsToTeam, HasFactory, HasUuids;

    protected $fillable = [
        'team_id',
        'intake_form_id',
        'label',
        'field_type',
        'required',
        'options',
        'placeholder',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'field_type' => IntakeFieldType::class,
            'required' => 'boolean',
            'options' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(IntakeForm::class, 'intake_form_id');
    }
}
