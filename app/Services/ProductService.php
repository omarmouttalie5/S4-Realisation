<?php

require_once __DIR__ . "/BaseService.php";

class ProductService extends BaseService {
    private const ALLOWED_SIZES = ['XS', 'S', 'M', 'L', 'XL'];

    public function getAll(): array {
        return $this->dao->getAll();
    }

    public function getById(int $id): ?array {
        return $this->dao->getById($this->validateId($id));
    }

    public function add(string $name, string $price, string $size): array {
        $product = $this->makeProduct(0, $name, $price, $size);
        return $this->dao->add($product);
    }

    public function update(int $id, string $name, string $price, string $size): ?array {
        $id = $this->validateId($id);
        $product = $this->makeProduct($id, $name, $price, $size);
        return $this->dao->update($product);
    }

    public function delete(int $id): bool {
        return $this->dao->delete($this->validateId($id));
    }

    private function makeProduct(int $id, string $name, string $price, string $size): Product {
        $name = $this->validateText($name, 'name');
        $price = $this->validatePrice($price);
        $size = strtoupper(trim($size));

        if (!in_array($size, self::ALLOWED_SIZES, true)) {
            throw new InvalidArgumentException('Size must be one of: XS, S, M, L, XL.');
        }

        return new Product($id, $name, $price, $size);
    }
}
