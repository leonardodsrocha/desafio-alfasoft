<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cria a tabela de log de auditoria para o modelo Contact.
     *
     * Cada linha representa uma ação realizada sobre um contato. Os campos
     * old_values e new_values armazenam um snapshot JSON dos atributos
     * relevantes do contato no momento da ação, permitindo rastrear o que
     * mudou e quem foi o responsável.
     *
     * A tabela usa apenas created_at (sem updated_at) porque um registro de
     * log nunca é modificado depois de criado — is imutável por design.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // Qual operação foi executada
            $table->enum('action', ['created', 'updated', 'deleted']);

            // Referência ao contato afetado (nullable: contatos podem ser purgados)
            $table->unsignedBigInteger('contact_id')->nullable()->index();
            $table->string('contact_name');         // snapshot do nome no momento da ação

            // Quem fez a ação
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable(); // snapshot do nome do usuário

            // Contexto de rede
            $table->string('ip_address', 45)->nullable(); // suporta IPv6

            // Valores antes e depois (só preenchidos em atualizações)
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            // Apenas created_at — logs são imutáveis
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Remove a tabela de logs em caso de rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
