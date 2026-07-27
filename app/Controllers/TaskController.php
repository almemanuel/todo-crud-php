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

    public function create() {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        $id = $this->model->insert($data);

        if (!$id) {
            return $this->failValidationErrors($this->model->errors());
        }

        $task = $this->model->find($id);

        return $this->respondCreated($task);
    }

    // PUT/PATCH /tasks/{id}
    public function update($id = null) {
        $task = $this->model->find($id);

        if (!$task) {
            return $this->failNotFound('Tarefa não encontrada.');
        }

        $data = $this->request->getJSON(true) 
            ?? $this->request->getRawInput() 
            ?? $this->request->getVar();

        if (empty($data) || !is_array($data)) {
            return $this->failValidationErrors(['error' => 'Nenhum dado fornecido para atualização.']);
        }

        if (isset($data['status'])) {
            $this->model->setValidationRule('status', 'in_list[pendente,em andamento,concluída]');
        }

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