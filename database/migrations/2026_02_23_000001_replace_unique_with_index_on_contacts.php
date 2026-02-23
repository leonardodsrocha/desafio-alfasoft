<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Substitui os índices UNIQUE de contact e email por índices regulares.
     *
     * Motivo: a unicidade precisa levar em conta o soft-delete — registros
     * excluídos devem liberar seu telefone e e-mail para reutilização.
     * Como índices UNIQUE no banco não conhecem deleted_at, a restrição passou
     * a ser responsabilidade exclusiva da camada de validação Laravel
     * (Rule::unique()->whereNull('deleted_at')).
     */
    public function up(): void
    {
        // Verifica quais índices existem antes de tentar remover.
        // Em instalações novas (testes, clone do repositório) a migração original
        // já não cria os UNIQUE, portanto este bloco é necessário somente para
        // bancos de dados que foram criados com a versão anterior da migração.
        $existingIndexes = collect(Schema::getIndexes('contacts'))->pluck('name');

        Schema::table('contacts', function (Blueprint $table) use ($existingIndexes) {
            if ($existingIndexes->contains('contacts_contact_unique')) {
                $table->dropUnique(['contact']);
            }

            if ($existingIndexes->contains('contacts_email_unique')) {
                $table->dropUnique(['email']);
            }
        });
    }

    /**
     * Restaura os índices UNIQUE originais em caso de rollback.
     */
    public function down(): void
    {
        $existingIndexes = collect(Schema::getIndexes('contacts'))->pluck('name');

        Schema::table('contacts', function (Blueprint $table) use ($existingIndexes) {
            if ($existingIndexes->contains('contacts_contact_index')) {
                $table->dropIndex(['contact']);
            }
            if ($existingIndexes->contains('contacts_email_index')) {
                $table->dropIndex(['email']);
            }

            $table->unique('contact');
            $table->unique('email');
        });
    }
};
