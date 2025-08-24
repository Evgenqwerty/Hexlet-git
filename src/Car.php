<?php

namespace App;

class Car
{
    private ?int $id = null;
    private string $make;
    private string $model;

    public function __construct(string $make, string $model, ?int $id = null)
    {
        $this->make = $make;
        $this->model = $model;
        $this->id = $id;
    }

    public static function fromArray(array $data): Car
    {
        return new Car($data['make'], $data['model'], $data['id'] ?? null);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getMake(): string
    {
        return $this->make;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function exists(): bool
    {
        return $this->id !== null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'make' => $this->make,
            'model' => $this->model
        ];
    }
}
