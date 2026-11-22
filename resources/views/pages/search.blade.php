@extends('layouts.app')

@section('title', 'Recherche')

@section('content')
    <h1>Recherche</h1>
    <p class="muted">{{ count($results) }} résultat(s) pour « {{ $term }} »</p>

    <ul class="list">
        @foreach ($results as $page)
            <li>
                <a href="{{ route('pages.show', $page->id()) }}">📄 {{ $page->title() }}</a>
                <span class="muted">{{ $page->excerpt(80) }}</span>
            </li>
        @endforeach
    </ul>
@endsection
