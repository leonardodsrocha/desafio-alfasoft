# Contact Management

Aplicação web CRUD desenvolvida em Laravel 10 para gerenciar uma agenda de contatos.

> **Desafio técnico** — Alfasoft · Fevereiro 2026

---

## Sumário

- [Visão geral](#visão-geral)
- [Stack](#stack)
- [Funcionalidades](#funcionalidades)
- [Pré-requisitos](#pré-requisitos)
- [Instalação local](#instalação-local)
- [Credenciais padrão](#credenciais-padrão)
- [Testes](#testes)
- [Rotas](#rotas)
- [Documentação da API (Swagger)](#documentação-da-api-swagger)
- [Estrutura do projeto](#estrutura-do-projeto)
- [Deploy](#deploy)

---

## Visão geral

Sistema de agenda com autenticação por sessão. Qualquer visitante pode ver a lista e o detalhe dos contatos; criar, editar e excluir exige login. A exclusão é feita via **soft-delete** do Laravel — o registro não sai do banco, apenas recebe `deleted_at`. Após a exclusão, o número de telefone e o e-mail ficam disponíveis para reutilização em novos cadastros.

**Demo online:** https://leonardorocha-lv.recruitment.alfasoft.pt
**Repositório:** https://github.com/leonardodsrocha/desafio-alfasoft

---

## Stack

| Camada | Tecnologia |
|--------|-----------|
| Backend | PHP 8.1 + Laravel 10 |
| Banco (produção) | MariaDB (MySQL) |
| Banco (testes) | SQLite in-memory |
| Frontend | Bootstrap 5.3 via CDN + Bootstrap Icons |
| Autenticação | Sessions (Laravel Auth) |
| Validação | Form Requests |
| Testes | PHPUnit 10 (57 testes, 125 asserções) |

---

## Funcionalidades

- **Listagem pública** de contatos com paginação (10 por página) e busca por nome, telefone ou e-mail
- **Detalhe** do contato em página própria (não popup)
- **Criação** de novo contato com validação server-side
- **Edição** de contato existente com pré-preenchimento do formulário
- **Exclusão** via soft-delete com confirmação no browser
- **Autenticação** por sessão (login/logout) com proteção CSRF e throttle no login
- **Guards de acesso**: visitantes só leem; as operações de escrita exigem login

### Regras de negócio

| Campo | Regra |
|-------|-------|
| `name` | string obrigatório, mínimo 6 caracteres |
| `contact` | exatamente 9 dígitos numéricos, único entre contatos **ativos** |
| `email` | e-mail válido (RFC), único entre contatos **ativos** |

---

## Pré-requisitos

- PHP >= 8.1
- Composer
- SQLite (para desenvolvimento local) ou MySQL/MariaDB

---

## Instalação local

```bash
# 1. Clonar o repositório
git clone https://github.com/leonardodsrocha/desafio-alfasoft.git
cd desafio-alfasoft

# 2. Instalar dependências PHP
composer install

# 3. Criar o arquivo de ambiente
cp .env.example .env

# 4. Gerar a chave da aplicação
php artisan key:generate

# 5. Criar o banco SQLite (padrão do .env.example)
touch database/database.sqlite

# 6. Rodar as migrations e os seeders
php artisan migrate --seed

# 7. Subir o servidor de desenvolvimento
php artisan serve
```

Acesse em http://localhost:8000.

---

## Credenciais padrão

| Campo | Valor |
|-------|-------|
| E-mail | `admin@admin.com` |
| Senha | `123456` |

Criado pelo `AdminUserSeeder` via `updateOrCreate` — rodar os seeders mais de uma vez não duplica o registro.

---

## Testes

A suite usa SQLite in-memory (configurado no `phpunit.xml`) e não afeta o banco de dados local.

```bash
php artisan test
```

```
Tests:    57 passed (125 assertions)
Duration: ~2s
```

### Cobertura dos testes

| Área | Testes |
|------|--------|
| Index — listagem e busca | 8 |
| Create — acesso ao formulário | 2 |
| Store — criação e validação | 14 |
| Show — detalhe e 404 para soft-deleted | 3 |
| Edit — acesso e pré-preenchimento | 3 |
| Update — edição e validação | 8 |
| Destroy — soft-delete | 3 |
| Login — formulário, validação, credenciais | 6 |
| Logout — sessão e guard | 2 |
| Unit — modelo Contact | 3 |

---

## Rotas

| Método | URL | Acesso | Descrição |
|--------|-----|--------|-----------|
| `GET` | `/` | Público | Redirect para `/contacts` |
| `GET` | `/contacts` | Público | Lista paginada com busca opcional |
| `GET` | `/contacts/{id}` | Público | Detalhe do contato |
| `GET` | `/login` | Guest | Formulário de login |
| `POST` | `/login` | Guest | Processar autenticação (throttle: 10/min) |
| `POST` | `/logout` | Auth | Encerrar sessão |
| `GET` | `/contacts/create` | Auth | Formulário de criação |
| `POST` | `/contacts` | Auth | Salvar novo contato |
| `GET` | `/contacts/{id}/edit` | Auth | Formulário de edição |
| `PUT` | `/contacts/{id}` | Auth | Atualizar contato |
| `DELETE` | `/contacts/{id}` | Auth | Soft-delete do contato |

---

## Documentação da API (Swagger)

A documentação interativa está disponível em:

```
/api-docs
```

Gerada com **OpenAPI 3.0** e servida via Swagger UI. O spec completo está em [`public/api-docs/openapi.yaml`](public/api-docs/openapi.yaml).

---

## Estrutura do projeto

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/LoginController.php      # Login e logout
│   │   └── ContactController.php         # CRUD de contatos
│   ├── Requests/
│   │   ├── ContactRequest.php            # Classe base com labels e mensagens
│   │   ├── StoreContactRequest.php       # Validação de criação
│   │   ├── UpdateContactRequest.php      # Validação de edição (ignora próprio ID)
│   │   └── LoginRequest.php             # Validação de credenciais
│   └── Middleware/
│       └── Authenticate.php
├── Models/
│   └── Contact.php                       # Model com SoftDeletes + scopeSearch
database/
├── migrations/
│   └── 2026_02_23_000000_create_contacts_table.php
├── seeders/
│   ├── AdminUserSeeder.php               # admin@admin.com / 123456
│   └── ContactSeeder.php                # 5 contatos de exemplo
└── factories/
    └── ContactFactory.php
resources/views/
├── layouts/app.blade.php                # Layout Bootstrap 5
├── auth/login.blade.php
└── contacts/
    ├── index.blade.php
    ├── create.blade.php
    ├── show.blade.php
    └── edit.blade.php
tests/
├── Unit/ExampleTest.php                 # Testes de configuração do modelo
└── Feature/
    ├── ContactValidationTest.php        # Suite principal (54 testes)
    └── ExampleTest.php                  # Rotas raiz
public/
└── api-docs/
    ├── openapi.yaml                     # OpenAPI 3.0 spec
    └── index.html                       # Swagger UI
```

---

## Deploy

O projeto usa o ambiente fornecido pela Alfasoft com MariaDB. Os passos de deploy são:

```bash
git pull origin master
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> **Importante:** o arquivo `.env` do servidor **não** está no repositório (`.gitignore`). As credenciais do banco já estão pré-configuradas no ambiente remoto — não sobrescreva esse arquivo.

---

## Licença

MIT
