@extends('layouts.app')

@section('title', $note ? 'Modifier '.$note->title() : 'Nouvelle note')

@section('content')
    @if ($parent)
        <div class="crumb">Sous-note de <a href="{{ route('notes.show', $parent->id()) }}">{{ $parent->title() }}</a></div>
    @endif

    <form class="note" method="post" action="{{ $note ? route('notes.update', $note->id()) : route('notes.store') }}">
        @csrf
        @if ($note)
            @method('put')
        @endif
        @if ($parent)
            <input type="hidden" name="parent_id" value="{{ $parent->id() }}">
        @endif

        <input name="title" placeholder="Sans titre" autofocus
               value="{{ old('title', $note?->title()->value) }}">

        <input name="tags" placeholder="Étiquettes, séparées par des virgules (5 max)"
               value="{{ old('tags', $note?->tags()->toCommaSeparated()) }}">

        <textarea name="content" placeholder="Écrivez en Markdown…">{{ old('content', $note?->content()) }}</textarea>

        <div class="toolbar">
            <button class="btn primary">Enregistrer</button>
            <a class="btn" href="{{ $note ? route('notes.show', $note->id()) : route('notes.index') }}">Annuler</a>
        </div>
    </form>
@endsection
