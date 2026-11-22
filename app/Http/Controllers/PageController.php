<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavePageRequest;
use App\Services\PageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(private readonly PageService $pages) {}

    public function index(): View|RedirectResponse
    {
        $first = $this->pages->tree()[0] ?? null;

        return $first
            ? redirect()->route('pages.show', $first->page->id())
            : view('pages.empty');
    }

    public function show(int $id): View
    {
        $page = $this->pages->get($id);

        return view('pages.show', [
            'page' => $page,
            'children' => $this->pages->childrenOf($page),
            'parent' => $page->parentId() ? $this->pages->get($page->parentId()) : null,
        ]);
    }

    public function create(Request $request): View
    {
        $parentId = $request->integer('parent') ?: null;

        return view('pages.form', [
            'page' => null,
            'parent' => $parentId ? $this->pages->get($parentId) : null,
        ]);
    }

    public function store(SavePageRequest $request): RedirectResponse
    {
        $page = $this->pages->create(
            $request->string('title'),
            $request->input('content'),
            $request->input('tags'),
            $request->integer('parent_id') ?: null,
        );

        return redirect()->route('pages.show', $page->id())->with('status', 'Page créée.');
    }

    public function edit(int $id): View
    {
        return view('pages.form', ['page' => $this->pages->get($id), 'parent' => null]);
    }

    public function update(SavePageRequest $request, int $id): RedirectResponse
    {
        $this->pages->update($id, $request->string('title'), $request->input('content'), $request->input('tags'));

        return redirect()->route('pages.show', $id)->with('status', 'Page enregistrée.');
    }

    public function archive(int $id): RedirectResponse
    {
        $this->pages->archive($id);

        return redirect()->route('pages.index')->with('status', 'Page mise à la corbeille.');
    }

    public function restore(int $id): RedirectResponse
    {
        $this->pages->restore($id);

        return redirect()->route('pages.show', $id)->with('status', 'Page restaurée.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->pages->delete($id);

        return redirect()->route('pages.trash')->with('status', 'Page supprimée définitivement.');
    }

    public function trash(): View
    {
        return view('pages.trash', ['pages' => $this->pages->trash()]);
    }

    public function search(Request $request): View
    {
        $term = $request->string('q')->toString();

        return view('pages.search', ['term' => $term, 'results' => $this->pages->search($term)]);
    }
}
