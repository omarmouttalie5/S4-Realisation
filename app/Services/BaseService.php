<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "DAO" . DIRECTORY_SEPARATOR . "ProductDAO.php";

abstract class BaseService {
    protected ProductDAO $dao;

    public function __construct(?ProductDAO $dao = null) {
        $this->dao = $dao ?? new ProductDAO();
    }

    protected function validateText(string $value, string $field): string {
        $value = trim($value);
        if ($value === '') {
            throw new InvalidArgumentException("The {$field} field is required.");
        }

        return $value;
    }

    protected function validatePrice(string $value): string {
        $value = trim($value);
        if ($value === '' || !is_numeric($value) || !is_finite((float) $value) || (float) $value < 0) {
            throw new InvalidArgumentException('Price must be a valid, non-negative number.');
        }

        return $value;
    }

    protected function validateId(int $id): int {
        if ($id < 1) {
            throw new InvalidArgumentException('Product ID must be a positive number.');
        }

        return $id;
    }
}
