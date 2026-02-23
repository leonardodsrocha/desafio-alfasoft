@extends('layouts.app')

@section('title', 'Contacts')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 fw-bold">
        <i class="bi bi-people-fill me-2 text-primary"></i>Contacts
    </h1>
    @auth
    <a href="{{ route('contacts.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus me-1"></i>New Contact
    </a>
    @endauth
</div>

{{-- Search form --}}
<form method="GET" action="{{ route('contacts.index') }}" class="mb-4">
    <div class="input-group">
        <span class="input-group-text bg-white">
            <i class="bi bi-search text-muted"></i>
        </span>
        <input type="text"
               name="search"
               class="form-control"
               placeholder="Search by name, email or phone…"
               value="{{ $search ?? '' }}">
        <button type="submit" class="btn btn-outline-secondary">Search</button>
        @if($search)
        <a href="{{ route('contacts.index') }}" class="btn btn-outline-danger">
            <i class="bi bi-x-lg"></i>
        </a>
        @endif
    </div>
</form>

@if($contacts->isEmpty())
<div class="card shadow-sm">
    <div class="card-body text-center py-5">
        <i class="bi bi-person-x display-3 text-muted"></i>
        <p class="mt-3 mb-0 text-muted fs-5">No contacts found.</p>
        @auth
        <a href="{{ route('contacts.create') }}" class="btn btn-primary mt-3">
            <i class="bi bi-person-plus me-1"></i>Add your first contact
        </a>
        @endauth
    </div>
</div>
@else
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($contacts as $contact)
                    <tr>
                        <td class="ps-3 text-muted">{{ $contact->id }}</td>
                        <td class="fw-semibold">{{ $contact->name }}</td>
                        <td>{{ $contact->contact }}</td>
                        <td>{{ $contact->email }}</td>
                        <td class="text-center">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('contacts.show', $contact) }}"
                                   class="btn btn-sm btn-outline-info action-btn"
                                   title="View details">
                                    <i class="bi bi-eye me-1"></i>View
                                </a>
                                @auth
                                <a href="{{ route('contacts.edit', $contact) }}"
                                   class="btn btn-sm btn-outline-primary action-btn"
                                   title="Edit contact">
                                    <i class="bi bi-pencil me-1"></i>Edit
                                </a>
                                <form method="POST"
                                      action="{{ route('contacts.destroy', $contact) }}"
                                      class="d-inline"
                                      id="delete-form-{{ $contact->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger action-btn"
                                            title="Delete contact"
                                            data-contact-id="{{ $contact->id }}"
                                            data-contact-name="{{ $contact->name }}"
                                            onclick="confirmDelete(this)">
                                        <i class="bi bi-trash me-1"></i>Delete
                                    </button>
                                </form>
                                @endauth
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Pagination --}}
@if($contacts->hasPages())
<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-muted">
        Showing {{ $contacts->firstItem() }}–{{ $contacts->lastItem() }} of {{ $contacts->total() }} contacts
    </small>
    {{ $contacts->links() }}
</div>
@endif
@endif
@endsection

@push('scripts')
<script>
    function confirmDelete(button) {
        const id   = button.dataset.contactId;
        const name = button.dataset.contactName;

        if (confirm(`Are you sure you want to delete "${name}"?\nThis action cannot be undone.`)) {
            document.getElementById('delete-form-' + id).submit();
        }
    }
</script>
@endpush
