<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    /**
     * Lista paginada do log de auditoria, com filtro opcional por ação.
     *
     * Exibida em ordem cronológica inversa (mais recente primeiro) com
     * 20 registros por página. Acessível somente por usuários autenticados
     * — definido pelo middleware 'auth' na rota.
     */
    public function index(Request $request): View
    {
        $filter = $request->string('action')->toString();

        $logs = ActivityLog::when(
                $filter && in_array($filter, ['created', 'updated', 'deleted']),
                fn ($q) => $q->where('action', $filter)
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('activity_logs.index', compact('logs', 'filter'));
    }
}
