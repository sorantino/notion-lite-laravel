@extends('layouts.app')

@section('title', 'Recherche')

@section('content')
    <h1>Recherche</h1>
    <p class="muted">{{ count($results) }} résultat(s) pour « {{ $term }} »</p>

    <ul class="list">
        @foreach ($results as $note)
            <li>
                <a href="{{ route('notes.show', $note->id()) }}">📄 {{ $note->title() }}</a>
                <span class="muted">{{ $note->excerpt(80) }}</span>
            </li>
        @endforeach
    </ul>
@endsection
