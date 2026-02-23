<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * A tentativa de autenticação é pública — qualquer visitante pode submeter o form.
     *
     * A proteção contra força bruta está na rota, via throttle:10,1 middleware,
     * e não aqui. Esta request apenas valida formato antes de tentar o Auth::attempt.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validação estrutural mínima dos campos de autenticação.
     *
     * Verifica que os campos chegaram preenchidos e com formato válido de email;
     * a comparação das credenciais contra a base de dados acontece depois,
     * em LoginController::login() através de Auth::attempt().
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }
}
