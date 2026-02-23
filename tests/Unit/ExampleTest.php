<?php

namespace Tests\Unit;

use App\Models\Contact;
use Illuminate\Database\Eloquent\SoftDeletes;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Verifica que $fillable cobre exatamente os três campos que o formulário
     * preenche por mass-assignment — nem mais, nem menos.
     *
     * Um campo faltando levaria a dados silenciosamente descartados no create();
     * um campo a mais (como `id` ou `deleted_at`) abriria superfície de ataque
     * caso o request fosse manipulado.
     */
    public function test_contact_fillable_covers_exactly_the_three_form_fields(): void
    {
        $this->assertSame(
            ['name', 'contact', 'email'],
            (new Contact())->getFillable()
        );
    }

    /**
     * Confirma que o trait SoftDeletes está ativo no modelo Contact.
     *
     * Sem SoftDeletes a unicidade de telefone e e-mail quebraria para registros
     * excluídos — um contato removido poderia ser recriado com o mesmo número
     * imediatamente, porque o registro já teria saído da tabela.
     */
    public function test_contact_uses_soft_deletes_trait(): void
    {
        $this->assertContains(
            SoftDeletes::class,
            class_uses_recursive(Contact::class)
        );
    }

    /**
     * Garante que nenhum dos campos visíveis da agenda está oculto na
     * serialização do modelo.
     *
     * $hidden é usado normalmente para passwords e tokens; esconder name,
     * contact ou email bloquearia completamente a listagem via API ou
     * chamadas a toArray() / toJson() sem aviso explícito.
     */
    public function test_contact_does_not_hide_public_fields(): void
    {
        $hidden = (new Contact())->getHidden();

        $this->assertNotContains('name', $hidden);
        $this->assertNotContains('contact', $hidden);
        $this->assertNotContains('email', $hidden);
    }
}
