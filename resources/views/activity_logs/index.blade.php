@extends('layouts.app')

@section('title', 'Activity Log')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 fw-bold">
        <i class="bi bi-journal-text me-2 text-primary"></i>Activity Log
    </h1>
    <span class="text-muted small">
        Full history of contact creations, edits and deletions.
    </span>
</div>

{{-- Filtro por ação --}}
<div class="mb-4 d-flex gap-2 flex-wrap">
    <a href="{{ route('activity-logs.index') }}"
       class="btn btn-sm {{ !$filter ? 'btn-dark' : 'btn-outline-secondary' }}">
        <i class="bi bi-list me-1"></i>All
    </a>
    <a href="{{ route('activity-logs.index', ['action' => 'created']) }}"
       class="btn btn-sm {{ $filter === 'created' ? 'btn-success' : 'btn-outline-success' }}">
        <i class="bi bi-plus-circle me-1"></i>Creations
    </a>
    <a href="{{ route('activity-logs.index', ['action' => 'updated']) }}"
       class="btn btn-sm {{ $filter === 'updated' ? 'btn-warning text-dark' : 'btn-outline-warning' }}">
        <i class="bi bi-pencil me-1"></i>Edits
    </a>
    <a href="{{ route('activity-logs.index', ['action' => 'deleted']) }}"
       class="btn btn-sm {{ $filter === 'deleted' ? 'btn-danger' : 'btn-outline-danger' }}">
        <i class="bi bi-trash me-1"></i>Deletions
    </a>
</div>

@if($logs->isEmpty())
<div class="card shadow-sm">
    <div class="card-body text-center py-5">
        <i class="bi bi-journal-x display-3 text-muted"></i>
        <p class="mt-3 mb-0 text-muted fs-5">No activity recorded.</p>
    </div>
</div>
@else
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" style="width:130px">Action</th>
                        <th>Contact</th>
                        <th>User</th>
                        <th>IP</th>
                        <th style="width:170px">Date / Time</th>
                        <th class="text-center" style="width:80px">Details</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr>
                        {{-- Ação --}}
                        <td class="ps-3">
                            <span class="badge {{ $log->actionBadgeClass() }} rounded-pill px-3 py-2">
                                @if($log->action === 'created')
                                    <i class="bi bi-plus-circle me-1"></i>
                                @elseif($log->action === 'updated')
                                    <i class="bi bi-pencil me-1"></i>
                                @else
                                    <i class="bi bi-trash me-1"></i>
                                @endif
                                {{ $log->actionLabel() }}
                            </span>
                        </td>

                        {{-- Contato --}}
                        <td>
                            <span class="fw-semibold">{{ $log->contact_name }}</span>
                            @if($log->contact_id)
                            <span class="text-muted small ms-1">#{{ $log->contact_id }}</span>
                            @endif
                        </td>

                        {{-- Usuário --}}
                        <td>
                            @if($log->user_name)
                                <i class="bi bi-person me-1 text-muted"></i>{{ $log->user_name }}
                            @else
                                <span class="text-muted fst-italic">System</span>
                            @endif
                        </td>

                        {{-- IP --}}
                        <td>
                            <code class="text-muted small">{{ $log->ip_address ?? '—' }}</code>
                        </td>

                        {{-- Data --}}
                        <td class="text-muted small">
                            <i class="bi bi-clock me-1"></i>
                            {{ $log->created_at->format('d/m/Y H:i:s') }}
                        </td>

                        {{-- Detalhes (old/new values) --}}
                        <td class="text-center">
                            @if($log->old_values || $log->new_values)
                            <button class="btn btn-sm btn-outline-secondary"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#log-detail-{{ $log->id }}"
                                    title="View details">
                                <i class="bi bi-chevron-down"></i>
                            </button>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>

                    {{-- Linha expansível com diff dos valores --}}
                    @if($log->old_values || $log->new_values)
                    <tr class="collapse" id="log-detail-{{ $log->id }}">
                        <td colspan="6" class="bg-light border-top-0 pt-0 pb-3 px-4">
                            <div class="row g-3 mt-0">
                                @if($log->old_values)
                                <div class="col-md-6">
                                    <p class="small fw-semibold text-danger mb-2">
                                        <i class="bi bi-dash-circle me-1"></i>Before
                                    </p>
                                    <table class="table table-sm table-bordered mb-0 bg-white">
                                        @foreach($log->old_values as $field => $value)
                                        <tr>
                                            <th class="text-muted small" style="width:40%">{{ $field }}</th>
                                            <td class="small font-monospace">{{ $value }}</td>
                                        </tr>
                                        @endforeach
                                    </table>
                                </div>
                                @endif

                                @if($log->new_values)
                                <div class="col-md-6">
                                    <p class="small fw-semibold text-success mb-2">
                                        <i class="bi bi-plus-circle me-1"></i>
                                        {{ $log->action === 'created' ? 'Registered values' : 'After' }}
                                    </p>
                                    <table class="table table-sm table-bordered mb-0 bg-white">
                                        @foreach($log->new_values as $field => $value)
                                        <tr>
                                            <th class="text-muted small" style="width:40%">{{ $field }}</th>
                                            <td class="small font-monospace">{{ $value }}</td>
                                        </tr>
                                        @endforeach
                                    </table>
                                </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Paginação --}}
@if($logs->hasPages())
<div class="mt-4 d-flex justify-content-center">
    {{ $logs->links() }}
</div>
@endif
@endif
@endsection
