<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR . "Controllers"
    . DIRECTORY_SEPARATOR . "GestionController.php";

$controller = new GestionController();
$controller->handleRequest();