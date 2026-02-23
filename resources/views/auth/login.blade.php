@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">

        <div class="text-center mb-4">
            <i class="bi bi-person-lock display-4 text-primary"></i>
            <h2 class="fw-bold mt-2">Sign In</h2>
            <p class="text-muted">Sign in to manage contacts</p>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('login') }}" novalidate>
                    @csrf

                    {{-- Email --}}
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">
                            Email address
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}"
                                   placeholder="admin@admin.com"
                                   autocomplete="email"
                                   autofocus>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Password --}}
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">
                            Password
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password"
                                   id="password"
                                   name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="••••••"
                                   autocomplete="current-password">
                            <button type="button"
                                    class="btn btn-outline-secondary"
                                    id="togglePassword"
                                    title="Show / hide password">
                                <i class="bi bi-eye" id="eyeIcon"></i>
                            </button>
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Remember me --}}
                    <div class="mb-4 form-check">
                        <input type="checkbox"
                               class="form-check-input"
                               id="remember"
                               name="remember"
                               {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label text-muted" for="remember">
                            Remember me
                        </label>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <p class="text-center mt-3">
            <a href="{{ route('contacts.index') }}" class="text-decoration-none text-muted">
                <i class="bi bi-arrow-left me-1"></i>Back to contacts list
            </a>
        </p>

    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('togglePassword').addEventListener('click', function () {
        const input = document.getElementById('password');
        const icon  = document.getElementById('eyeIcon');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    });
</script>
@endpush
