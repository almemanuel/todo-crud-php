# To-Do CRUD Manager (CodeIgniter 4 + PostgreSQL + Docker)

Este é um gerenciador de tarefas estruturado sob algumas das melhores práticas de desenvolvimento. O projeto utiliza **CodeIgniter 4** no backend, **PostgreSQL** como banco de dados principal, **Docker** para orquestração de ambiente e **Vanilla JS + Bootstrap 5** para a interface do usuário.

---

## 🏗️ Diferenciais & Arquitetura

Para elevar a maturidade do projeto, foram implementados os seguintes padrões:
* **Service Layer (Desacoplamento):** Toda a lógica de negócio e regras de validação foram extraídas do controller para a classe `TaskService`, mantendo o controlador enxuto e focado apenas em gerenciar a requisição/resposta HTTP.
* **Integridade de Dados (ACID):** Operações de escrita e remoção no banco contam com gerenciamento explícito de transações (`transStart`, `transComplete` e `transRollback`) para garantir que os dados permaneçam íntegros.
* **CSRF Protection (Segurança):** A proteção de Cross-Site Request Forgery está ativada no framework. Ela foi configurada com exceções para os caminhos de API (`tasks/*`), permitindo que a API REST seja testada livremente de forma desacoplada via ferramentas externas, enquanto o frontend envia o token de forma dinâmica.
* **TDD & Cobertura de Testes:** Suíte de testes automatizados com **PHPUnit** cobrindo testes de integração funcional das rotas/controllers e testes unitários da camada de serviço utilizando banco SQLite `:memory:` isolado.

---

## 🚀 Como Rodar o Projeto

### Pré-requisitos
* **Docker** e **Docker Compose** instalados na máquina.

### Passo 1: Configurar Variáveis de Ambiente
Renomeie o arquivo `.env.example` na raiz do projeto para `.env`.
As credenciais padrão contidas nele já estão configuradas para funcionar imediatamente com o Docker Compose do projeto:
```env
DB_NAME=todo_db
DB_USER=todo_user
DB_PASS=todo_pass
```

### Passo 2: Construir e Subir os Contêineres
Na raiz do projeto, execute o comando para buildar a imagem e iniciar os serviços em segundo plano:
```bash
docker compose up -d --build
```
> [!NOTE]
> O contêiner de banco de dados PostgreSQL iniciará primeiro. O contêiner PHP esperará o banco de dados ficar saudável (`healthy`) para então subir, rodar as migrations automaticamente através do `entrypoint.sh` e iniciar o servidor Apache.

### Passo 3: Acessar a Aplicação
Acesse no seu navegador:
* **Interface Web:** [http://localhost:8080](http://localhost:8080)

---

## 🔌 Rotas da API REST

A API de tarefas foi estruturada utilizando rotas do tipo `resource` do CodeIgniter 4, gerando os seguintes endpoints RESTful clássicos:

| Método | Endpoint | Ação | Descrição |
| :--- | :--- | :--- | :--- |
| **GET** | `/tasks` | `index` | Retorna a lista de todas as tarefas |
| **POST** | `/tasks` | `create` | Cria uma nova tarefa. Payload JSON: `{"title": "...", "description": "...", "status": "..."}` |
| **PUT** | `/tasks/{id}` | `update` | Atualiza uma tarefa por completo |
| **DELETE** | `/tasks/{id}` | `delete` | Remove uma tarefa do banco de dados |


---

## 📂 Testando via Bruno / Postman

A pasta `docs/bruno/To-Do CRUD/` contém uma coleção de requisições prontas no formato **Bruno** (alternativa leve e opensource ao Postman).
Como configuramos o bypass do CSRF para as rotas da API no [Filters.php](file:///c:/Users/samir/codes/todo-crud-php/app/Config/Filters.php), você pode executar as requisições de criação, atualização e exclusão na ferramenta diretamente sem a necessidade de configurar tokens de sessão adicionais.

Também há um arquivo que você pode importar para o seu Postman: `collection.json`
