<?php

namespace App\Controllers;

use App\Models\TaskModel;
use CodeIgniter\RESTful\ResourceController;

class TaskController extends ResourceController {
    protected $modelName = TaskModel::class;
    protected $format = 'json';

    // GET /tasks
    public function index() {
        $tasks = $this->model->findAll();
        
        return $this->respond($tasks);
    }

    // GET /tasks/{id}
    public function show($id = null) {
        $task = $this->model->find($id);
        
        if (!$task) {
            return $this->failNotFound('Tarefa no encontrada.');
        }

        return $this->respond($task);
    }

    // POST /tasks
    public function create() {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        if (!$this->model->insert($data)) {
            return $this->failValidationErrors($this->model->errors());
        }

        $taskId = $this->model->getInsertID();
        $newTask = $this->model->find($taskId);

        return $this->respondCreated($task, 'Tarefa criada com sucesso.');
    }

    // PUT/PATCH /tasks/{id}
    public function update($id = null) {
        $task = $this->model->find($id);

        if (!$task) {
            return $this->failNotFound('Tarefa não encontrada.');
        }

        $data = $this->request->getJSON(true) ?? $this->request->getRawInput();

        $this->model->setValidationRule('status', 'required|in_list[pendente,em andamento,concluída]');

        if (!$this->model->validate($data)) {
            return $this->failValidationErrors($this->model->errors());
        }

        $this->model->update($id, $data);

        return $this->respondUpdated($this->model->find($id), 'Tarefa atualizada com sucesso.');
    }

    // DELETE /tasks/{id}
    public function delete($id = null) {
        $task = $this->model->find($id);

        if (!$task) {
            return $this->failNotFound('Tarefa no encontrada.');
        }

        $this->model->delete($id);
        
        return $this->respondDeleted(['id' => $id], 'Tarefa deletada com sucesso.');
    }
}