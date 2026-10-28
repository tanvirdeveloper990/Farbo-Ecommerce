<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContactController extends Controller
{
    private function authorizeAccess(Request $request): void
    {
        abort_unless($request->user()->hasPermission('contacts.manage'), 403);
    }

    public function page()
    {
        Inertia::setRootView('storefront');

        return Inertia::render('Storefront/Contact', [
            'settings' => SiteSetting::current(),
        ]);
    }

    public function index(Request $request)
    {
        $this->authorizeAccess($request);
        $filters = $request->validate(['search' => 'nullable|string|max:200']);
        $records = Contact::query()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('phone_number', 'like', '%'.$search.'%')
                        ->orWhere('subject', 'like', '%'.$search.'%');
                });
            })
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('Contacts', ['records' => $records, 'filters' => $filters]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:160',
            'phone_number' => ['required', 'string', 'min:7', 'max:40', 'regex:/^[+0-9()\s.\-]+$/'],
            'subject' => 'required|string|max:500',
            'description' => 'required|string|max:10000',
        ]);

        Contact::create($data);

        return redirect('/contact')->with('success', 'Your message has been sent. We will be in touch soon.');
    }

    public function show(Request $request, Contact $contact)
    {
        $this->authorizeAccess($request);

        return Inertia::render('ContactDetails', ['contact' => $contact]);
    }

    public function edit(Request $request, Contact $contact)
    {
        $this->authorizeAccess($request);

        return Inertia::render('ContactEdit', ['contact' => $contact]);
    }

    public function update(Request $request, Contact $contact)
    {
        $this->authorizeAccess($request);
        $data = $request->validate([
            'name' => 'required|string|max:160',
            'phone_number' => ['required', 'string', 'min:7', 'max:40', 'regex:/^[+0-9()\s.\-]+$/'],
            'subject' => 'required|string|max:500',
            'description' => 'required|string|max:10000',
        ]);
        $contact->update($data);

        return redirect('/admin/contacts/'.$contact->id)->with('success', 'Contact message updated successfully.');
    }

    public function destroy(Request $request, Contact $contact)
    {
        $this->authorizeAccess($request);
        $contact->delete();

        return redirect('/admin/contacts')->with('success', 'Contact message deleted successfully.');
    }
}
