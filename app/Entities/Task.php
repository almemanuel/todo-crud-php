<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Task extends Entity
{
    protected $datamap = [];

    protected $casts = [
        'id' => 'int',
        'title' => 'string',
        'description' => 'string',
        'status' => 'string'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public function isPendente(): bool
    {
        return $this->status === 'pendente';
    }

    public function isEmAndamento(): bool
    {
        return $this->status === 'em andamento';
    }

    public function isConcluida(): bool
    {
        return $this->status === 'concluída';
    }
}
