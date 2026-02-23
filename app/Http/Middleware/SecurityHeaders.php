<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Injeta cabeçalhos HTTP de segurança em todas as respostas web.
 *
 * Estes cabeçalhos não substituem outras camadas de proteção (CSRF, validação,
 * autenticação), mas adicionam instruções ao navegador que mitigam categorias
 * inteiras de ataques do lado do cliente:
 *
 * - X-Frame-Options: impede que a aplicação seja embutida em iframes de outros
 *   domínios, bloqueando ataques de clickjacking.
 *
 * - X-Content-Type-Options: instrui o navegador a nunca "adivinhar" o tipo MIME
 *   de uma resposta — MIME sniffing pode fazer o browser executar texto como JS.
 *
 * - X-XSS-Protection: ativa o filtro XSS embutido em navegadores legados
 *   (irrelevante nos modernos, mas inócuo mantê-lo).
 *
 * - Referrer-Policy: limita as informações de origem enviadas em cabeçalhos
 *   Referer ao fazer requisições cross-origin, evitando vazar URLs internas.
 *
 * - Permissions-Policy: desativa APIs de browser que a aplicação não utiliza
 *   (câmera, microfone, geolocalização), reduzindo a superfície de ataque.
 */
class SecurityHeaders
{
    /**
     * Adiciona os cabeçalhos de segurança à resposta HTTP.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }
}
