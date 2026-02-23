<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class StoreContactRequest extends ContactRequest
{
    /**
     * Somente usuários autenticados podem criar contatos.
     *
     * Esta verificação é redundante com o middleware 'auth' definido na rota,
     * mas garante que a request nunca executa sem autenticação mesmo quando
     * invocada fora do contexto normal de rotas web (ex.: testes de integração
     * ou chamadas diretas ao controller).
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Regras de validação para criar um novo contato.
     *
     * A verificação de unicidade de telefone e e-mail usa whereNull('deleted_at')
     * para ignorar registros excluídos via soft-delete. Isso permite que os dados
     * de um contato excluído sejam reutilizados em um novo cadastro.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'min:6'],
            'contact' => ['required', 'digits:9', Rule::unique('contacts', 'contact')->whereNull('deleted_at')],
            'email'   => ['required', 'email:rfc', Rule::unique('contacts', 'email')->whereNull('deleted_at')],
        ];
    }
}
