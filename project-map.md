# Mapa do Projeto
Atualizado em: 2026-07-26
Stack: PHP 8.2+, CodeIgniter 4.7, PostgreSQL 15

## Estrutura de pastas
- `app/` — raiz da aplicação (PSR-4 namespace `App\`)
- `app/Config/` — configurações do framework (database, routes, services, etc.)
- `app/Controllers/` — controllers MVC. `BaseController` é classe abstrata para herança
- `app/Models/` — models (Eloquent-like ou Query Builder)
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

## Arquivos-chave
- `composer.json` — dependências: `codeigniter4/framework:^4.7`, dev: faker, vfsstream, phpunit
- `app/Config/Routes.php` — roteamento (ex: `$routes->get('/', 'Home::index')`)
- `app/Config/Services.php` — DI container (carregamento de serviços)
- `app/Controllers/BaseController.php` — classe base para controllers; método `initController()` para setup
- `app/Controllers/Home.php` — controller de exemplo (GET `/` → `Home::index`)
- `public/index.php` — entry point da aplicação web
- `spark` — CLI tool para migrations, seeds, server, etc.
- `env` — template de `.env` (copiar e configurar baseURL, database, etc.)
- `.env.example` — template de variáveis de ambiente (incluindo credenciais Postgres)
- `phpunit.dist.xml` — config de testes (PHPUnit 10.5.16)
- `app/Config/Database.php` — conexão DB (padrão: MySQLi, SQLite, Postgres)

## Infraestrutura / Docker
- `docker-compose.yaml` — orquestração: Postgres 15 Alpine, volume `pgdata` para persistência, carrega `schema/init.sql` no startup
- `schema/init.sql` — schema inicial: tabela `tasks` (id, title, description, status com CHECK, created_at, updated_at)

## Convenções observadas
- PSR-4 autoload: `App\` → `app/`, `Config\` → `app/Config/`
- Controllers estendem `BaseController` (que estende `CodeIgniter\Controller`)
- Métodos protegidos/privados em controllers; public apenas para actions
- Helpers carregados via `$this->helpers = [...]` em `BaseController::initController()`
- Views renderizadas com `view('path', $data)` (helper ou service)
- Modelos: não há exemplo ainda, mas segue padrão `App\Models\NomeModel extends Model`
- Migrações em `app/Database/Migrations/`; seeds em `app/Database/Seeds/`
- Testes em `tests/` espelhando estrutura: `tests/unit/`, `tests/database/`, etc.
- Sem Eloquent nativo — usa Query Builder do CI4
- `.htaccess` em `public/` e `app/` para segurança (não servir app direto)
- Diretório `writable/` deve ter permissão 755+ (cache, logs, uploads, session)
- Database: PostgreSQL (via Docker), configurado em `app/Config/Database.php` e `.env.example`
