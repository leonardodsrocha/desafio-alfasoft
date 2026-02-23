<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    /**
     * Display a listing of contacts (public).
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $contacts = Contact::when($search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('contact', 'like', "%{$search}%");
        })->latest()->paginate(10)->withQueryString();

        return view('contacts.index', compact('contacts', 'search'));
    }

    /**
     * Show the form for creating a new contact.
     */
    public function create(): View
    {
        return view('contacts.create');
    }

    /**
     * Store a newly created contact in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'min:6'],
            'contact' => ['required', 'digits:9', 'unique:contacts,contact'],
            'email'   => ['required', 'email', 'unique:contacts,email'],
        ]);

        Contact::create($validated);

        return redirect()->route('contacts.index')
            ->with('success', 'Contact created successfully.');
    }

    /**
     * Display the specified contact.
     */
    public function show(Contact $contact): View
    {
        return view('contacts.show', compact('contact'));
    }

    /**
     * Show the form for editing the specified contact.
     */
    public function edit(Contact $contact): View
    {
        return view('contacts.edit', compact('contact'));
    }

    /**
     * Update the specified contact in storage.
     */
    public function update(Request $request, Contact $contact): RedirectResponse
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'min:6'],
            'contact' => ['required', 'digits:9', 'unique:contacts,contact,' . $contact->id],
            'email'   => ['required', 'email', 'unique:contacts,email,' . $contact->id],
        ]);

        $contact->update($validated);

        return redirect()->route('contacts.show', $contact)
            ->with('success', 'Contact updated successfully.');
    }

    /**
     * Soft-delete the specified contact.
     */
    public function destroy(Contact $contact): RedirectResponse
    {
        $contact->delete();

        return redirect()->route('contacts.index')
            ->with('success', 'Contact deleted successfully.');
    }
}
