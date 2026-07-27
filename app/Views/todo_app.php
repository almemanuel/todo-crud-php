<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSRF Protection Meta Tags -->
    <meta name="csrf-header" content="<?= csrf_header() ?>">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <title>Gerenciador de Tarefas</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-dark text-light min-vh-100 d-flex align-items-center justify-content-center p-3">

    <div class="card bg-secondary text-white shadow-lg border-0 w-100" style="max-width: 650px;">
        <div class="card-body p-4">
            <h1 class="h4 text-center fw-bold mb-4 text-primary-emphasis">Minhas Tarefas</h1>

            <!-- Form de Criação -->
            <form id="task-form" class="mb-4">
                <div class="mb-3">
                    <label for="task-title" class="form-label text-sm fw-semibold">Nome da Tarefa</label>
                    <input type="text" id="task-title" class="form-control bg-dark text-white border-secondary" 
                        placeholder="Ex: Refatorar Controller" required>
                </div>

                <div class="mb-3">
                    <label for="task-description" class="form-label text-sm fw-semibold">Descrição</label>
                    <textarea id="task-description" class="form-control bg-dark text-white border-secondary" 
                        placeholder="Detalhes sobre a tarefa (opcional)..." rows="2"></textarea>
                </div>

                <div class="mb-3">
                    <label for="task-status" class="form-label text-sm fw-semibold">Status Inicial</label>
                    <select id="task-status" class="form-select bg-dark text-white border-secondary">
                        <option value="pendente" selected>Pendente</option>
                        <option value="em andamento">Em Andamento</option>
                        <option value="concluída">Concluída</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary fw-semibold w-100">
                    <i class="bi bi-plus-lg"></i> Adicionar Tarefa
                </button>
            </form>

            <hr class="border-secondary my-4">

            <!-- Lista de Tarefas -->
            <ul id="task-list" class="list-group list-group-flush rounded overflow-auto" style="max-height: 380px;">
                <!-- Renderizado via JS -->
            </ul>
        </div>
    </div>

    <!-- Modal de Edição -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-secondary text-white border-0 shadow">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">Editar Tarefa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit-task-id">
                    
                    <div class="mb-3">
                        <label for="edit-task-title" class="form-label text-sm fw-semibold">Nome da Tarefa</label>
                        <input type="text" id="edit-task-title" class="form-control bg-dark text-white border-secondary" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit-task-description" class="form-label text-sm fw-semibold">Descrição</label>
                        <textarea id="edit-task-description" class="form-control bg-dark text-white border-secondary" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="edit-task-status" class="form-label text-sm fw-semibold">Status</label>
                        <select id="edit-task-status" class="form-select bg-dark text-white border-secondary">
                            <option value="pendente">Pendente</option>
                            <option value="em andamento">Em Andamento</option>
                            <option value="concluída">Concluída</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" onclick="saveTaskEdit()" class="btn btn-primary btn-sm">Salvar Alterações</button>
                </div>
            </div>
        </div>
    </div>

    <!-- IMPORTANTE: O JS do Bootstrap DEVE vir ANTES do nosso script customizado -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const API_URL = '/tasks';
        let editModalInstance = null;
        let tasks = []; // Cache to store tasks in memory and avoid inline string issues

        // Helper function to get the current CSRF token hash from the meta tag
        function getCsrfToken() {
            const metaToken = document.querySelector('meta[name="csrf-token"]');
            return metaToken ? metaToken.getAttribute('content') : '';
        }

        // Helper function to get the CSRF header name
        function getCsrfHeader() {
            const metaHeader = document.querySelector('meta[name="csrf-header"]');
            return metaHeader ? metaHeader.getAttribute('content') : 'X-CSRF-TOKEN';
        }

        // Aguarda todo o HTML carregar antes de manipular o DOM
        document.addEventListener('DOMContentLoaded', () => {
            // Inicializa o modal com segurança
            const modalEl = document.getElementById('editModal');
            if (modalEl && typeof bootstrap !== 'undefined') {
                editModalInstance = new bootstrap.Modal(modalEl);
            }

            // Atrela o submit do form com segurança
            const form = document.getElementById('task-form');
            if (form) {
                form.addEventListener('submit', handleCreateTask);
            }

            fetchTasks();
        });

        const statusBadges = {
            'pendente': '<span class="badge bg-warning text-dark">Pendente</span>',
            'em andamento': '<span class="badge bg-info text-dark">Em Andamento</span>',
            'concluída': '<span class="badge bg-success">Concluída</span>'
        };

        // 1. READ (Listar todas)
        async function fetchTasks() {
            const list = document.getElementById('task-list');
            if (!list) return;

            list.innerHTML = `<li class="list-group-item bg-dark text-muted text-center py-3 border-secondary">Carregando...</li>`;

            try {
                const res = await fetch(API_URL);
                const responseData = await res.json();
                
                // Trata o retorno se for { data: [...] } ou direto [...]
                let fetchedTasks = responseData;
                if (responseData && responseData.data && Array.isArray(responseData.data)) {
                    fetchedTasks = responseData.data;
                } else if (responseData && responseData.tasks && Array.isArray(responseData.tasks)) {
                    fetchedTasks = responseData.tasks;
                }

                // Armazena em cache na memória global
                tasks = Array.isArray(fetchedTasks) ? fetchedTasks : [];

                list.innerHTML = '';

                if (tasks.length === 0) {
                    list.innerHTML = `<li class="list-group-item bg-dark text-muted text-center py-3 border-secondary">Nenhuma tarefa encontrada.</li>`;
                    return;
                }

                tasks.forEach(task => {
                    const li = document.createElement('li');
                    li.className = 'list-group-item bg-dark text-white border-secondary py-3';
                    
                    const badge = statusBadges[task.status] || '<span class="badge bg-secondary">Desconhecido</span>';
                    const descriptionHtml = task.description 
                        ? `<p class="mb-0 text-muted small mt-1">${escapeHtml(task.description)}</p>` 
                        : '';

                    // O botão Editar agora passa apenas o ID da tarefa para o modal
                    li.innerHTML = `
                        <div class="d-flex align-items-start justify-content-between gap-2">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2">
                                    <strong class="${task.status === 'concluída' ? 'text-decoration-line-through text-muted' : ''}">
                                        ${escapeHtml(task.title)}
                                    </strong>
                                    ${badge}
                                </div>
                                ${descriptionHtml}
                            </div>
                            <div class="d-flex gap-1">
                                <button onclick="openEditModal(${task.id})" 
                                    class="btn btn-outline-warning btn-sm border-0" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button onclick="deleteTask(${task.id})" class="btn btn-outline-danger btn-sm border-0" title="Excluir">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
                    list.appendChild(li);
                });
            } catch (err) {
                console.error('Erro na API:', err);
                list.innerHTML = `<li class="list-group-item bg-dark text-danger text-center py-3 border-secondary">Erro ao carregar tarefas.</li>`;
            }
        }

        // 2. CREATE
        async function handleCreateTask(e) {
            e.preventDefault();
            const titleInput = document.getElementById('task-title');
            const descInput = document.getElementById('task-description');
            const statusInput = document.getElementById('task-status');

            const title = titleInput.value.trim();
            const description = descInput.value.trim();
            const status = statusInput.value;

            if (!title) return;

            try {
                const res = await fetch(API_URL, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        [getCsrfHeader()]: getCsrfToken()
                    },
                    body: JSON.stringify({ title, description, status })
                });

                if (!res.ok) {
                    const errData = await res.json();
                    const messages = errData.messages || errData.errors || { error: 'Erro ao criar tarefa.' };
                    alert('Falha ao criar tarefa: ' + Object.values(messages).join(', '));
                    return;
                }

                titleInput.value = '';
                descInput.value = '';
                statusInput.value = 'pendente';
                fetchTasks();
            } catch (err) {
                console.error('Erro na criação:', err);
                alert('Erro de conexão ao criar tarefa.');
            }
        }

        // 3. UPDATE
        function openEditModal(id) {
            const task = tasks.find(t => t.id === id);
            if (!task) {
                alert('Tarefa não encontrada em cache.');
                return;
            }

            document.getElementById('edit-task-id').value = task.id;
            document.getElementById('edit-task-title').value = task.title;
            document.getElementById('edit-task-description').value = task.description || '';
            document.getElementById('edit-task-status').value = task.status;
            
            if (editModalInstance) {
                editModalInstance.show();
            }
        }

        async function saveTaskEdit() {
            const id = document.getElementById('edit-task-id').value;
            const title = document.getElementById('edit-task-title').value.trim();
            const description = document.getElementById('edit-task-description').value.trim();
            const status = document.getElementById('edit-task-status').value;

            if (!title) return;

            try {
                const res = await fetch(`${API_URL}/${id}`, {
                    method: 'PUT',
                    headers: { 
                        'Content-Type': 'application/json',
                        [getCsrfHeader()]: getCsrfToken()
                    },
                    body: JSON.stringify({ title, description, status })
                });

                if (!res.ok) {
                    const errData = await res.json();
                    const messages = errData.messages || errData.errors || { error: 'Erro ao editar tarefa.' };
                    alert('Falha ao salvar alterações: ' + Object.values(messages).join(', '));
                    return;
                }

                if (editModalInstance) {
                    editModalInstance.hide();
                }
                fetchTasks();
            } catch (err) {
                console.error('Erro na edição:', err);
                alert('Erro de conexão ao salvar alterações.');
            }
        }

        // 4. DELETE
        async function deleteTask(id) {
            if (!confirm('Deseja realmente excluir esta tarefa?')) return;
            try {
                const res = await fetch(`${API_URL}/${id}`, { 
                    method: 'DELETE',
                    headers: {
                        [getCsrfHeader()]: getCsrfToken()
                    }
                });

                if (!res.ok) {
                    const errData = await res.json();
                    const messages = errData.messages || errData.errors || { error: 'Erro ao excluir tarefa.' };
                    alert('Falha ao excluir tarefa: ' + Object.values(messages).join(', '));
                    return;
                }

                fetchTasks();
            } catch (err) {
                console.error('Erro na exclusão:', err);
                alert('Erro de conexão ao excluir tarefa.');
            }
        }

        function escapeHtml(text) {
            return text.replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m]));
        }

        function escapeJs(text) {
            return text.replace(/'/g, "\\'").replace(/"/g, '&quot;');
        }
    </script>
</body>
</html>