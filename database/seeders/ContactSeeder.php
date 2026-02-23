<?php

namespace Database\Seeders;

use App\Models\Contact;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Carrega uma amostra de contatos de demonstração para ambiente local.
     *
     * firstOrCreate() verifica o e-mail antes de inserir, tornando o seeder
     * idempotente: rodá-lo múltiplas vezes não duplica os registros. O e-mail
     * é usado como chave de existência por ser único e imutável na regra de
     * negócio, ao contrário do nome que pode ser alterado.
     */
    public function run(): void
    {
        $contacts = [
            [
                'name'    => 'Maria Silva',
                'contact' => '912345678',
                'email'   => 'maria.silva@example.com',
            ],
            [
                'name'    => 'João Santos',
                'contact' => '923456789',
                'email'   => 'joao.santos@example.com',
            ],
            [
                'name'    => 'Ana Ferreira',
                'contact' => '934567890',
                'email'   => 'ana.ferreira@example.com',
            ],
            [
                'name'    => 'Carlos Oliveira',
                'contact' => '945678901',
                'email'   => 'carlos.oliveira@example.com',
            ],
            [
                'name'    => 'Sofia Costa',
                'contact' => '956789012',
                'email'   => 'sofia.costa@example.com',
            ],
        ];

        foreach ($contacts as $contact) {
            Contact::firstOrCreate(['email' => $contact['email']], $contact);
        }
    }
}
