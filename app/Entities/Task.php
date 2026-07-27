<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Task extends Entity
{
    protected $attributes = [
        'id'          => null,
        'title'       => null,
        'description' => null,
        'status'      => 'pendente',
        'created_at'  => null,
        'updated_at'  => null,
    ];

    protected $datamap = [];

    protected $casts = [
        'id' => 'int',
        'title' => 'string',
        'description' => '?string',
        'status' => 'string'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public function isPendente(): bool
    {
        return $this->attributes['status'] === 'pending' || $this->attributes['status'] === 'pendente';
    }

    public function isEmAndamento(): bool
    {
        return $this->attributes['status'] === 'in_progress' || $this->attributes['status'] === 'em_andamento';
    }

    public function isConcluida(): bool
    {
        return $this->attributes['status'] === 'completed' || $this->attributes['status'] === 'concluida';
    }
}
