<?php

// NEW: Entry point for the API layer
require_once "gestionController.php";

$controller = new GestionController();
$controller->handleRequest();