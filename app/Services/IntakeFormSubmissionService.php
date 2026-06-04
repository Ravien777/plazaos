<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Client;
use App\Models\IntakeForm;
use App\Models\IntakeFormSubmission;
use App\Notifications\FormSubmissionReceived;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class IntakeFormSubmissionService
{
    public function submit(IntakeForm $form, Client $client, ?string $clientUserId, array $data): IntakeFormSubmission
    {
        $submission = IntakeFormSubmission::create([
            'intake_form_id' => $form->id,
            'client_id' => $client->id,
            'client_user_id' => $clientUserId,
            'submitted_at' => now(),
        ]);

        $fields = $form->fields()->get()->keyBy('id');

        foreach ($data as $fieldId => $value) {
            $field = $fields->get($fieldId);
            if (!$field) {
                continue;
            }

            $filePath = null;
            $finalValue = $value;

            if ($field->field_type->value === 'file' && $value instanceof \Illuminate\Http\UploadedFile) {
                $filePath = $value->store('intake-forms/' . $form->id, config('filesystems.default'));
                $finalValue = $value->getClientOriginalName();
            } elseif (is_array($value)) {
                $finalValue = json_encode($value);
            }

            $submission->data()->create([
                'intake_form_field_id' => $field->id,
                'value' => is_string($finalValue) ? $finalValue : null,
                'file_path' => $filePath,
            ]);
        }

        activity()->log($submission, 'intake_form.submitted', "Client {$client->company_name} submitted intake form \"{$form->title}\".");

        User::first()->notify(new FormSubmissionReceived($form, $client, $submission));

        return $submission;
    }

    public function getSubmissionsForClient(Client $client, int $perPage = 25)
    {
        return IntakeFormSubmission::where('client_id', $client->id)
            ->with('form:id,title', 'data.field:id,label,field_type')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getSubmissionsForForm(IntakeForm $form, int $perPage = 25)
    {
        return IntakeFormSubmission::where('intake_form_id', $form->id)
            ->with('client:id,company_name', 'data.field:id,label,field_type')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
