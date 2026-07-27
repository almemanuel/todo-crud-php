<?php

namespace App\Services;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Services\TaskService;
use RuntimeException;

class TaskServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $refresh = true;

    protected $namespace = 'App';

    protected TaskService $taskService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->taskService = new TaskService();
    }

    public function testGetAllTasksReturnsArray()
    {
        $tasks = $this->taskService->getAllTasks();
        $this->assertIsArray($tasks);
    }

    public function testCreateTaskSuccessfully()
    {
        $data = [
            'title'       => 'Service Test Task',
            'description' => 'Tested via Service unit tests'
        ];

        $task = $this->taskService->createTask($data);

        $this->assertNotNull($task->id);
        $this->assertEquals('Service Test Task', $task->title);
        $this->assertEquals('pendente', $task->status);
    }

    public function testCreateTaskThrowsExceptionWhenTitleIsMissing()
    {
        $data = [
            'description' => 'Missing title'
        ];

        $this->expectException(RuntimeException::class);
        $this->taskService->createTask($data);
    }

    public function testUpdateTaskSuccessfully()
    {
        $data = [
            'title' => 'Original Task'
        ];
        $task = $this->taskService->createTask($data);

        $updateData = [
            'title'  => 'Updated Task Title',
            'status' => 'em andamento'
        ];

        $updatedTask = $this->taskService->updateTask($task->id, $updateData);

        $this->assertEquals('Updated Task Title', $updatedTask->title);
        $this->assertEquals('em andamento', $updatedTask->status);
    }

    public function testUpdateTaskThrowsExceptionWhenNotFound()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Task not found.');

        $this->taskService->updateTask(999999, ['title' => 'Oops']);
    }

    public function testDeleteTaskSuccessfully()
    {
        $task = $this->taskService->createTask(['title' => 'Task to delete']);

        $result = $this->taskService->deleteTask($task->id);

        $this->assertTrue($result);
        $this->assertNull($this->taskService->getTaskById($task->id));
    }

    public function testDeleteTaskThrowsExceptionWhenNotFound()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Task not found.');

        $this->taskService->deleteTask(999999);
    }
}
