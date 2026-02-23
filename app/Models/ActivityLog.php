<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro imutável de uma ação executada sobre um contato.
 *
 * @property int                        $id
 * @property string                     $action       'created' | 'updated' | 'deleted'
 * @property int|null                   $contact_id
 * @property string                     $contact_name  Snapshot do nome no momento da ação
 * @property int|null                   $user_id
 * @property string|null                $user_name     Snapshot do nome do usuário
 * @property string|null                $ip_address
 * @property array|null                 $old_values    Atributos antes da alteração
 * @property array|null                 $new_values    Atributos após a alteração
 * @property \Illuminate\Support\Carbon $created_at
 */
class ActivityLog extends Model
{
    /** Logs nunca são atualizados após criação — desabilita updated_at. */
    public const UPDATED_AT = null;

    protected $fillable = [
        'action',
        'contact_id',
        'contact_name',
        'user_id',
        'user_name',
        'ip_address',
        'old_values',
        'new_values',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Relação com o contato afetado (nullable: contato pode ter sido purgado).
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class)->withTrashed();
    }

    /**
     * Relação com o usuário que executou a ação.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Rótulo legível para a ação registrada.
     */
    public function actionLabel(): string
    {
        return match ($this->action) {
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            default   => ucfirst($this->action),
        };
    }

    /**
     * Classe Bootstrap de badge correspondente à ação.
     */
    public function actionBadgeClass(): string
    {
        return match ($this->action) {
            'created' => 'bg-success',
            'updated' => 'bg-warning text-dark',
            'deleted' => 'bg-danger',
            default   => 'bg-secondary',
        };
    }
}
