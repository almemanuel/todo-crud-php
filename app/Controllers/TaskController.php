<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Services\TaskService;
use Exception;

class TaskController extends ResourceController
{
    protected $format = 'json';
    protected TaskService $taskService;

    public function __construct()
    {
        $this->taskService = new TaskService();
    }

    // GET /tasks
    public function index()
    {
        $tasks = $this->taskService->getAllTasks();
        return $this->respond($tasks);
    }

    // GET /tasks/{id}
    public function show($id = null)
    {
        $task = $this->taskService->getTaskById((int)$id);
        
        if (!$task) {
            return $this->failNotFound('Tarefa não encontrada.');
        }

        return $this->respond($task);
    }

    // POST /tasks
    public function create()
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        try {
            $task = $this->taskService->createTask($data);
            return $this->respondCreated($task);
        } catch (Exception $e) {
            return $this->failValidationErrors(['error' => $e->getMessage()]);
        }
    }

    // PUT/PATCH /tasks/{id}
    public function update($id = null)
    {
        $data = $this->request->getJSON(true) 
            ?? $this->request->getRawInput() 
            ?? $this->request->getVar();

        if (empty($data) || !is_array($data)) {
            return $this->failValidationErrors(['error' => 'Nenhum dado fornecido para atualização.']);
        }

        try {
            $task = $this->taskService->updateTask((int)$id, $data);
            return $this->respondUpdated($task, 'Tarefa atualizada com sucesso.');
        } catch (Exception $e) {
            if ($e->getMessage() === 'Task not found.') {
                return $this->failNotFound('Tarefa não encontrada.');
            }
            return $this->failValidationErrors(['error' => $e->getMessage()]);
        }
    }

    // DELETE /tasks/{id}
    public function delete($id = null)
    {
        try {
            $this->taskService->deleteTask((int)$id);
            return $this->respondDeleted(['id' => $id], 'Tarefa deletada com sucesso.');
        } catch (Exception $e) {
            if ($e->getMessage() === 'Task not found.') {
                return $this->failNotFound('Tarefa não encontrada.');
            }
            return $this->failValidationErrors(['error' => $e->getMessage()]);
        }
    }
}