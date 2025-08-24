<?php

namespace App;

class CarRepository
{
    private \PDO $conn;

    public function __construct(\PDO $conn)
    {
        $this->conn = $conn;
    }

    public function getEntities(): array
    {
        $cars = [];
        $sql = "SELECT * FROM cars";
        $stmt = $this->conn->query($sql);

        while ($row = $stmt->fetch()) {
            $car = new Car($row['make'], $row['model'], $row['id']);
            $cars[] = $car;
        }

        return $cars;
    }

    public function find(int $id): ?Car
    {
        $sql = "SELECT * FROM cars WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        
        if ($row = $stmt->fetch()) {
            return new Car($row['make'], $row['model'], $row['id']);
        }

        return null;
    }

    public function save(Car $car): void
    {
        if ($car->exists()) {
            $this->update($car);
        } else {
            $this->create($car);
        }
    }

    private function update(Car $car): void
    {
        $sql = "UPDATE cars SET make = :make, model = :model WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':make' => $car->getMake(),
            ':model' => $car->getModel(),
            ':id' => $car->getId()
        ]);
    }

    private function create(Car $car): void
    {
        $sql = "INSERT INTO cars (make, model) VALUES (:make, :model)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':make' => $car->getMake(),
            ':model' => $car->getModel()
        ]);
        $car->setId((int)$this->conn->lastInsertId());
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM cars WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        return $stmt->rowCount() > 0;
    }
}
