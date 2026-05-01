<?php
// ============================================================
// index.php — Punto de entrada del backend (API)
// ============================================================

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '';
// Este archivo decide a qué controlador enviarlo.

// $raw = file_get_contents("php://input");
// error_log("RAW INPUT: " . $raw);  // revisa en el log del servidor
// $data = json_decode($raw, true);
// error_log("JSON ERROR: " . json_last_error_msg());

// Cabeceras HTTP: le dicen al navegador cómo interpretar la respuesta
header("Content-Type: application/json");          // Responderemos en formato JSON
header("Access-Control-Allow-Origin: *");          // Permitir peticiones desde cualquier origen
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Los navegadores envían una petición OPTIONS antes de POST/PUT/DELETE
// para verificar permisos. La respondemos con 200 OK y terminamos.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Cargar la configuración (conexión + creación de tablas)
require_once "config.php";

// Inicializar la base de datos (crea tablas si no existen)
initDatabase();

// ------------------------------------------------------------
// Detectar el recurso solicitado desde la URL
// Ejemplo: /backend/index.php/productos/3
//   → recurso = "productos", id = 3
// ------------------------------------------------------------
$segments = array_values(                      // Dividir la ruta en partes
    array_filter(
        explode('/', trim($path, '/')),        // Separar por "/"
        'strlen'                               // Eliminar partes vacías
    )
);

// Buscar en los segmentos cuál es el recurso ("productos" o "clientes")
$resource   = '';
$resourceId = null;

foreach ($segments as $index => $segment) {
    if (in_array($segment, ['productos', 'clientes'], true)) {
        $resource   = $segment;
        $resourceId = $segments[$index + 1] ?? null; // El siguiente segmento es el ID (si existe)
        break;
    }
}

// ------------------------------------------------------------
// Routing: según el recurso, llamar al controlador correcto
// ------------------------------------------------------------
switch ($resource) {

    case 'productos':
        require_once "controllers/producto.php";
        handleProducto($method, $resourceId); // Delegar al controlador de productos
        break;

    case 'clientes':
        require_once "controllers/cliente.php";
        handleCliente($method, $resourceId);  // Delegar al controlador de clientes
        break;

    default:
        // Si el recurso no existe, responder con error 404
        http_response_code(404);
        echo json_encode(['error' => 'Recurso no encontrado']);
        break;
}
