<ul>
    @foreach ($nodes as $node)
        <li>
            <a href="{{ route('pages.show', $node->page->id()) }}"
               @class(['active' => request()->routeIs('pages.show') && (int) request()->route('id') === $node->page->id()])>
                📄 {{ $node->page->title() }}
            </a>
            @if ($node->children)
                @include('pages._tree', ['nodes' => $node->children])
            @endif
        </li>
    @endforeach
</ul>
