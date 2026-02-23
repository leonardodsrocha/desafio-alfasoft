<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateContactRequest extends ContactRequest
{
    /**
     * Somente usuários autenticados podem editar contatos.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Regras de validação para editar um contato existente.
     *
     * O ->ignore($contactId) exclui o próprio registro da verificação de unicidade,
     * evitando falso erro ao submeter o formulário sem alterar telefone ou e-mail.
     * O ->whereNull('deleted_at') restringe a verificação apenas a registros ativos,
     * permitindo que dados de contatos excluídos sejam reutilizados.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $contactId = $this->route('contact')->id;

        return [
            'name'    => ['required', 'string', 'min:6'],
            'contact' => ['required', 'digits:9', Rule::unique('contacts', 'contact')->ignore($contactId)->whereNull('deleted_at')],
            'email'   => ['required', 'email:rfc', Rule::unique('contacts', 'email')->ignore($contactId)->whereNull('deleted_at')],
        ];
    }
}
