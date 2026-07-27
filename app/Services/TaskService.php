<?php

namespace App\Services;

use App\Models\TaskModel;
use App\Entities\Task;
use Config\Database;
use RuntimeException;

class TaskService
{
    protected TaskModel $taskModel;

    public function __construct()
    {
        $this->taskModel = new TaskModel();
    }

    /**
     * Retrieve all tasks.
     *
     * @return array
     */
    public function getAllTasks(): array
    {
        return $this->taskModel->findAll();
    }

    /**
     * Retrieve a single task by ID.
     *
     * @param int $id
     * @return Task|null
     */
    public function getTaskById(int $id): ?Task
    {
        return $this->taskModel->find($id);
    }

    /**
     * Create a new task.
     *
     * @param array $data
     * @return Task
     * @throws RuntimeException
     */
    public function createTask(array $data): Task
    {
        $db = Database::connect();
        
        $db->transStart();

        $id = $this->taskModel->insert($data);

        if (!$id) {
            $db->transRollback();
            $errors = $this->taskModel->errors();
            throw new RuntimeException(implode(', ', $errors));
        }

        $task = $this->taskModel->find($id);

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new RuntimeException('Failed to complete transaction when creating the task.');
        }

        return $task;
    }

    /**
     * Update an existing task.
     *
     * @param int $id
     * @param array $data
     * @return Task
     * @throws RuntimeException
     */
    public function updateTask(int $id, array $data): Task
    {
        $task = $this->taskModel->find($id);
        if (!$task) {
            throw new RuntimeException('Task not found.');
        }

        $db = Database::connect();
        $db->transStart();

        if (isset($data['status'])) {
            $this->taskModel->setValidationRule('status', ['required', 'in_list[pendente,em andamento,concluída]']);
        }

        if (!$this->taskModel->validate($data)) {
            $db->transRollback();
            $errors = $this->taskModel->errors();
            throw new RuntimeException(implode(', ', $errors));
        }

        $this->taskModel->update($id, $data);

        $updatedTask = $this->taskModel->find($id);

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new RuntimeException('Failed to complete transaction when updating the task.');
        }

        return $updatedTask;
    }

    /**
     * Delete a task.
     *
     * @param int $id
     * @return bool
     * @throws RuntimeException
     */
    public function deleteTask(int $id): bool
    {
        $task = $this->taskModel->find($id);
        if (!$task) {
            throw new RuntimeException('Task not found.');
        }

        $db = Database::connect();
        $db->transStart();

        $this->taskModel->delete($id);

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new RuntimeException('Failed to complete transaction when deleting the task.');
        }

        return true;
    }
}
