<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\IntakeForm;
use App\Models\IntakeFormField;
use Illuminate\Pagination\LengthAwarePaginator;

class IntakeFormService
{
    public function list(): LengthAwarePaginator
    {
        return IntakeForm::withCount('submissions')
            ->orderBy('created_at', 'desc')
            ->paginate(25);
    }

    public function create(array $data): IntakeForm
    {
        $fields = $data['fields'] ?? [];
        unset($data['fields']);

        $form = IntakeForm::create($data);

        foreach ($fields as $i => $field) {
            $field['sort_order'] = $field['sort_order'] ?? $i;
            $form->fields()->create($field);
        }

        activity()->log($form, 'intake_form.created', "Intake form \"{$form->title}\" was created.");

        return $form;
    }

    public function update(IntakeForm $form, array $data): IntakeForm
    {
        $fields = $data['fields'] ?? [];
        unset($data['fields']);

        $form->update($data);

        $keptIds = [];
        foreach ($fields as $i => $field) {
            $field['sort_order'] = $field['sort_order'] ?? $i;
            if (!empty($field['id'])) {
                $existing = $form->fields()->find($field['id']);
                if ($existing) {
                    $existing->update($field);
                    $keptIds[] = $existing->id;
                    continue;
                }
            }
            unset($field['id']);
            $new = $form->fields()->create($field);
            $keptIds[] = $new->id;
        }

        $form->fields()->whereNotIn('id', $keptIds)->delete();

        activity()->log($form, 'intake_form.updated', "Intake form \"{$form->title}\" was updated.");

        return $form;
    }

    public function delete(IntakeForm $form): void
    {
        activity()->log($form, 'intake_form.deleted', "Intake form \"{$form->title}\" was deleted.");

        $form->delete();
    }
}
