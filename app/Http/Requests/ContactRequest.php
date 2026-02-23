<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Classe base compartilhada pelas requests de criação e edição de contatos.
 *
 * Centraliza os labels dos atributos e as mensagens de erro personalizadas
 * para evitar duplicação entre StoreContactRequest e UpdateContactRequest.
 * As regras de validação ficam nas subclasses porque diferem: a edição
 * precisa excluir o próprio registro das verificações de unicidade.
 */
abstract class ContactRequest extends FormRequest
{
    /**
     * Nomes legíveis dos atributos para as mensagens de erro geradas pelo Laravel.
     *
     * Sem este mapeamento, a mensagem seria "The contact field is required."
     * em vez de "The phone field is required." — o campo chama-se `contact`
     * na base de dados mas o usuário reconhece como telefone.
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
     * Mensagens de erro específicas para regras que precisam de mais contexto.
     *
     * A regra `digits` valida simultaneamente o comprimento e que o valor
     * é numérico, mas a mensagem padrão pode gerar confusão se o usuário
     * digitar letras. A mensagem personalizada torna explícito que o campo
     * aceita apenas dígitos e com comprimento exacto.
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
