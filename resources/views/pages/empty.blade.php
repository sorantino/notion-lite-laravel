@extends('layouts.app')

@section('content')
    <h1>Bienvenue 👋</h1>
    <p class="muted">Votre espace est vide.</p>
    <a class="btn primary" href="{{ route('pages.create') }}">Créer une première page</a>
@endsection
