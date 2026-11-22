<ul>
    @foreach ($nodes as $node)
        <li>
            <a href="{{ route('notes.show', $node->note->id()) }}"
               @class(['active' => request()->routeIs('notes.show') && (int) request()->route('id') === $node->note->id()])>
                📄 {{ $node->note->title() }}
            </a>
            @if ($node->children)
                @include('notes._tree', ['nodes' => $node->children])
            @endif
        </li>
    @endforeach
</ul>
