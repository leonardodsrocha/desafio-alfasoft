<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cria a tabela principal da agenda de contatos.
     *
     * Os campos `contact` e `email` têm índice regular para desempenho de consultas.
     * A unicidade é aplicada somente na camada de validação do Laravel usando
     * whereNull('deleted_at'), permitindo que dados de contatos excluídos via
     * soft-delete sejam reutilizados em novos cadastros.
     */
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact', 9)->index();
            $table->string('email')->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Remove a tabela por completo em caso de rollback.
     *
     * dropIfExists() é usado defensivamente para que o rollback seja
     * idêmpotente mesmo que a migração anterior tenha falhado a meio.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
