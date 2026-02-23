<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ContactValidationTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function actingAsAdmin(): self
    {
        return $this->actingAs(
            User::factory()->create([
                'email'    => 'admin@admin.com',
                'password' => Hash::make('123456'),
            ])
        );
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name'    => 'Maria Silva',
            'contact' => '912345678',
            'email'   => 'maria.silva@example.com',
        ], $overrides);
    }

    // =========================================================================
    // INDEX – listing and search
    // =========================================================================

    /** @test */
    public function guests_can_view_the_contacts_index(): void
    {
        $this->get(route('contacts.index'))->assertOk();
    }

    /** @test */
    public function index_displays_all_contacts(): void
    {
        $contacts = Contact::factory()->count(3)->create();

        $response = $this->get(route('contacts.index'));

        foreach ($contacts as $contact) {
            $response->assertSee($contact->name);
        }
    }

    /** @test */
    public function index_does_not_display_soft_deleted_contacts(): void
    {
        Contact::factory()->create(['name' => 'Active Person']);
        $deleted = Contact::factory()->create(['name' => 'Deleted Person']);
        $deleted->delete();

        $this->get(route('contacts.index'))
             ->assertSee('Active Person')
             ->assertDontSee('Deleted Person');
    }

    /** @test */
    public function index_search_filters_by_name(): void
    {
        Contact::factory()->create(['name' => 'João Fernandes']);
        Contact::factory()->create(['name' => 'Manuel Pereira']);

        $this->get(route('contacts.index', ['search' => 'João']))
             ->assertSee('João Fernandes')
             ->assertDontSee('Manuel Pereira');
    }

    /** @test */
    public function index_search_filters_by_email(): void
    {
        Contact::factory()->create(['email' => 'joao@example.com', 'name' => 'João Fernandes']);
        Contact::factory()->create(['email' => 'manuel@example.com', 'name' => 'Manuel Pereira']);

        $this->get(route('contacts.index', ['search' => 'joao@example.com']))
             ->assertSee('João Fernandes')
             ->assertDontSee('Manuel Pereira');
    }

    /** @test */
    public function index_search_filters_by_phone(): void
    {
        Contact::factory()->create(['contact' => '911111111', 'name' => 'João Fernandes']);
        Contact::factory()->create(['contact' => '922222222', 'name' => 'Manuel Pereira']);

        $this->get(route('contacts.index', ['search' => '911111111']))
             ->assertSee('João Fernandes')
             ->assertDontSee('Manuel Pereira');
    }

    /** @test */
    public function index_search_returns_no_results_for_unmatched_term(): void
    {
        Contact::factory()->create(['name' => 'Maria Silva']);

        $this->get(route('contacts.index', ['search' => 'xyz-not-found']))
             ->assertDontSee('Maria Silva');
    }

    /** @test */
    public function index_search_does_not_return_soft_deleted_contacts(): void
    {
        $deleted = Contact::factory()->create(['name' => 'Deleted Person']);
        $deleted->delete();

        $this->get(route('contacts.index', ['search' => 'Deleted']))
             ->assertDontSee('Deleted Person');
    }

    // =========================================================================
    // CREATE – form access
    // =========================================================================

    /** @test */
    public function guests_cannot_access_create_form(): void
    {
        $this->get(route('contacts.create'))
             ->assertRedirect(route('login'));
    }

    /** @test */
    public function authenticated_user_can_access_create_form(): void
    {
        $this->actingAsAdmin()
             ->get(route('contacts.create'))
             ->assertOk()
             ->assertViewIs('contacts.create');
    }

    // =========================================================================
    // STORE – creating a contact
    // =========================================================================

    /** @test */
    public function guests_cannot_store_contacts(): void
    {
        $this->post(route('contacts.store'), $this->validPayload())
             ->assertRedirect(route('login'));
    }

    /** @test */
    public function it_requires_name_to_store_a_contact(): void
    {
        $this->actingAsAdmin()
             ->post(route('contacts.store'), $this->validPayload(['name' => '']))
             ->assertSessionHasErrors('name');
    }

    /** @test */
    public function it_rejects_name_shorter_than_6_characters(): void
    {
        $this->actingAsAdmin()
             ->post(route('contacts.store'), $this->validPayload(['name' => 'Ana']))
             ->assertSessionHasErrors('name');
    }

    /** @test */
    public function it_accepts_name_with_6_or_more_characters(): void
    {
        $this->actingAsAdmin()
             ->post(route('contacts.store'), $this->validPayload(['name' => 'Beatriz']))
             ->assertSessionDoesntHaveErrors('name');
    }

    /** @test */
    public function it_requires_contact_to_store_a_contact(): void
    {
        $this->actingAsAdmin()
             ->post(route('contacts.store'), $this->validPayload(['contact' => '']))
             ->assertSessionHasErrors('contact');
    }

    /** @test */
    public function it_rejects_contact_with_fewer_than_9_digits(): void
    {
        $this->actingAsAdmin()
             ->post(route('contacts.store'), $this->validPayload(['contact' => '91234567']))
             ->assertSessionHasErrors('contact');
    }

    /** @test */
    public function it_rejects_contact_with_more_than_9_digits(): void
    {
        $this->actingAsAdmin()
             ->post(route('contacts.store'), $this->validPayload(['contact' => '9123456789']))
             ->assertSessionHasErrors('contact');
    }

    /** @test */
    public function it_rejects_non_numeric_contact(): void
    {
        $this->actingAsAdmin()
             ->post(route('contacts.store'), $this->validPayload(['contact' => 'ABCDEFGHI']))
             ->assertSessionHasErrors('contact');
    }

    /** @test */
    public function it_requires_email_to_store_a_contact(): void
    {
        $this->actingAsAdmin()
             ->post(route('contacts.store'), $this->validPayload(['email' => '']))
             ->assertSessionHasErrors('email');
    }

    /** @test */
    public function it_rejects_invalid_email_format(): void
    {
        $this->actingAsAdmin()
             ->post(route('contacts.store'), $this->validPayload(['email' => 'not-an-email']))
             ->assertSessionHasErrors('email');
    }

    /** @test */
    public function it_rejects_duplicate_contact_phone(): void
    {
        Contact::factory()->create(['contact' => '912345678']);

        $this->actingAsAdmin()
             ->post(route('contacts.store'), $this->validPayload(['contact' => '912345678']))
             ->assertSessionHasErrors('contact');
    }

    /** @test */
    public function it_rejects_duplicate_contact_email(): void
    {
        Contact::factory()->create(['email' => 'maria.silva@example.com']);

        $this->actingAsAdmin()
             ->post(route('contacts.store'), $this->validPayload())
             ->assertSessionHasErrors('email');
    }

    /** @test */
    public function it_rejects_phone_of_a_soft_deleted_contact(): void
    {
        $deleted = Contact::factory()->create(['contact' => '912345678']);
        $deleted->delete();

        $this->actingAsAdmin()
             ->post(route('contacts.store'), $this->validPayload(['contact' => '912345678']))
             ->assertSessionHasErrors('contact');
    }

    /** @test */
    public function it_rejects_email_of_a_soft_deleted_contact(): void
    {
        $deleted = Contact::factory()->create(['email' => 'taken@example.com']);
        $deleted->delete();

        $this->actingAsAdmin()
             ->post(route('contacts.store'), $this->validPayload(['email' => 'taken@example.com']))
             ->assertSessionHasErrors('email');
    }

    /** @test */
    public function it_stores_a_valid_contact_and_redirects(): void
    {
        $this->actingAsAdmin()
             ->post(route('contacts.store'), $this->validPayload())
             ->assertRedirect(route('contacts.index'))
             ->assertSessionHas('success');

        $this->assertDatabaseHas('contacts', [
            'name'    => 'Maria Silva',
            'contact' => '912345678',
            'email'   => 'maria.silva@example.com',
        ]);
    }

    // =========================================================================
    // SHOW – viewing a contact
    // =========================================================================

    /** @test */
    public function guests_can_view_a_contact_detail_page(): void
    {
        $contact = Contact::factory()->create();

        $this->get(route('contacts.show', $contact))->assertOk();
    }

    /** @test */
    public function show_page_displays_contact_data(): void
    {
        $contact = Contact::factory()->create([
            'name'    => 'Ana Rodrigues',
            'contact' => '966123456',
            'email'   => 'ana.rodrigues@example.com',
        ]);

        $this->get(route('contacts.show', $contact))
             ->assertOk()
             ->assertViewIs('contacts.show')
             ->assertSee('Ana Rodrigues')
             ->assertSee('966123456')
             ->assertSee('ana.rodrigues@example.com');
    }

    /** @test */
    public function soft_deleted_contact_returns_404(): void
    {
        $contact = Contact::factory()->create();
        $contact->delete();

        $this->get(route('contacts.show', $contact->id))
             ->assertNotFound();
    }

    // =========================================================================
    // EDIT – form access and data
    // =========================================================================

    /** @test */
    public function guests_cannot_edit_contacts(): void
    {
        $contact = Contact::factory()->create();

        $this->get(route('contacts.edit', $contact))
             ->assertRedirect(route('login'));
    }

    /** @test */
    public function authenticated_user_can_access_edit_form(): void
    {
        $contact = Contact::factory()->create();

        $this->actingAsAdmin()
             ->get(route('contacts.edit', $contact))
             ->assertOk()
             ->assertViewIs('contacts.edit');
    }

    /** @test */
    public function edit_form_is_prepopulated_with_contact_data(): void
    {
        $contact = Contact::factory()->create([
            'name'    => 'Carlos Sousa',
            'contact' => '933987654',
            'email'   => 'carlos.sousa@example.com',
        ]);

        $this->actingAsAdmin()
             ->get(route('contacts.edit', $contact))
             ->assertOk()
             ->assertSee('Carlos Sousa')
             ->assertSee('933987654')
             ->assertSee('carlos.sousa@example.com');
    }

    // =========================================================================
    // UPDATE – editing a contact
    // =========================================================================

    /** @test */
    public function guests_cannot_update_contacts(): void
    {
        $contact = Contact::factory()->create();

        $this->put(route('contacts.update', $contact), $this->validPayload())
             ->assertRedirect(route('login'));
    }

    /** @test */
    public function it_requires_name_to_update_a_contact(): void
    {
        $contact = Contact::factory()->create();

        $this->actingAsAdmin()
             ->put(route('contacts.update', $contact), $this->validPayload(['name' => '']))
             ->assertSessionHasErrors('name');
    }

    /** @test */
    public function it_rejects_name_shorter_than_6_characters_on_update(): void
    {
        $contact = Contact::factory()->create();

        $this->actingAsAdmin()
             ->put(route('contacts.update', $contact), $this->validPayload(['name' => 'Lei']))
             ->assertSessionHasErrors('name');
    }

    /** @test */
    public function it_rejects_contact_with_fewer_than_9_digits_on_update(): void
    {
        $contact = Contact::factory()->create();

        $this->actingAsAdmin()
             ->put(route('contacts.update', $contact), $this->validPayload(['contact' => '91234']))
             ->assertSessionHasErrors('contact');
    }

    /** @test */
    public function it_rejects_invalid_email_on_update(): void
    {
        $contact = Contact::factory()->create();

        $this->actingAsAdmin()
             ->put(route('contacts.update', $contact), $this->validPayload(['email' => 'bad-email']))
             ->assertSessionHasErrors('email');
    }

    /** @test */
    public function it_allows_updating_a_contact_keeping_its_own_phone_and_email(): void
    {
        $contact = Contact::factory()->create([
            'contact' => '912345678',
            'email'   => 'maria.silva@example.com',
        ]);

        $this->actingAsAdmin()
             ->put(route('contacts.update', $contact), [
                 'name'    => 'Maria Silva Editada',
                 'contact' => '912345678',
                 'email'   => 'maria.silva@example.com',
             ])
             ->assertRedirect(route('contacts.show', $contact))
             ->assertSessionDoesntHaveErrors();
    }

    /** @test */
    public function it_rejects_duplicate_phone_from_another_contact_on_update(): void
    {
        Contact::factory()->create(['contact' => '999888777', 'email' => 'other@example.com']);
        $contact = Contact::factory()->create();

        $this->actingAsAdmin()
             ->put(route('contacts.update', $contact), $this->validPayload(['contact' => '999888777']))
             ->assertSessionHasErrors('contact');
    }

    /** @test */
    public function it_rejects_duplicate_email_from_another_contact_on_update(): void
    {
        Contact::factory()->create(['contact' => '999888777', 'email' => 'duplicate@example.com']);
        $contact = Contact::factory()->create();

        $this->actingAsAdmin()
             ->put(route('contacts.update', $contact), $this->validPayload(['email' => 'duplicate@example.com']))
             ->assertSessionHasErrors('email');
    }

    /** @test */
    public function it_updates_contact_data_in_database(): void
    {
        $contact = Contact::factory()->create();

        $this->actingAsAdmin()
             ->put(route('contacts.update', $contact), [
                 'name'    => 'Nome Actualizado',
                 'contact' => '966000111',
                 'email'   => 'updated@example.com',
             ])
             ->assertRedirect(route('contacts.show', $contact))
             ->assertSessionHas('success');

        $this->assertDatabaseHas('contacts', [
            'id'      => $contact->id,
            'name'    => 'Nome Actualizado',
            'contact' => '966000111',
            'email'   => 'updated@example.com',
        ]);
    }

    // =========================================================================
    // DESTROY – deleting a contact
    // =========================================================================

    /** @test */
    public function guests_cannot_delete_contacts(): void
    {
        $contact = Contact::factory()->create();

        $this->delete(route('contacts.destroy', $contact))
             ->assertRedirect(route('login'));
    }

    /** @test */
    public function it_soft_deletes_a_contact(): void
    {
        $contact = Contact::factory()->create();

        $this->actingAsAdmin()
             ->delete(route('contacts.destroy', $contact))
             ->assertRedirect(route('contacts.index'));

        $this->assertSoftDeleted('contacts', ['id' => $contact->id]);
    }

    /** @test */
    public function it_redirects_with_success_message_on_destroy(): void
    {
        $contact = Contact::factory()->create();

        $this->actingAsAdmin()
             ->delete(route('contacts.destroy', $contact))
             ->assertRedirect(route('contacts.index'))
             ->assertSessionHas('success');
    }

    // =========================================================================
    // LOGIN
    // =========================================================================

    /** @test */
    public function login_form_is_accessible_to_guests(): void
    {
        $this->get(route('login'))
             ->assertOk()
             ->assertViewIs('auth.login');
    }

    /** @test */
    public function authenticated_user_is_redirected_away_from_login(): void
    {
        $this->actingAs(User::factory()->create())
             ->get(route('login'))
             ->assertRedirect(route('contacts.index'));
    }

    /** @test */
    public function login_requires_email_field(): void
    {
        $this->post(route('login'), ['email' => '', 'password' => '123456'])
             ->assertSessionHasErrors('email');
    }

    /** @test */
    public function login_requires_password_field(): void
    {
        $this->post(route('login'), ['email' => 'admin@admin.com', 'password' => ''])
             ->assertSessionHasErrors('password');
    }

    /** @test */
    public function login_requires_valid_email_format(): void
    {
        $this->post(route('login'), ['email' => 'not-an-email', 'password' => '123456'])
             ->assertSessionHasErrors('email');
    }

    /** @test */
    public function valid_credentials_log_the_user_in_and_redirect(): void
    {
        User::factory()->create([
            'email'    => 'admin@admin.com',
            'password' => Hash::make('123456'),
        ]);

        $this->post(route('login'), ['email' => 'admin@admin.com', 'password' => '123456'])
             ->assertRedirect(route('contacts.index'));

        $this->assertAuthenticatedAs(User::where('email', 'admin@admin.com')->first());
    }

    /** @test */
    public function invalid_credentials_return_validation_error(): void
    {
        User::factory()->create([
            'email'    => 'admin@admin.com',
            'password' => Hash::make('123456'),
        ]);

        $this->post(route('login'), ['email' => 'admin@admin.com', 'password' => 'wrong-password'])
             ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    // =========================================================================
    // LOGOUT
    // =========================================================================

    /** @test */
    public function authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->post(route('logout'))
             ->assertRedirect(route('contacts.index'))
             ->assertSessionHas('success');

        $this->assertGuest();
    }

    /** @test */
    public function guest_cannot_access_logout(): void
    {
        $this->post(route('logout'))
             ->assertRedirect(route('login'));
    }
}
