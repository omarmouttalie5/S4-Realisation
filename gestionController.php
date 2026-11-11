<?php

require_once "ProductManager.php";

// NEW: Presentation / HTTP layer
// This class receives requests from the frontend and delegates work to the business layer.
class GestionController {
    private ProductManager $manager;

    public function __construct() {
        $this->manager = new ProductManager();
    }

    public function handleRequest() {
        header("Content-Type: application/json; charset=UTF-8");
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === "POST") {
            $data = json_decode(file_get_contents('php://input'), true);
            $name = $data['name'] ?? '';
            $price = $data['price'] ?? '';
            $size = $data['size'] ?? '';

            try {
                if (!empty($name) && !empty($price) && !empty($size)) {
                    $newItem = $this->manager->add($name, $price, $size);
                    echo json_encode(['success' => true, 'data' => $newItem]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Please fill all fields']);
                }
            } catch (InvalidArgumentException $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        } else {
            echo json_encode($this->manager->getAll());
        }
    }
}