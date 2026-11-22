<?php

namespace Tests\Feature;

use App\Services\PageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_home_page_invites_to_create_a_first_page(): void
    {
        $this->get(route('pages.index'))->assertOk()->assertSee('Créer une première page');
    }

    public function test_the_home_page_redirects_to_the_first_page(): void
    {
        $page = $this->app->make(PageService::class)->create('Accueil');

        $this->get(route('pages.index'))->assertRedirect(route('pages.show', $page->id()));
    }

    public function test_a_page_can_be_created_and_displayed(): void
    {
        $response = $this->post(route('pages.store'), ['title' => 'Ma page', 'content' => '**Bonjour**', 'tags' => 'notes']);

        $response->assertRedirect();
        $this->followRedirects($response)
            ->assertSee('Ma page')
            ->assertSee('<strong>Bonjour</strong>', false)
            ->assertSee('#notes');
    }

    public function test_a_business_rule_violation_is_shown_to_the_user(): void
    {
        $this->from(route('pages.create'))
            ->post(route('pages.store'), ['title' => str_repeat('a', 121)])
            ->assertRedirect(route('pages.create'))
            ->assertSessionHasErrors(['domain' => 'Le titre ne peut pas dépasser 120 caractères.']);
    }

    public function test_html_in_content_is_escaped(): void
    {
        $page = $this->app->make(PageService::class)->create('XSS', '<script>alert(1)</script>');

        $this->get(route('pages.show', $page->id()))->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_a_page_can_go_through_the_trash(): void
    {
        $page = $this->app->make(PageService::class)->create('Temporaire');

        $this->post(route('pages.archive', $page->id()))->assertRedirect(route('pages.index'));
        $this->get(route('pages.trash'))->assertSee('Temporaire');
        $this->delete(route('pages.destroy', $page->id()))->assertRedirect(route('pages.trash'));
        $this->get(route('pages.show', $page->id()))->assertNotFound();
    }

    public function test_search_lists_matching_pages(): void
    {
        $this->app->make(PageService::class)->create('Réunion budget');

        $this->get(route('pages.search', ['q' => 'budget']))->assertOk()->assertSee('Réunion budget');
    }
}
