<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Contact;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Observa eventos do modelo Contact e persiste registros de auditoria.
 *
 * Cada observer method recebe a instância do contato já com o estado
 * correspondente ao momento do evento:
 *
 * - created : contato já salvo, new_values = atributos preenchidos
 * - updated : após o UPDATE — getOriginal() retém os valores pré-save e
 *             getChanges() contém apenas os atributos que foram alterados
 * - deleted : soft-delete aplicado — registra snapshot final do contato
 */
class ContactObserver
{
    /**
     * Lista dos campos do contato relevantes para o log.
     * Campos de controle (id, timestamps, deleted_at) são omitidos.
     */
    private const TRACKED_FIELDS = ['name', 'contact', 'email'];

    // -------------------------------------------------------------------------

    /**
     * Registra a criação de um novo contato.
     */
    public function created(Contact $contact): void
    {
        $this->log('created', $contact, null, $this->pick($contact->getAttributes()));
    }

    /**
     * Registra a atualização de um contato com os valores antes e depois.
     *
     * Após o UPDATE, o Eloquent mantém em getOriginal() os valores que
     * estavam no banco antes do save, e em getChanges() somente os campos
     * que foram efetivamente alterados. Não é necessário interceptar o
     * evento 'updating' — toda a informação está disponível aqui.
     */
    public function updated(Contact $contact): void
    {
        $changed = array_keys($contact->getChanges());
        $tracked = array_intersect($changed, self::TRACKED_FIELDS);

        // Ignora updates que não alteraram campos rastreados (ex.: só updated_at)
        if (empty($tracked)) {
            return;
        }

        $oldValues = array_intersect_key($contact->getOriginal(), array_flip(self::TRACKED_FIELDS));
        $newValues = array_intersect_key($contact->getChanges(),  array_flip(self::TRACKED_FIELDS));

        $this->log('updated', $contact, $oldValues, $newValues);
    }

    /**
     * Registra o soft-delete de um contato.
     */
    public function deleted(Contact $contact): void
    {
        $this->log('deleted', $contact, $this->pick($contact->getAttributes()), null);
    }

    // -------------------------------------------------------------------------

    /**
     * Persiste um registro de auditoria na tabela activity_logs.
     *
     * O IP e o usuário são capturados em tempo de execução via facades estáticas.
     * Fora do contexto de uma requisição HTTP (ex.: seeders, jobs de fila),
     * Request::ip() pode retornar '127.0.0.1' ou null — isso é aceitável.
     */
    private function log(
        string $action,
        Contact $contact,
        ?array $oldValues,
        ?array $newValues
    ): void {
        $user = Auth::user();

        ActivityLog::create([
            'action'       => $action,
            'contact_id'   => $contact->id,
            'contact_name' => $contact->name,
            'user_id'      => $user?->id,
            'user_name'    => $user?->name,
            'ip_address'   => Request::ip(),
            'old_values'   => $oldValues ?: null,
            'new_values'   => $newValues ?: null,
        ]);
    }

    /**
     * Seleciona apenas os campos rastreados de um array de atributos.
     */
    private function pick(array $attributes): array
    {
        return array_intersect_key($attributes, array_flip(self::TRACKED_FIELDS));
    }
}
