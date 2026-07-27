# Mapa do Projeto
Atualizado em: 2026-07-26
Stack: PHP 8.3, CodeIgniter 4.7, PostgreSQL 16 (Docker)

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
- `.env.example` — template de variáveis de ambiente: CI_ENVIRONMENT, APP_BASE_URL; credenciais Postgres (DB_HOST=postgres-db, DB_NAME, DB_USER, DB_PASS, DB_PORT=5432); charset/collation utf8 para `database.default.*`
- `phpunit.dist.xml` — config de testes (PHPUnit 10.5.16)
- `app/Config/Database.php` — conexão DB (padrão: MySQLi, SQLite, Postgres)
- `collection.json` — coleção de requisições Postman para API To-Do CRUD. URLs: `http://localhost:8080/tasks`. Requisições: POST (Create), GET (Get all), PUT (Update task), PATCH (Update status), DELETE
- `docs/bruno/To-Do CRUD/opencollection.yml` — manifesto da coleção Bruno (v1.0.0, proxy config, ignore node_modules/.git)
- `docs/bruno/To-Do CRUD/Create task.yml` — POST http://localhost:8080/tasks, body: {"title": "Comprar café"}
- `docs/bruno/To-Do CRUD/Get all tasks.yml` — GET http://localhost:8080/tasks
- `docs/bruno/To-Do CRUD/Update task.yml` — PUT http://localhost:8080/tasks/1, body: {"description": "blablabla", "status": "em andamento"}
- `docs/bruno/To-Do CRUD/Update task status.yml` — PATCH http://localhost:8080/tasks/1, body: {"status": "concluída"}
- `docs/bruno/To-Do CRUD/Delete task.yml` — DELETE http://localhost:8080/tasks/1

## Infraestrutura / Docker
- `Dockerfile` — imagem PHP 8.3-apache. Instala: libpq-dev, libicu-dev, zip, unzip, git; extensões: pdo, pdo_pgsql, pgsql, intl, opcache. Habilita mod_rewrite. DocumentRoot: `/var/www/html/public`. Copia composer binary. WORKDIR: `/var/www/html`. Executa entrypoint.sh. EXPOSE 80
- `docker-compose.yaml` — orquestração com dois services: (1) `app` (build local, porta 8080, env_file .env, DB_HOST/hostname=postgres-db, depends_on postgres-db com healthcheck); (2) `postgres-db` (postgres:16-alpine, porta 5432, healthcheck via pg_isready, volume pgdata, env: POSTGRES_DB/USER/PASSWORD). Rede: `todo-network` (bridge). Volume: `pgdata`
- `entrypoint.sh` — script que roda `php spark migrate --all` e depois `apache2-foreground`
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
- Database: PostgreSQL 16 (via Docker), configurado em `app/Config/Database.php` e `.env.example`
- ResourceController responde via métodos `$this->respond()`, `$this->respondCreated()`, `$this->respondUpdated()`, `$this->failValidationErrors()`, `$this->failNotFound()`
- Testes de API: coleção Postman (collection.json) e Bruno (docs/bruno/To-Do CRUD/) apontam para localhost:8080 (Docker)
- Docker: migrations rodadas automaticamente no entrypoint antes do Apache iniciar
