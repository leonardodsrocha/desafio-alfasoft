<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactRequest extends FormRequest
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
            'name.min'      => 'The name must be at least 6 characters.',
            'contact.digits' => 'The phone must be exactly 9 digits.',
        ];
    }
}
