<?php

declare(strict_types=1);

namespace App\Enums;

enum IntakeFieldType: string
{
    case Text = 'text';
    case Textarea = 'textarea';
    case Email = 'email';
    case Select = 'select';
    case MultiSelect = 'multi_select';
    case File = 'file';
    case Checkbox = 'checkbox';
    case Date = 'date';

    public function label(): string
    {
        return match ($this) {
            self::Text => 'Text',
            self::Textarea => 'Textarea',
            self::Email => 'Email',
            self::Select => 'Select',
            self::MultiSelect => 'Multi Select',
            self::File => 'File Upload',
            self::Checkbox => 'Checkbox',
            self::Date => 'Date',
        };
    }
}
