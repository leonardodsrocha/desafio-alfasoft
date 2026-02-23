<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The root URL redirects to the contacts list.
     */
    public function test_root_redirects_to_contacts(): void
    {
        $this->get('/')
             ->assertRedirect('/contacts');
    }

    /**
     * The contacts index page returns a successful response.
     */
    public function test_contacts_index_returns_ok(): void
    {
        $this->get('/contacts')
             ->assertOk();
    }
}
