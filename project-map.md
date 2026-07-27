# Mapa do Projeto
Atualizado em: 2026-07-26
Stack: PHP 8.2+, CodeIgniter 4.7, PostgreSQL 15

## Estrutura de pastas
- `app/` — raiz da aplicação (PSR-4 namespace `App\`)
- `app/Config/` — configurações do framework (database, routes, services, etc.)
- `app/Controllers/` — controllers MVC. `BaseController` é classe abstrata para herança
- `app/Models/` — models (Eloquent-like ou Query Builder)
- `app/Entities/` — entidades (data objects estendendo CodeIgniter\Entity)
- `app/Views/` — templates/views PHP
- `app/Database/Migrations/` — versionamento de schema
- `app/Database/Seeds/` — seed de dados
- `app/Filters/` — middlewares/filtros HTTP
- `app/Helpers/` — funções utilitárias
- `app/Libraries/` — classes reutilizáveis
- `app/Language/` — i18n (ex: `en/Validation.php` para mensagens)
- `app/ThirdParty/` — libs externas (não composer)
- `public/` — entrada web (`index.php` aqui, não na raiz)
- `tests/` — testes unitários e funcionais com PHPUnit
- `tests/_support/` — fixtures, mocks, models de teste
- `writable/` — diretório de escrita (cache, logs, uploads, session)
- `schema/` — scripts SQL de inicialização (para Docker)
- `docs/bruno/To-Do CRUD/` — coleção de testes de API em formato Bruno (Postman-like)

## Arquivos-chave
- `composer.json` — dependências: `codeigniter4/framework:^4.7`, dev: faker, vfsstream, phpunit
- `app/Config/Routes.php` — roteamento: GET `/` → TaskController::index; resource `tasks` define rotas (index, new, create, edit, update, delete) para TaskController
- `app/Config/Services.php` — DI container (carregamento de serviços)
- `app/Controllers/BaseController.php` — classe base para controllers; método `initController()` para setup
- `app/Controllers/TaskController.php` — ResourceController com JSON format. Métodos roteados: index (GET /tasks), create (POST /tasks → respondCreated com task), update (PUT/PATCH /tasks/{id}), delete (DELETE /tasks/{id}). Método show() implementado mas não roteado. `update()` tolera corpo vazio (retorna erro se ausente) e valida status in_list apenas se enviado. Responde com $this->respond*/failValidationErrors/failNotFound
- `app/Entities/Task.php` — entidade Task (extends CodeIgniter\Entity). Atributos iniciais: id (int), title, description, status ('pendente' default). Casts: id int, title/description/status string. Métodos: isPendente(), isEmAndamento(), isConcluida() suportam variantes PT/EN
- `app/Models/TaskModel.php` — modelo de Task (extends Model). Tabela: `tasks`, returnType Task::class. Validação: title obrigatório (max 255), status in_list [pendente, em andamento, concluída]. Timestamps automáticos (created_at, updated_at)
- `public/index.php` — entry point da aplicação web
- `spark` — CLI tool para migrations, seeds, server, etc.
- `.env.example` — template de variáveis de ambiente: CI_ENVIRONMENT, APP_BASE_URL; credenciais Postgres (DB_HOST=db, DB_NAME, DB_USER, DB_PASS, DB_PORT=5432); charset/collation utf8 para `database.default.*`
- `phpunit.dist.xml` — config de testes (PHPUnit 10.5.16)
- `app/Config/Database.php` — conexão DB (padrão: MySQLi, SQLite, Postgres)
- `collection.json` — coleção de requisições Postman/Bruno para API To-Do CRUD (Create, Get all, Update, Update status, Delete)
- `docs/bruno/To-Do CRUD/opencollection.yml` — manifesto da coleção Bruno (v1.0.0)
- `docs/bruno/To-Do CRUD/*.yml` — requisições Bruno (Create task, Delete task, Get all tasks, Update task, Update task status)

## Infraestrutura / Docker
- `docker-compose.yaml` — orquestração: service `postgres` (postgres:15-alpine, container_name: todo_postgres, restart: always), variáveis DB_NAME/DB_USER/DB_PASS/DB_PORT do .env, volume pgdata para persistência, init.sql via `/docker-entrypoint-initdb.d/`
- `schema/init.sql` — schema inicial: tabela `tasks` (id, title, description, status com CHECK, created_at, updated_at)

## Convenções observadas
- PSR-4 autoload: `App\` → `app/`, `Config\` → `app/Config/`
- Controllers estendem `BaseController` (que estende `CodeIgniter\Controller`) ou `ResourceController` (para REST/JSON)
- Métodos protegidos/privados em controllers; public apenas para actions
- Helpers carregados via `$this->helpers = [...]` em `BaseController::initController()`
- Views renderizadas com `view('path', $data)` (helper ou service)
- Entidades: Task.php em `App\Entities\` estende CodeIgniter\Entity; usado como returnType no modelo
- Modelos: TaskModel.php é o modelo principal; segue padrão `App\Models\NomeModel extends Model`; retorna entidades tipadas
- Migrações em `app/Database/Migrations/`; seeds em `app/Database/Seeds/`
- Testes em `tests/` espelhando estrutura: `tests/unit/`, `tests/database/`, etc.
- Sem Eloquent nativo — usa Query Builder do CI4
- `.htaccess` em `public/` e `app/` para segurança (não servir app direto)
- Diretório `writable/` deve ter permissão 755+ (cache, logs, uploads, session)
- Database: PostgreSQL (via Docker), configurado em `app/Config/Database.php` e `.env.example`
- ResourceController responde via métodos `$this->respond()`, `$this->respondCreated()`, `$this->respondUpdated()`, `$this->failValidationErrors()`, `$this->failNotFound()`
- Testes de API: coleção Postman (collection.json) e Bruno (docs/bruno/To-Do CRUD/) para validação manual/CI
