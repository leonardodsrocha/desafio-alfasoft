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
     * O ->ignore($contactId) nas regras de unicidade é o detalhe crítico desta
     * classe: sem ele, submeter o formulário sem alterar o telefone ou o e-mail
     * retornaria sempre "already taken", porque o próprio registro está na tabela.
     * O ignore não cria exceção para outros contatos — a unicidade global
     * permanece e continua abrangendo registros em soft-delete.
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
