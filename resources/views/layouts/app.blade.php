<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Notion Lite')</title>
    <style>
        :root { --bg: #fff; --side: #f7f7f5; --text: #37352f; --muted: #787774; --line: #e9e9e7; --accent: #2383e2; --danger: #e03e3e; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; color: var(--text); background: var(--bg); display: flex; min-height: 100vh; }
        a { color: inherit; text-decoration: none; }
        aside { width: 260px; background: var(--side); border-right: 1px solid var(--line); padding: 16px 10px; flex-shrink: 0; }
        aside .brand { font-weight: 600; padding: 4px 8px 12px; }
        aside input[type=search] { width: 100%; padding: 6px 8px; border: 1px solid var(--line); border-radius: 6px; margin-bottom: 12px; }
        aside ul { list-style: none; margin: 0; padding-left: 12px; }
        aside > ul { padding-left: 0; }
        aside li a { display: block; padding: 4px 8px; border-radius: 4px; color: var(--muted); }
        aside li a:hover, aside li a.active { background: #ebebea; color: var(--text); }
        aside .links { margin-top: 16px; border-top: 1px solid var(--line); padding-top: 12px; }
        main { flex: 1; padding: 48px 72px; max-width: 900px; }
        h1 { font-size: 2.4rem; margin: 0 0 8px; }
        .muted { color: var(--muted); }
        .crumb { color: var(--muted); font-size: .9rem; margin-bottom: 12px; }
        .tags span { display: inline-block; background: #e3f2fd; color: #0b6bcb; border-radius: 4px; padding: 2px 8px; margin-right: 4px; font-size: .85rem; }
        .toolbar { display: flex; gap: 8px; margin: 16px 0 24px; flex-wrap: wrap; }
        .toolbar form { margin: 0; }
        .btn { display: inline-block; border: 1px solid var(--line); background: #fff; border-radius: 6px; padding: 6px 12px; cursor: pointer; font-size: .9rem; color: var(--text); }
        .btn:hover { background: var(--side); }
        .btn.primary { background: var(--accent); border-color: var(--accent); color: #fff; }
        .btn.danger { color: var(--danger); }
        .content { line-height: 1.7; }
        .alert { padding: 10px 14px; border-radius: 6px; margin-bottom: 20px; }
        .alert.ok { background: #edf3ec; color: #448361; }
        .alert.error { background: #fdebec; color: var(--danger); }
        form.page input[name=title] { width: 100%; font-size: 2.2rem; font-weight: 700; border: none; outline: none; margin-bottom: 12px; color: var(--text); }
        form.page input[name=tags] { width: 100%; padding: 8px; border: 1px solid var(--line); border-radius: 6px; margin-bottom: 12px; }
        form.page textarea { width: 100%; min-height: 360px; padding: 12px; border: 1px solid var(--line); border-radius: 6px; font: inherit; line-height: 1.6; }
        .list { list-style: none; padding: 0; }
        .list li { padding: 12px 0; border-bottom: 1px solid var(--line); display: flex; justify-content: space-between; gap: 12px; align-items: center; }
    </style>
</head>
<body>
<aside>
    <div class="brand"><a href="{{ route('pages.index') }}">📝 Notion Lite</a></div>
    <form action="{{ route('pages.search') }}" method="get">
        <input type="search" name="q" placeholder="Rechercher…" value="{{ request('q') }}">
    </form>
    @include('pages._tree', ['nodes' => $tree])
    <div class="links">
        <ul>
            <li><a href="{{ route('pages.create') }}">＋ Nouvelle page</a></li>
            <li><a href="{{ route('pages.trash') }}">🗑 Corbeille</a></li>
        </ul>
    </div>
</aside>
<main>
    @if (session('status'))
        <div class="alert ok">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert error">{{ $errors->first() }}</div>
    @endif

    @yield('content')
</main>
</body>
</html>
