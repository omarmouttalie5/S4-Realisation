<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "Services" . DIRECTORY_SEPARATOR . "ProductService.php";

class GestionController {
    private ProductService $service;

    public function __construct(?ProductService $service = null) {
        $this->service = $service ?? new ProductService();
    }

    public function handleRequest(): void {
        header("Content-Type: application/json; charset=UTF-8");
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        try {
            switch ($method) {
                case 'GET':
                    $this->handleGet();
                    return;
                case 'POST':
                    $data = $this->readRequestData();
                    $product = $this->service->add(
                        $this->field($data, 'name'),
                        $this->field($data, 'price'),
                        $this->field($data, 'size')
                    );
                    $this->respond(['success' => true, 'data' => $product], 201);
                    return;
                case 'PUT':
                    $data = $this->readRequestData();
                    $product = $this->service->update(
                        $this->requestId($data),
                        $this->field($data, 'name'),
                        $this->field($data, 'price'),
                        $this->field($data, 'size')
                    );

                    if ($product === null) {
                        $this->respond(['success' => false, 'message' => 'Product not found.'], 404);
                        return;
                    }

                    $this->respond(['success' => true, 'data' => $product]);
                    return;
                case 'DELETE':
                    $data = array_key_exists('id', $_GET) ? [] : $this->readRequestData();
                    if (!$this->service->delete($this->requestId($data))) {
                        $this->respond(['success' => false, 'message' => 'Product not found.'], 404);
                        return;
                    }

                    $this->respond(['success' => true, 'message' => 'Product deleted.']);
                    return;
                default:
                    header('Allow: GET, POST, PUT, DELETE');
                    $this->respond(['success' => false, 'message' => 'Method not allowed.'], 405);
            }
        } catch (UnexpectedValueException $exception) {
            $this->respond(['success' => false, 'message' => $exception->getMessage()], 400);
        } catch (InvalidArgumentException $exception) {
            $this->respond(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            error_log($exception->__toString());
            $this->respond(['success' => false, 'message' => 'An unexpected server error occurred.'], 500);
        }
    }

    private function handleGet(): void {
        if (!array_key_exists('id', $_GET)) {
            $this->respond($this->service->getAll());
            return;
        }

        $product = $this->service->getById($this->requestId([]));
        if ($product === null) {
            $this->respond(['success' => false, 'message' => 'Product not found.'], 404);
            return;
        }

        $this->respond(['success' => true, 'data' => $product]);
    }

    private function readRequestData(): array {
        $content = file_get_contents('php://input');
        if ($content === false) {
            throw new UnexpectedValueException('Unable to read the request body.');
        }

        try {
            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new UnexpectedValueException('Request body must contain valid JSON.', 0, $exception);
        }

        if (!is_array($data)) {
            throw new UnexpectedValueException('Request body must be a JSON object.');
        }

        return $data;
    }

    private function field(array $data, string $name): string {
        $value = $data[$name] ?? '';
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            throw new InvalidArgumentException("The {$name} field must be a string or number.");
        }

        return (string) $value;
    }

    private function requestId(array $data): int {
        $value = $_GET['id'] ?? $data['id'] ?? null;
        $id = filter_var($value, FILTER_VALIDATE_INT);
        if ($id === false || $id === null || $id < 1) {
            throw new InvalidArgumentException('A valid product ID is required.');
        }

        return $id;
    }

    private function respond(array $payload, int $status = 200): void {
        http_response_code($status);
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
