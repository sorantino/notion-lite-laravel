@extends('layouts.app')

@section('title', $note->title())

@section('content')
    @if ($parent)
        <div class="crumb"><a href="{{ route('notes.show', $parent->id()) }}">{{ $parent->title() }}</a> /</div>
    @endif

    <h1>{{ $note->title() }}</h1>

    <div class="tags">
        @foreach ($note->tags()->values as $tag)
            <span>#{{ $tag }}</span>
        @endforeach
    </div>

    <div class="toolbar">
        <a class="btn primary" href="{{ route('notes.edit', $note->id()) }}">Modifier</a>
        @if ($note->canHaveChildren())
            <a class="btn" href="{{ route('notes.create', ['parent' => $note->id()]) }}">＋ Sous-note</a>
        @endif
        <form method="post" action="{{ route('notes.archive', $note->id()) }}">
            @csrf
            <button class="btn danger">Mettre à la corbeille</button>
        </form>
        <form method="post" action="{{ route('notes.summarize', $note->id()) }}">
            @csrf
            <button class="btn">✨ Résumer</button>
        </form>
    </div>

    @if ($note->summary())
        <div class="summary">
            <strong>Résumé</strong>
            <p>{{ $note->summary() }}</p>
        </div>
    @endif

    <div class="content">
        {!! Str::markdown($note->content(), ['html_input' => 'escape', 'allow_unsafe_links' => false]) !!}
    </div>

    @if ($children)
        <h3 class="muted">Sous-notes</h3>
        <ul class="list">
            @foreach ($children as $child)
                <li>
                    <a href="{{ route('notes.show', $child->id()) }}">📄 {{ $child->title() }}</a>
                    <span class="muted">{{ $child->excerpt(60) }}</span>
                </li>
            @endforeach
        </ul>
    @endif

    <p class="muted">Modifiée le {{ $note->updatedAt()->format('d/m/Y à H:i') }}</p>
@endsection
