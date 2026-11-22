<?php

namespace Tests\Feature;

use App\Application\NoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_home_page_invites_to_create_a_first_note(): void
    {
        $this->get(route('notes.index'))->assertOk()->assertSee('Créer une première note');
    }

    public function test_the_home_page_redirects_to_the_first_note(): void
    {
        $note = $this->app->make(NoteService::class)->create('Accueil');

        $this->get(route('notes.index'))->assertRedirect(route('notes.show', $note->id()));
    }

    public function test_a_note_can_be_created_and_displayed(): void
    {
        $response = $this->post(route('notes.store'), ['title' => 'Ma note', 'content' => '**Bonjour**', 'tags' => 'notes']);

        $response->assertRedirect();
        $this->followRedirects($response)
            ->assertSee('Ma note')
            ->assertSee('<strong>Bonjour</strong>', false)
            ->assertSee('#notes');
    }

    public function test_a_business_rule_violation_is_shown_to_the_user(): void
    {
        $this->from(route('notes.create'))
            ->post(route('notes.store'), ['title' => str_repeat('a', 121)])
            ->assertRedirect(route('notes.create'))
            ->assertSessionHasErrors(['domain' => 'Le titre ne peut pas dépasser 120 caractères.']);
    }

    public function test_html_in_content_is_escaped(): void
    {
        $note = $this->app->make(NoteService::class)->create('XSS', '<script>alert(1)</script>');

        $this->get(route('notes.show', $note->id()))->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_a_note_can_go_through_the_trash(): void
    {
        $note = $this->app->make(NoteService::class)->create('Temporaire');

        $this->post(route('notes.archive', $note->id()))->assertRedirect(route('notes.index'));
        $this->get(route('notes.trash'))->assertSee('Temporaire');
        $this->delete(route('notes.destroy', $note->id()))->assertRedirect(route('notes.trash'));
        $this->get(route('notes.show', $note->id()))->assertNotFound();
    }

    public function test_search_lists_matching_notes(): void
    {
        $this->app->make(NoteService::class)->create('Réunion budget');

        $this->get(route('notes.search', ['q' => 'budget']))->assertOk()->assertSee('Réunion budget');
    }
}
