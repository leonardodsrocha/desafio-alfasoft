<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    /**
     * Lista paginada de contatos com pesquisa opcional.
     *
     * A pesquisa é passada de volta à view para manter o campo preenchido
     * após o form ser submetido. A paginação usa withQueryString() para que
     * o parâmetro ?search= não desapareça ao mudar de página.
     * A lógica de filtragem vive no scopeSearch do modelo — o controller
     * apenas decide quando aplicar o scope.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $contacts = Contact::when($search, fn ($q) => $q->search($search))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('contacts.index', compact('contacts', 'search'));
    }

    /**
     * Formulário de criação de um novo contato.
     *
     * Esta rota está declarada antes de contacts/{contact} nas rotas protegidas
     * para evitar que o segmento literal "create" seja confundido com um ID
     * de contato durante o route-model binding.
     */
    public function create(): View
    {
        return view('contacts.create');
    }

    /**
     * Persiste o novo contato após validação automática pelo StoreContactRequest.
     *
     * Se a validação falhar, Laravel redireciona de volta sem executar este método.
     * Só os campos declarados em $fillable são escritos na base de dados,
     * independentemente do que chegue no request.
     */
    public function store(StoreContactRequest $request): RedirectResponse
    {
        Contact::create($request->validated());

        return redirect()->route('contacts.index')
            ->with('success', 'Contact created successfully.');
    }

    /**
     * Página de detalhe de um contato resolvido por route-model binding.
     *
     * O binding padrão já exclui os registros marcados com soft-delete;
     * acessar o URL de um contato apagado retorna automaticamente um 404
     * sem nenhuma verificação explícita aqui.
     */
    public function show(Contact $contact): View
    {
        return view('contacts.show', compact('contact'));
    }

    /**
     * Formulário de edição pré-preenchido com os dados atuais do contato.
     */
    public function edit(Contact $contact): View
    {
        return view('contacts.edit', compact('contact'));
    }

    /**
     * Aplica as alterações e redireciona para a página de detalhe.
     *
     * O UpdateContactRequest ignora o ID do próprio contato nas regras de
     * unicidade de telefone e e-mail, evitando um falso erro de duplicado quando
     * o usuário submete sem alterar esses campos.
     */
    public function update(UpdateContactRequest $request, Contact $contact): RedirectResponse
    {
        $contact->update($request->validated());

        return redirect()->route('contacts.show', $contact)
            ->with('success', 'Contact updated successfully.');
    }

    /**
     * Apaga o contato de forma reversível (soft-delete).
     *
     * O registro não é removido fisicamente — fica na tabela com deleted_at
     * preenchido. Isso mantém ativas as restrições de unicidade de telefone
     * e e-mail: o mesmo número ou endereço não pode ser cadastrado novamente,
     * mesmo que o contato original tenha sido excluído.
     */
    public function destroy(Contact $contact): RedirectResponse
    {
        $contact->delete();

        return redirect()->route('contacts.index')
            ->with('success', 'Contact deleted successfully.');
    }
}

