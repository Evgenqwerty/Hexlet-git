<?php

namespace App;

class CarValidator
{
    public function validate(array $carData): array
    {
        $errors = [];

        if (empty($carData['make'])) {
            $errors['make'] = 'Make is required';
        }

        if (empty($carData['model'])) {
            $errors['model'] = 'Model is required';
        }

        return $errors;
    }
}
