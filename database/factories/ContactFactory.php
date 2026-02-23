<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Estado padrão de um contato gerado para testes.
     *
     * O telefone usa numerify('#########') para garantir exatamente 9 dígitos
     * numéricos, alinhado com a validação `digits:9` das requests.
     * unique() é aplicado a ambos os campos para evitar colisões quando
     * vários contatos são criados na mesma suite de testes.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'    => fake()->name(),
            'contact' => fake()->unique()->numerify('#########'),
            'email'   => fake()->unique()->safeEmail(),
        ];
    }
}
