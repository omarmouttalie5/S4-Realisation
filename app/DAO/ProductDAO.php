<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "Models" . DIRECTORY_SEPARATOR . "Product.php";

class ProductDAO {
    private string $filePath;

    public function __construct(string $filePath = "products.json") {
        $this->filePath = dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR . "data"
            . DIRECTORY_SEPARATOR . basename($filePath);

        $handle = fopen($this->filePath, 'c+');
        if ($handle === false) {
            throw new RuntimeException('Unable to open the product data file.');
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                throw new RuntimeException('Unable to lock the product data file.');
            }

            if (filesize($this->filePath) === 0) {
                fwrite($handle, "[]\n");
                fflush($handle);
            }
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    public function getAll(): array {
        return $this->readItems();
    }

    public function getById(int $id): ?array {
        foreach ($this->readItems() as $item) {
            if ((int) ($item['id'] ?? 0) === $id) {
                return $item;
            }
        }

        return null;
    }

    public function add(Product $product): array {
        return $this->modify(function (array &$items) use ($product): array {
            $ids = array_map(static function (array $item): int {
                return (int) ($item['id'] ?? 0);
            }, $items);
            $item = $this->productToArray($product, $ids ? max($ids) + 1 : 1);
            $items[] = $item;

            return $item;
        });
    }

    public function update(Product $product): ?array {
        return $this->modify(function (array &$items) use ($product): ?array {
            foreach ($items as $index => $item) {
                if ((int) ($item['id'] ?? 0) === $product->id) {
                    $items[$index] = $this->productToArray($product, $product->id);
                    return $items[$index];
                }
            }

            return null;
        });
    }

    public function delete(int $id): bool {
        return $this->modify(function (array &$items) use ($id): bool {
            foreach ($items as $index => $item) {
                if ((int) ($item['id'] ?? 0) === $id) {
                    array_splice($items, $index, 1);
                    return true;
                }
            }

            return false;
        });
    }

    private function productToArray(Product $product, int $id): array {
        return [
            'id' => $id,
            'name' => $product->name,
            'price' => $product->price,
            'size' => $product->size
        ];
    }

    private function readItems(): array {
        $handle = fopen($this->filePath, 'r');
        if ($handle === false) {
            throw new RuntimeException('Unable to read the product data file.');
        }

        try {
            if (!flock($handle, LOCK_SH)) {
                throw new RuntimeException('Unable to lock the product data file.');
            }

            return $this->decodeItems(stream_get_contents($handle));
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private function modify(callable $callback) {
        $handle = fopen($this->filePath, 'c+');
        if ($handle === false) {
            throw new RuntimeException('Unable to open the product data file.');
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                throw new RuntimeException('Unable to lock the product data file.');
            }

            rewind($handle);
            $items = $this->decodeItems(stream_get_contents($handle));
            $result = $callback($items);
            $content = json_encode(
                $items,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            ) . PHP_EOL;

            rewind($handle);
            if (!ftruncate($handle, 0) || fwrite($handle, $content) !== strlen($content) || !fflush($handle)) {
                throw new RuntimeException('Unable to save the product data file.');
            }

            return $result;
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private function decodeItems(string $content): array {
        try {
            $items = json_decode($content === '' ? '[]' : $content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('The product data file contains invalid JSON.', 0, $exception);
        }

        if (!is_array($items)) {
            throw new RuntimeException('The product data file must contain a JSON array.');
        }

        return array_values($items);
    }
}
