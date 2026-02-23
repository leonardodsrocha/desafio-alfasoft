<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Base class for Contact form requests.
 * Provides shared attribute labels and error messages.
 */
abstract class ContactRequest extends FormRequest
{
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
