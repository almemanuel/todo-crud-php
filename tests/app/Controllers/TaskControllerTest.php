<?php

namespace App\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

class TaskControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $refresh = true;

    protected $namespace = 'App';

    protected function setUp(): void
    {
        parent::setUp();
        
        // Disable CSRF filter dynamically during Controller tests
        $filtersConfig = config('Filters');
        if (isset($filtersConfig->globals['before'])) {
            foreach ($filtersConfig->globals['before'] as $key => $value) {
                if ($value === 'csrf' || $key === 'csrf') {
                    unset($filtersConfig->globals['before'][$key]);
                }
            }
        }
    }

    // --------------------------------------------------------------------
    // 1. LISTAGEM (GET /tasks)
    // --------------------------------------------------------------------

    public function testGetAllTasksReturns200AndJson()
    {
        $result = $this->get('tasks');

        $result->assertOK();
        $result->assertJSON();
    }

    // --------------------------------------------------------------------
    // 2. CRIAÇÃO (POST /tasks)
    // --------------------------------------------------------------------

    public function testCreateTaskSuccess()
    {
        $payload = [
            'title' => 'Minha Tarefa de Teste',
            'description' => 'Descrição da tarefa de teste',
        ];

        $result = $this->post('tasks', $payload);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201]));
        $result->assertJSONFragment(['title' => 'Minha Tarefa de Teste']);
    }

    public function testCreateTaskFailsWhenTitleIsMissing()
    {
        $payload = [
            'description' => 'Tentando criar sem título'
        ];

        $result = $this->post('tasks', $payload);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [400, 422]));
    }

    // --------------------------------------------------------------------
    // 3. ATUALIZAÇÃO (PUT / PATCH /tasks/{id})
    // --------------------------------------------------------------------

    public function testUpdateTaskSuccess()
    {
        $model = new \App\Models\TaskModel();
        $taskId = $model->insert([
            'title' => 'Tarefa Original',
            'description' => 'Desc Original'
        ]);

        $data = [
            'title' => 'Tarefa Editada',
            'status' => 'em andamento'
        ];

        $result = $this->withHeaders([
            'Content-Type' => 'application/json'
        ])->withBody(json_encode($data))
        ->put("tasks/{$taskId}");

        $result->assertOK();
        $result->assertJSONFragment(['title' => 'Tarefa Editada']);
    }

    public function testUpdateTaskNotFoundReturns404()
    {
        $payload = [
            'title' => 'Inexistente'
        ];

        $result = $this->withHeaders([
            'Content-Type' => 'application/json'
        ])->withBody(json_encode($payload))
        ->put('tasks/999999');

        $result->assertStatus(404);
    }

    // --------------------------------------------------------------------
    // 4. EXCLUSÃO (DELETE /tasks/{id})
    // --------------------------------------------------------------------

    public function testDeleteTaskSuccess()
    {
        $model = new \App\Models\TaskModel();
        $id = $model->insert([
            'title' => 'Tarefa para deletar'
        ]);

        $result = $this->delete("tasks/{$id}");

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 204]));
    }

    public function testDeleteTaskNotFoundReturns404()
    {
        $result = $this->delete('tasks/999999');

        $result->assertStatus(404);
    }
}