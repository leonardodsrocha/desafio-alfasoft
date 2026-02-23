@extends('layouts.app')

@section('title', $contact->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">

        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('contacts.index') }}">Contacts</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">{{ $contact->name }}</li>
            </ol>
        </nav>

        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0 fw-bold">
                    <i class="bi bi-person-vcard me-2"></i>Contact Details
                </h4>
                <span class="badge bg-secondary">#{{ $contact->id }}</span>
            </div>

            <div class="card-body p-4">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">
                        <i class="bi bi-person me-1"></i>Name
                    </dt>
                    <dd class="col-sm-8 fw-semibold fs-5">{{ $contact->name }}</dd>

                    <dt class="col-sm-4 text-muted">
                        <i class="bi bi-telephone me-1"></i>Phone
                    </dt>
                    <dd class="col-sm-8">
                        <a href="tel:{{ $contact->contact }}" class="text-decoration-none">
                            {{ $contact->contact }}
                        </a>
                    </dd>

                    <dt class="col-sm-4 text-muted">
                        <i class="bi bi-envelope me-1"></i>Email
                    </dt>
                    <dd class="col-sm-8">
                        <a href="mailto:{{ $contact->email }}" class="text-decoration-none">
                            {{ $contact->email }}
                        </a>
                    </dd>

                    <dt class="col-sm-4 text-muted">
                        <i class="bi bi-calendar-plus me-1"></i>Created
                    </dt>
                    <dd class="col-sm-8 text-muted">
                        {{ $contact->created_at->format('d/m/Y H:i') }}
                    </dd>

                    <dt class="col-sm-4 text-muted">
                        <i class="bi bi-calendar-check me-1"></i>Updated
                    </dt>
                    <dd class="col-sm-8 text-muted">
                        {{ $contact->updated_at->format('d/m/Y H:i') }}
                    </dd>
                </dl>
            </div>

            <div class="card-footer bg-transparent d-flex justify-content-between align-items-center py-3">
                <a href="{{ route('contacts.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to list
                </a>

                @auth
                <div class="d-flex gap-2">
                    <a href="{{ route('contacts.edit', $contact) }}" class="btn btn-primary">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </a>

                    <form method="POST"
                          action="{{ route('contacts.destroy', $contact) }}"
                          id="delete-form-show">
                        @csrf
                        @method('DELETE')
                        <button type="button"
                                class="btn btn-danger"
                                onclick="confirmDelete('{{ addslashes($contact->name) }}')">
                            <i class="bi bi-trash me-1"></i>Delete
                        </button>
                    </form>
                </div>
                @endauth
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmDelete(name) {
        if (confirm(`Are you sure you want to delete "${name}"?\nThis action cannot be undone.`)) {
            document.getElementById('delete-form-show').submit();
        }
    }
</script>
@endpush
