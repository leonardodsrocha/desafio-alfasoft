@extends('layouts.app')

@section('title', 'New Contact')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">

        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('contacts.index') }}">Contacts</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">New Contact</li>
            </ol>
        </nav>

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white py-3">
                <h4 class="card-title mb-0 fw-bold">
                    <i class="bi bi-person-plus me-2"></i>New Contact
                </h4>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('contacts.store') }}" novalidate>
                    @csrf

                    {{-- Name --}}
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">
                            Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               id="name"
                               name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}"
                               placeholder="e.g. Maria Silva"
                               autofocus>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Minimum 6 characters.</div>
                    </div>

                    {{-- Phone --}}
                    <div class="mb-3">
                        <label for="contact" class="form-label fw-semibold">
                            Phone <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               id="contact"
                               name="contact"
                               class="form-control @error('contact') is-invalid @enderror"
                               value="{{ old('contact') }}"
                               placeholder="e.g. 912345678"
                               inputmode="numeric"
                               pattern="[0-9]*"
                               maxlength="9"
                               oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                        @error('contact')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Exactly 9 digits, must be unique.</div>
                    </div>

                    {{-- Email --}}
                    <div class="mb-4">
                        <label for="email" class="form-label fw-semibold">
                            Email <span class="text-danger">*</span>
                        </label>
                        <input type="email"
                               id="email"
                               name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}"
                               placeholder="e.g. maria@example.com">
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Must be a valid & unique email address.</div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('contacts.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Save Contact
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
