<?php
// filepath: backend/index.php
// API REST para Productos y Clientes

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once "config.php";

// Obtener el método de la solicitud y la ruta
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$path = trim($path, '/');

// Extraer el recurso y el ID
$segments = array_values(array_filter(explode('/', $path), 'strlen'));
$resource = '';
$resourceId = null;

foreach ($segments as $index => $segment) {
    if (in_array($segment, ['productos', 'clientes', 'init'], true)) {
        $resource = $segment;
        $resourceId = $segments[$index + 1] ?? null;
        break;
    }
}

// Inicializar la base de datos si no existe
if ($resource !== 'init') {
    initDatabase();
}

// Routing básico
switch ($resource) {
    case 'productos':
        require_once "controllers/producto.php";
        handleProducto($method, $resourceId);
        break;

    case 'clientes':
        require_once "controllers/cliente.php";
        handleCliente($method, $resourceId);
        break;

    case 'init':
        // Endpoint para inicializar la base de datos
        $messages = initDatabase(true);
        echo json_encode([
            'message' => 'Base de datos inicializada',
            'details' => $messages
        ]);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Recurso no encontrado']);
        break;
}
