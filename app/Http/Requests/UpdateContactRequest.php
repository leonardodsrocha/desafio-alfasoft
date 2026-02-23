<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContactRequest extends FormRequest
{
    /**
     * Only authenticated users can update contacts.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Validation rules for updating an existing contact.
     * The `ignore()` call ensures the current contact's own phone/email
     * does not trigger a uniqueness violation.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $contactId = $this->route('contact')->id;

        return [
            'name'    => ['required', 'string', 'min:6'],
            'contact' => ['required', 'digits:9', Rule::unique('contacts', 'contact')->ignore($contactId)],
            'email'   => ['required', 'email:rfc', Rule::unique('contacts', 'email')->ignore($contactId)],
        ];
    }

    /**
     * Human-readable attribute names used in error messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name'    => 'name',
            'contact' => 'phone',
            'email'   => 'email',
        ];
    }

    /**
     * Custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.min'       => 'The name must be at least 6 characters.',
            'contact.digits' => 'The phone must be exactly 9 digits.',
        ];
    }
}
