<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Registro de um contato na agenda.
 *
 * O campo `contact` armazena o número de telefone com exatamente 9 dígitos.
 * O nome do campo vem do enunciado do desafio e é mantido para garantir
 * consistência entre a migração, o formulário e a validação.
 *
 * @property int                             $id
 * @property string                          $name
 * @property string                          $contact    Número de telefone (9 dígitos)
 * @property string                          $email
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon      $created_at
 * @property \Illuminate\Support\Carbon      $updated_at
 */
class Contact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'contact',
        'email',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /**
     * Filtra contatos que contenham o termo em qualquer um dos três campos visíveis.
     *
     * A pesquisa usa OR entre colunas para que o usuário não precise saber
     * onde está a informação — pesquisar "912" pode encontrar pelo telefone,
     * pesquisar "silva" pelo nome ou e-mail. A correspondência parcial (LIKE %term%)
     * é intencional e permite encontrar "João" escrevendo apenas "oão".
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('contact', 'like', "%{$term}%");
        });
    }
}
