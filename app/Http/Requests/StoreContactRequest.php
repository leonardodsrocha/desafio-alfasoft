<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class StoreContactRequest extends ContactRequest
{
    /**
     * Only authenticated users can store contacts.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Validation rules for creating a new contact.
     *
     * Phone and email must be globally unique, including among soft-deleted
     * contacts — this prevents accidental reuse of a deleted contact's data.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'min:6'],
            'contact' => ['required', 'digits:9', Rule::unique('contacts', 'contact')],
            'email'   => ['required', 'email:rfc', Rule::unique('contacts', 'email')],
        ];
    }
}
