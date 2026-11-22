@extends('layouts.app')

@section('title', 'Corbeille')

@section('content')
    <h1>🗑 Corbeille</h1>
    <p class="muted">Les pages ici sont en lecture seule. Restaurez-les ou supprimez-les définitivement.</p>

    <ul class="list">
        @forelse ($pages as $page)
            <li>
                <span>📄 {{ $page->title() }}</span>
                <span class="toolbar" style="margin: 0">
                    <form method="post" action="{{ route('pages.restore', $page->id()) }}">
                        @csrf
                        <button class="btn">Restaurer</button>
                    </form>
                    <form method="post" action="{{ route('pages.destroy', $page->id()) }}">
                        @csrf
                        @method('delete')
                        <button class="btn danger">Supprimer</button>
                    </form>
                </span>
            </li>
        @empty
            <li class="muted">La corbeille est vide.</li>
        @endforelse
    </ul>
@endsection
