<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthRoutesTest extends TestCase
{
    public function test_guests_are_redirected_to_login(): void
    {
        foreach (['/map', '/users', '/distritos', '/municipios', '/freguesias', '/codigos-postais', '/places'] as $uri) {
            $this->get($uri)->assertRedirect(route('login'));
        }
    }

    public function test_login_page_renders_without_prefilled_credentials(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('name="username"', false)
            ->assertDontSee('value="ola"', false);
    }

    public function test_login_requires_username_and_password(): void
    {
        $this->post('/login', [])->assertSessionHasErrors(['username', 'password']);
    }

    public function test_logout_is_not_available_via_get(): void
    {
        $this->get('/logout')->assertNotFound();
    }

    public function test_language_switch_only_accepts_supported_languages(): void
    {
        $this->get('/change-language/xx')->assertNotFound();
        $this->get('/change-language/en')->assertRedirect();
    }
}
