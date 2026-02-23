<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateContactRequest extends ContactRequest
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
     *
     * `->ignore($contactId)` ensures the contact's own current phone/email
     * does not trigger a false uniqueness violation on itself.
     * Both phone and email remain globally unique, including soft-deleted rows.
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
}
