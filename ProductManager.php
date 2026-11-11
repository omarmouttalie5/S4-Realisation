<?php

require_once "Product.php";
require_once "ProductDAO.php";

// NEW: Business Logic layer (3-tier architecture)
// This layer validates and prepares the data before it reaches the DAO.
class ProductManager {
    private ProductDAO $dao;

    public function __construct() {
        $this->dao = new ProductDAO();
    }

    public function getAll(): array {
        return $this->dao->getAll();
    }

    public function add(string $name, string $price, string $size): array {
        $cleanName = $this->sanitizeText($name, 'name');
        $cleanPrice = $this->sanitizePrice($price);
        $cleanSize = $this->sanitizeSize($size);

        $product = new Product(0, $cleanName, $cleanPrice, $cleanSize);

        return $this->dao->add($product);
    }

    private function sanitizeText(string $value, string $field): string {
        $value = trim($value);

        if ($value === '') {
            throw new InvalidArgumentException("The {$field} field is required.");
        }

        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    private function sanitizePrice(string $value): string {
        $value = trim($value);

        if ($value === '' || !is_numeric($value)) {
            throw new InvalidArgumentException('Price must be a valid number.');
        }

        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    private function sanitizeSize(string $value): string {
        $value = strtoupper(trim($value));
        $allowedSizes = ['XS', 'S', 'M', 'L', 'XL'];

        if (!in_array($value, $allowedSizes, true)) {
            throw new InvalidArgumentException('Size must be one of: XS, S, M, L, XL.');
        }

        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}