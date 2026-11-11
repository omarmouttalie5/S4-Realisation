<?php

require_once __DIR__ . "/Product.php";

// NEW: Data Access Object (DAO) layer
// This file is responsible only for reading and writing product data from JSON.
class ProductDAO {
    private string $filePath;

    public function __construct(string $filePath = "products.json") {
        $this->filePath = __DIR__ . DIRECTORY_SEPARATOR . basename($filePath);

        if (!file_exists($this->filePath)) {
            file_put_contents($this->filePath, json_encode([], JSON_PRETTY_PRINT));
        }
    }

    public function getAll(): array {
        $content = @file_get_contents($this->filePath);
        $data = json_decode($content, true);

        return is_array($data) ? $data : [];
    }

    public function add(Product $product): array {
        $items = $this->getAll();
        $nextId = count($items) ? max(array_column($items, 'id')) + 1 : 1;

        $item = [
            'id' => $nextId,
            'name' => trim($product->name),
            'price' => trim($product->price),
            'size' => trim($product->size)
        ];

        $items[] = $item;
        file_put_contents(
            $this->filePath,
            json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        return $item;
    }
}
