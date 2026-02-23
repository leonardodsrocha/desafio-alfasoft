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
     * A verificação de unicidade de telefone e e-mail não inclui a cláusula
     * whereNull('deleted_at'), por isso abrange também registros em soft-delete.
     * Essa decisão é intencional — impede a reutilização de dados de contatos
     * que foram excluídos, evitando colisões silenciosas na agenda.
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
