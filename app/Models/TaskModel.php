<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\Task;

class TaskModel extends Model
{
    protected $table = 'tasks';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    
    protected $returnType = Task::class;
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'title',
        'description',
        'status'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'title' => 'required|max_length[255]',
        'description' => 'permit_empty',
        'status' => 'permit_empty|in_list[pendente,em andamento,concluída]'
    ];

    protected $validationMessages = [
        'title' => [
            'required' => 'O título é obrigatório',
            'max_length' => 'O título não pode exceder 255 caracteres'
        ],
        'status' => [
            'in_list' => 'O status deve ser: pendente, em andamento ou concluída'
        ]
    ];

    protected $skipValidation       = false;

}
