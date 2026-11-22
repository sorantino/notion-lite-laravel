<?php

namespace App\Providers;

use App\Application\NoteService;
use App\Domain\Note\NoteRepository;
use App\Domain\Ports\LlmPort;
use App\Infrastructure\Llm\FakeLlmAdapter;
use App\Infrastructure\Llm\HttpLlmAdapter;
use App\Infrastructure\Llm\OllamaAdapter;
use App\Infrastructure\Persistence\EloquentNoteRepository;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(NoteRepository::class, EloquentNoteRepository::class);

        $this->app->bind(LlmPort::class, fn (): LlmPort => match (config('llm.provider')) {
            'fake' => new FakeLlmAdapter,
            'http' => new HttpLlmAdapter(config('llm.http.base_url'), config('llm.http.api_key'), config('llm.http.model')),
            'ollama' => new OllamaAdapter(config('llm.ollama.host'), config('llm.ollama.model')),
            default => throw new InvalidArgumentException('Fournisseur LLM inconnu : '.config('llm.provider')),
        });
    }

    public function boot(): void
    {
        View::composer('layouts.app', function ($view): void {
            $view->with('tree', $this->app->make(NoteService::class)->tree());
        });
    }
}
