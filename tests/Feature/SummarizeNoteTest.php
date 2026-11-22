<?php

namespace Tests\Feature;

use App\Application\NoteService;
use App\Application\SummarizeNote;
use App\Domain\Note\NoteNotFound;
use App\Domain\Ports\LlmPort;
use App\Infrastructure\Llm\FakeLlmAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SummarizeNoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_summarizes_a_note_and_saves_the_summary(): void
    {
        $note = $this->app->make(NoteService::class)->create('Réunion', 'Budget validé, prochaine réunion lundi.');

        $summary = $this->app->make(SummarizeNote::class)->handle($note->id());

        $this->assertSame(FakeLlmAdapter::RESPONSE, $summary);
        $this->assertSame(FakeLlmAdapter::RESPONSE, $this->app->make(NoteService::class)->get($note->id())->summary());
    }

    public function test_it_sends_the_note_title_and_content_to_the_llm(): void
    {
        $llm = new class implements LlmPort
        {
            /** @var list<array{role: string, content: string}> */
            public array $messages = [];

            public function complete(array $messages): string
            {
                $this->messages = $messages;

                return '  Un résumé.  ';
            }
        };
        $this->app->instance(LlmPort::class, $llm);
        $note = $this->app->make(NoteService::class)->create('Réunion', 'Budget validé.');

        $summary = $this->app->make(SummarizeNote::class)->handle($note->id());

        $this->assertSame('Un résumé.', $summary);
        $this->assertSame('system', $llm->messages[0]['role']);
        $this->assertSame("# Réunion\n\nBudget validé.", $llm->messages[1]['content']);
    }

    public function test_it_fails_for_an_unknown_note(): void
    {
        $this->expectException(NoteNotFound::class);

        $this->app->make(SummarizeNote::class)->handle(999);
    }

    public function test_the_summarize_button_shows_the_summary(): void
    {
        $note = $this->app->make(NoteService::class)->create('Réunion', 'Budget validé.');

        $this->followingRedirects()
            ->post(route('notes.summarize', $note->id()))
            ->assertOk()
            ->assertSee('Résumé généré.')
            ->assertSee(FakeLlmAdapter::RESPONSE);
    }
}
