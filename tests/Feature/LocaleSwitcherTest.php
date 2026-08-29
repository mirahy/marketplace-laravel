<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleSwitcherTest extends TestCase
{
    use RefreshDatabase;

    public function test_switching_to_english_updates_the_session_and_the_active_locale(): void
    {
        $this->get('/idioma/en')->assertRedirect();

        $this->assertSame('en', session('locale'));

        $this->get('/');

        $this->assertSame('en', app()->getLocale());
    }

    public function test_switching_to_spanish_updates_the_session_and_the_active_locale(): void
    {
        $this->get('/idioma/es')->assertRedirect();

        $this->assertSame('es', session('locale'));

        $this->get('/');

        $this->assertSame('es', app()->getLocale());
    }

    public function test_an_invalid_locale_is_ignored_and_does_not_break_the_request(): void
    {
        $this->withSession(['locale' => 'en'])->get('/idioma/xx')->assertRedirect();

        $this->assertSame('en', session('locale'));
    }

    public function test_default_locale_is_portuguese_when_nothing_was_chosen(): void
    {
        $this->get('/');

        $this->assertSame('pt_BR', app()->getLocale());
    }
}
