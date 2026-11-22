@extends('layouts.app')

@section('title', $page ? 'Modifier '.$page->title() : 'Nouvelle page')

@section('content')
    @if ($parent)
        <div class="crumb">Sous-page de <a href="{{ route('pages.show', $parent->id()) }}">{{ $parent->title() }}</a></div>
    @endif

    <form class="page" method="post" action="{{ $page ? route('pages.update', $page->id()) : route('pages.store') }}">
        @csrf
        @if ($page)
            @method('put')
        @endif
        @if ($parent)
            <input type="hidden" name="parent_id" value="{{ $parent->id() }}">
        @endif

        <input name="title" placeholder="Sans titre" autofocus
               value="{{ old('title', $page?->title()->value) }}">

        <input name="tags" placeholder="Étiquettes, séparées par des virgules (5 max)"
               value="{{ old('tags', $page?->tags()->toCommaSeparated()) }}">

        <textarea name="content" placeholder="Écrivez en Markdown…">{{ old('content', $page?->content()) }}</textarea>

        <div class="toolbar">
            <button class="btn primary">Enregistrer</button>
            <a class="btn" href="{{ $page ? route('pages.show', $page->id()) : route('pages.index') }}">Annuler</a>
        </div>
    </form>
@endsection
