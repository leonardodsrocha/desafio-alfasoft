<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cria a tabela principal da agenda de contatos.
     *
     * O campo `contact` guarda um número de telefone de 9 dígitos; o nome
     * equívoco vem do enunciado do desafio. O índice UNIQUE em ambos os campos
     * (telefone e e-mail) é aplicado no nível do banco de dados e não apenas
     * na camada de validação, o que garante consistência mesmo em cenários
     * de concorrência ou inserções diretas.
     */
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact', 9)->unique();
            $table->string('email')->unique();
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
