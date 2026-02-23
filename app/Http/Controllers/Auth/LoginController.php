<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Mostra o formulário de autenticação.
     *
     * A rota está protegida com o middleware 'guest': um usuário que já
     * tem sessão iniciada é redirecionado para a lista de contatos sem
     * chegar a ver o formulário.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Processa as credenciais e inicia a sessão autenticada.
     *
     * Auth::attempt() verifica a password contra o hash bcrypt guardado
     * na base de dados. A regeneração de sessão após login bem-sucedido
     * é obrigatória para prevenir ataques de fixação de sessão (session fixation).
     *
     * O erro de credenciais inválidas é propositadamente anexado ao campo
     * 'email' e não a 'password' — desta forma não se revela qual dos dois
     * campos está errado, dificultando a enumeração de usuários.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('contacts.index'))
                ->with('success', 'Welcome back!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Encerra a sessão e invalida o token CSRF por completo.
     *
     * A sessão é invalidada (e não apenas limpa) e o token é regenerado —
     * qualquer pedido já em trânsito com o token antigo, por exemplo num
     * tab aberto, será recusado. Ambos os passos são necessários para
     * respeitar as boas práticas de logout seguro.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('contacts.index')
            ->with('success', 'You have been logged out.');
    }
}
