<?php

namespace App\Http\Controllers;

use App\Application\NoteService;
use App\Application\SummarizeNote;
use App\Http\Requests\SaveNoteRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NoteController extends Controller
{
    public function __construct(private readonly NoteService $notes) {}

    public function index(): View|RedirectResponse
    {
        $first = $this->notes->tree()[0] ?? null;

        return $first
            ? redirect()->route('notes.show', $first->note->id())
            : view('notes.empty');
    }

    public function show(int $id): View
    {
        $note = $this->notes->get($id);

        return view('notes.show', [
            'note' => $note,
            'children' => $this->notes->childrenOf($note),
            'parent' => $note->parentId() ? $this->notes->get($note->parentId()) : null,
        ]);
    }

    public function create(Request $request): View
    {
        $parentId = $request->integer('parent') ?: null;

        return view('notes.form', [
            'note' => null,
            'parent' => $parentId ? $this->notes->get($parentId) : null,
        ]);
    }

    public function store(SaveNoteRequest $request): RedirectResponse
    {
        $note = $this->notes->create(
            $request->string('title'),
            $request->input('content'),
            $request->input('tags'),
            $request->integer('parent_id') ?: null,
        );

        return redirect()->route('notes.show', $note->id())->with('status', 'Note créée.');
    }

    public function edit(int $id): View
    {
        return view('notes.form', ['note' => $this->notes->get($id), 'parent' => null]);
    }

    public function update(SaveNoteRequest $request, int $id): RedirectResponse
    {
        $this->notes->update($id, $request->string('title'), $request->input('content'), $request->input('tags'));

        return redirect()->route('notes.show', $id)->with('status', 'Note enregistrée.');
    }

    public function summarize(int $id, SummarizeNote $summarizeNote): RedirectResponse
    {
        $summarizeNote->handle($id);

        return redirect()->route('notes.show', $id)->with('status', 'Résumé généré.');
    }

    public function archive(int $id): RedirectResponse
    {
        $this->notes->archive($id);

        return redirect()->route('notes.index')->with('status', 'Note mise à la corbeille.');
    }

    public function restore(int $id): RedirectResponse
    {
        $this->notes->restore($id);

        return redirect()->route('notes.show', $id)->with('status', 'Note restaurée.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->notes->delete($id);

        return redirect()->route('notes.trash')->with('status', 'Note supprimée définitivement.');
    }

    public function trash(): View
    {
        return view('notes.trash', ['notes' => $this->notes->trash()]);
    }

    public function search(Request $request): View
    {
        $term = $request->string('q')->toString();

        return view('notes.search', ['term' => $term, 'results' => $this->notes->search($term)]);
    }
}
