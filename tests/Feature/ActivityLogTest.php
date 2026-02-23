<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Cobertura do sistema de log de auditoria.
 *
 * Verifica que cada operação CRUD no Contact gera o registro esperado
 * em activity_logs, com os campos corretos (ação, contato, valores),
 * e que a rota de listagem está protegida por autenticação.
 */
class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // Helpers
    // =========================================================================

    private function admin(): User
    {
        return User::factory()->create([
            'email'    => 'admin@admin.com',
            'password' => Hash::make('123456'),
        ]);
    }

    // =========================================================================
    // Observer — criação
    // =========================================================================

    /** @test */
    public function creating_a_contact_logs_a_created_entry(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('contacts.store'), [
            'name'    => 'Maria Silva',
            'contact' => '912345678',
            'email'   => 'maria@example.com',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action'       => 'created',
            'contact_name' => 'Maria Silva',
            'user_id'      => $admin->id,
        ]);
    }

    /** @test */
    public function created_log_stores_new_values_snapshot(): void
    {
        $this->actingAs($this->admin())->post(route('contacts.store'), [
            'name'    => 'João Costa',
            'contact' => '923456789',
            'email'   => 'joao@example.com',
        ]);

        $log = ActivityLog::where('action', 'created')->latest()->first();

        $this->assertNotNull($log->new_values);
        $this->assertEquals('João Costa', $log->new_values['name']);
        $this->assertEquals('923456789', $log->new_values['contact']);
        $this->assertEquals('joao@example.com', $log->new_values['email']);
        $this->assertNull($log->old_values);
    }

    // =========================================================================
    // Observer — atualização
    // =========================================================================

    /** @test */
    public function updating_a_contact_logs_an_updated_entry(): void
    {
        $admin   = $this->admin();
        $contact = Contact::factory()->create(['name' => 'Nome Antigo']);

        $this->actingAs($admin)->put(route('contacts.update', $contact), [
            'name'    => 'Nome Novo',
            'contact' => $contact->contact,
            'email'   => $contact->email,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action'       => 'updated',
            'contact_id'   => $contact->id,
            'user_id'      => $admin->id,
        ]);
    }

    /** @test */
    public function updated_log_stores_old_and_new_values(): void
    {
        $contact = Contact::factory()->create([
            'name'    => 'Nome Antigo',
            'contact' => '911111111',
            'email'   => 'antigo@example.com',
        ]);

        $this->actingAs($this->admin())->put(route('contacts.update', $contact), [
            'name'    => 'Nome Novo',
            'contact' => '922222222',
            'email'   => 'novo@example.com',
        ]);

        $log = ActivityLog::where('action', 'updated')
            ->where('contact_id', $contact->id)
            ->latest()
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals('Nome Antigo', $log->old_values['name']);
        $this->assertEquals('Nome Novo',   $log->new_values['name']);
    }

    /** @test */
    public function updating_contact_without_tracked_changes_does_not_create_log(): void
    {
        $contact = Contact::factory()->create();

        // Dispara um update que não altera nenhum campo rastreado (name/contact/email)
        $contact->touch(); // atualiza só updated_at

        $this->assertDatabaseMissing('activity_logs', [
            'action'     => 'updated',
            'contact_id' => $contact->id,
        ]);
    }

    // =========================================================================
    // Observer — exclusão
    // =========================================================================

    /** @test */
    public function deleting_a_contact_logs_a_deleted_entry(): void
    {
        $admin   = $this->admin();
        $contact = Contact::factory()->create(['name' => 'A Deletar']);

        $this->actingAs($admin)->delete(route('contacts.destroy', $contact));

        $this->assertDatabaseHas('activity_logs', [
            'action'       => 'deleted',
            'contact_id'   => $contact->id,
            'contact_name' => 'A Deletar',
            'user_id'      => $admin->id,
        ]);
    }

    /** @test */
    public function deleted_log_stores_old_values_snapshot(): void
    {
        $contact = Contact::factory()->create([
            'name'    => 'Ana Lima',
            'contact' => '933333333',
            'email'   => 'ana@example.com',
        ]);

        $this->actingAs($this->admin())->delete(route('contacts.destroy', $contact));

        $log = ActivityLog::where('action', 'deleted')
            ->where('contact_id', $contact->id)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals('Ana Lima',        $log->old_values['name']);
        $this->assertEquals('933333333',       $log->old_values['contact']);
        $this->assertEquals('ana@example.com', $log->old_values['email']);
        $this->assertNull($log->new_values);
    }

    // =========================================================================
    // Rota /activity-logs
    // =========================================================================

    /** @test */
    public function guests_cannot_access_activity_log(): void
    {
        $this->get(route('activity-logs.index'))
             ->assertRedirect(route('login'));
    }

    /** @test */
    public function authenticated_user_can_access_activity_log(): void
    {
        $this->actingAs($this->admin())
             ->get(route('activity-logs.index'))
             ->assertOk()
             ->assertViewIs('activity_logs.index');
    }

    /** @test */
    public function activity_log_displays_entries(): void
    {
        $contact = Contact::factory()->create();
        ActivityLog::create([
            'action'       => 'created',
            'contact_id'   => $contact->id,
            'contact_name' => $contact->name,
            'user_id'      => null,
            'user_name'    => null,
            'ip_address'   => '127.0.0.1',
            'old_values'   => null,
            'new_values'   => ['name' => $contact->name],
        ]);

        $this->actingAs($this->admin())
             ->get(route('activity-logs.index'))
             ->assertSee($contact->name)
             ->assertSee('Created');
    }

    /** @test */
    public function activity_log_can_be_filtered_by_action(): void
    {
        $contact = Contact::factory()->create();

        ActivityLog::create([
            'action' => 'created', 'contact_id' => $contact->id,
            'contact_name' => 'Criado', 'ip_address' => '127.0.0.1',
            'new_values' => ['name' => 'Criado'],
        ]);
        ActivityLog::create([
            'action' => 'deleted', 'contact_id' => $contact->id,
            'contact_name' => 'Deletado', 'ip_address' => '127.0.0.1',
            'old_values' => ['name' => 'Deletado'],
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('activity-logs.index', ['action' => 'created']));

        $response->assertSee('Criado')
                 ->assertDontSee('Deletado');
    }
}
