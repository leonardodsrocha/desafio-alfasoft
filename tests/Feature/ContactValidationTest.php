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
        $admin = User::factory()->create([
            'email'    => 'admin@admin.com',
            'password' => Hash::make('123456'),
        ]);

        return $this->actingAs($admin);
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
    // STORE – creating a contact
    // =========================================================================

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
    public function it_stores_a_valid_contact_and_redirects(): void
    {
        $this->actingAsAdmin()
             ->post(route('contacts.store'), $this->validPayload())
             ->assertRedirect(route('contacts.index'))
             ->assertSessionHas('success');

        $this->assertDatabaseHas('contacts', ['email' => 'maria.silva@example.com']);
    }

    // =========================================================================
    // UPDATE – editing a contact
    // =========================================================================

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
                 'name'    => 'Maria Silva Updated',
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

    // =========================================================================
    // Soft Delete
    // =========================================================================

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

    // =========================================================================
    // Login
    // =========================================================================

    /** @test */
    public function valid_credentials_log_the_user_in_and_redirect(): void
    {
        User::factory()->create([
            'email'    => 'admin@admin.com',
            'password' => Hash::make('123456'),
        ]);

        $this->post(route('login'), [
            'email'    => 'admin@admin.com',
            'password' => '123456',
        ])->assertRedirect(route('contacts.index'));

        $this->assertAuthenticatedAs(User::where('email', 'admin@admin.com')->first());
    }

    /** @test */
    public function invalid_credentials_return_validation_error(): void
    {
        User::factory()->create([
            'email'    => 'admin@admin.com',
            'password' => Hash::make('123456'),
        ]);

        $this->post(route('login'), [
            'email'    => 'admin@admin.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    /** @test */
    public function authenticated_user_is_redirected_away_from_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->get(route('login'))
             ->assertRedirect(route('contacts.index'));
    }

    // =========================================================================
    // Authentication Guards
    // =========================================================================

    /** @test */
    public function guests_cannot_access_create_form(): void
    {
        $this->get(route('contacts.create'))
             ->assertRedirect(route('login'));
    }

    /** @test */
    public function guests_cannot_store_contacts(): void
    {
        $this->post(route('contacts.store'), $this->validPayload())
             ->assertRedirect(route('login'));
    }

    /** @test */
    public function guests_cannot_edit_contacts(): void
    {
        $contact = Contact::factory()->create();

        $this->get(route('contacts.edit', $contact))
             ->assertRedirect(route('login'));
    }

    /** @test */
    public function guests_cannot_delete_contacts(): void
    {
        $contact = Contact::factory()->create();

        $this->delete(route('contacts.destroy', $contact))
             ->assertRedirect(route('login'));
    }

    /** @test */
    public function guests_can_view_the_contacts_index(): void
    {
        $this->get(route('contacts.index'))
             ->assertOk();
    }

    /** @test */
    public function guests_can_view_a_contact_detail_page(): void
    {
        $contact = Contact::factory()->create();

        $this->get(route('contacts.show', $contact))
             ->assertOk();
    }
}
