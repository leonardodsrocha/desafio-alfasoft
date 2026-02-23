<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Cria ou atualiza o usuário administrador da aplicação.
     *
     * updateOrCreate() é preferível a create() porque torna o seeder idempotente:
     * rodá-lo várias vezes em ambiente de desenvolvimento (ex.: após migrations
     * em fresh) não duplica o registro nem lança uma exceção de chave duplicada.
     * A senha '123456' destina-se exclusivamente ao ambiente de desenvolvimento.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name'     => 'Admin',
                'email'    => 'admin@admin.com',
                'password' => Hash::make('123456'),
            ]
        );
    }
}
