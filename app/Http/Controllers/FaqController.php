<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FaqController extends Controller
{
    private function authorizeAccess(Request $r): void
    {
        abort_unless($r->user()->hasPermission('faqs.manage'), 403);
    }

    public function index(Request $r)
    {
        $this->authorizeAccess($r);
        $filters = $r->validate([
            'search' => 'nullable|string|max:200',
            'status' => 'nullable|in:active,inactive',
        ]);
        $records = Faq::query()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('question', 'like', '%'.$search.'%')
                        ->orWhere('answer', 'like', '%'.$search.'%');
                });
            })
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status'] === 'active'))
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('Faqs', ['records' => $records, 'filters' => $filters]);
    }

    public function form(Request $r, ?Faq $faq = null)
    {
        $this->authorizeAccess($r);

        return Inertia::render('FaqForm', ['faq' => $faq]);
    }

    public function save(Request $r, ?Faq $faq = null)
    {
        $this->authorizeAccess($r);
        $data = $r->validate(['question' => 'required|string|max:500', 'answer' => 'required|string|max:10000', 'status' => 'required|boolean']);
        $faq ??= new Faq;
        $faq->fill($data)->save();

        return redirect('/admin/faqs')->with('success', 'FAQ saved successfully.');
    }

    public function destroy(Request $r, Faq $faq)
    {
        $this->authorizeAccess($r);
        $faq->delete();

        return redirect('/admin/faqs')->with('success', 'FAQ deleted successfully.');
    }
}
