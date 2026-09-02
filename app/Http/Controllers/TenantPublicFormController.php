<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class TenantPublicFormController extends Controller
{
    /**
     * ESUBIZ_CORE_PUBLIC_FORM_RUNTIME_V1
     *
     * Generic Core form submission handler.
     *
     * Authentication system forms are deliberately excluded because
     * Registration, Login and Password Reset must continue to use their
     * dedicated authentication handlers.
     */
    public function submit(
        Request $request,
        int $form
    ) {
        try {
            $db = DB::connection('tenant');
            $schema = $db->getSchemaBuilder();

            abort_unless(
                $schema->hasTable('forms')
                && $schema->hasTable('form_fields')
                && $schema->hasTable('form_submissions'),
                404
            );
        } catch (Throwable $e) {
            abort(404);
        }

        $formRecord = $db
            ->table('forms')
            ->where('id', $form)
            ->where('is_active', true)
            ->first();

        abort_unless($formRecord, 404);

        $settings = json_decode(
            (string) ($formRecord->settings ?? ''),
            true
        );

        $settings = is_array($settings)
            ? $settings
            : [];

        $purpose = trim(
            (string) ($settings['purpose'] ?? '')
        );

        $coreDefault = trim(
            (string) (
                $settings['core_default_form']
                ?? ''
            )
        );

        /*
         * Authentication forms never enter generic submissions.
         */
        abort_if(
            str_starts_with(
                $purpose,
                'authentication.'
            )
            || in_array(
                $coreDefault,
                [
                    'registration',
                    'login',
                    'password-reset',
                ],
                true
            ),
            422,
            'This form is handled by the authentication system.'
        );

        $fields = $db
            ->table('form_fields')
            ->where('form_id', $formRecord->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        abort_if(
            $fields->isEmpty(),
            422,
            'This form has no fields.'
        );

        $rules = [
            'fields' => [
                'nullable',
                'array',
            ],
        ];

        $passwordFieldNames = [];

        foreach ($fields as $field) {
            $name = trim(
                (string) $field->name
            );

            if ($name === '') {
                continue;
            }

            $type = strtolower(
                trim(
                    (string) $field->type
                )
            );

            $options = json_decode(
                (string) ($field->options ?? ''),
                true
            );

            $options = is_array($options)
                ? array_values(
                    array_filter(
                        array_map(
                            static fn ($value) =>
                                trim((string) $value),
                            $options
                        ),
                        static fn ($value) =>
                            $value !== ''
                    )
                )
                : [];

            $key = 'fields.' . $name;

            switch ($type) {
                case 'email':
                    $rules[$key] = [
                        $field->required
                            ? 'required'
                            : 'nullable',
                        'email',
                        'max:320',
                    ];
                    break;

                case 'number':
                    $rules[$key] = [
                        $field->required
                            ? 'required'
                            : 'nullable',
                        'numeric',
                    ];
                    break;

                case 'date':
                    $rules[$key] = [
                        $field->required
                            ? 'required'
                            : 'nullable',
                        'date',
                    ];
                    break;

                case 'select':
                case 'radio':
                    $fieldRules = [
                        $field->required
                            ? 'required'
                            : 'nullable',
                        'string',
                        'max:5000',
                    ];

                    if ($options) {
                        $fieldRules[] =
                            Rule::in($options);
                    }

                    $rules[$key] =
                        $fieldRules;
                    break;

                case 'checkbox':
                    if ($options) {
                        $rules[$key] = [
                            $field->required
                                ? 'required'
                                : 'nullable',
                            'array',
                        ];

                        if ($field->required) {
                            $rules[$key][] =
                                'min:1';
                        }

                        $rules[$key . '.*'] = [
                            'string',
                            Rule::in($options),
                        ];
                    } else {
                        $rules[$key] = [
                            $field->required
                                ? 'required'
                                : 'nullable',
                            Rule::in(['1']),
                        ];
                    }
                    break;

                case 'textarea':
                    $rules[$key] = [
                        $field->required
                            ? 'required'
                            : 'nullable',
                        'string',
                        'max:20000',
                    ];
                    break;

                case 'password':
                    /*
                     * Password inputs may exist because the same Core
                     * builder supplies Authentication forms.
                     *
                     * Generic submissions must never persist passwords.
                     */
                    $rules[$key] = [
                        $field->required
                            ? 'required'
                            : 'nullable',
                        'string',
                        'max:5000',
                    ];

                    $passwordFieldNames[] =
                        $name;
                    break;

                case 'tel':
                case 'text':
                default:
                    $rules[$key] = [
                        $field->required
                            ? 'required'
                            : 'nullable',
                        'string',
                        'max:5000',
                    ];
                    break;
            }
        }

        $validated = Validator::make(
            $request->all(),
            $rules
        )->validate();

        $submitted = $validated['fields']
            ?? [];

        /*
         * Never save generic password values.
         */
        foreach (
            $passwordFieldNames
            as $passwordFieldName
        ) {
            unset(
                $submitted[
                    $passwordFieldName
                ]
            );
        }

        /*
         * Persist only fields actually defined by this form.
         */
        $allowedNames = $fields
            ->pluck('name')
            ->map(
                static fn ($name) =>
                    (string) $name
            )
            ->all();

        $submitted = array_intersect_key(
            $submitted,
            array_flip($allowedNames)
        );

        $db
            ->table('form_submissions')
            ->insert([
                'form_id' =>
                    $formRecord->id,

                'data' =>
                    json_encode(
                        $submitted,
                        JSON_UNESCAPED_SLASHES
                        | JSON_UNESCAPED_UNICODE
                    ),

                'status' =>
                    'new',

                'created_at' =>
                    now(),

                'updated_at' =>
                    now(),
            ]);

        return back()->with(
            'core_form_success_' . $formRecord->id,
            'Form submitted successfully.'
        );
    }
}
