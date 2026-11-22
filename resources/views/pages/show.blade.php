@extends('layouts.app')

@section('title', $page->title())

@section('content')
    @if ($parent)
        <div class="crumb"><a href="{{ route('pages.show', $parent->id()) }}">{{ $parent->title() }}</a> /</div>
    @endif

    <h1>{{ $page->title() }}</h1>

    <div class="tags">
        @foreach ($page->tags()->values as $tag)
            <span>#{{ $tag }}</span>
        @endforeach
    </div>

    <div class="toolbar">
        <a class="btn primary" href="{{ route('pages.edit', $page->id()) }}">Modifier</a>
        @if ($page->canHaveChildren())
            <a class="btn" href="{{ route('pages.create', ['parent' => $page->id()]) }}">＋ Sous-page</a>
        @endif
        <form method="post" action="{{ route('pages.archive', $page->id()) }}">
            @csrf
            <button class="btn danger">Mettre à la corbeille</button>
        </form>
        {{-- Les futurs boutons IA (Résumer, Suggérer des étiquettes…) viendront ici. --}}
    </div>

    <div class="content">
        {!! Str::markdown($page->content(), ['html_input' => 'escape', 'allow_unsafe_links' => false]) !!}
    </div>

    @if ($children)
        <h3 class="muted">Sous-pages</h3>
        <ul class="list">
            @foreach ($children as $child)
                <li>
                    <a href="{{ route('pages.show', $child->id()) }}">📄 {{ $child->title() }}</a>
                    <span class="muted">{{ $child->excerpt(60) }}</span>
                </li>
            @endforeach
        </ul>
    @endif

    <p class="muted">Modifiée le {{ $page->updatedAt()->format('d/m/Y à H:i') }}</p>
@endsection
