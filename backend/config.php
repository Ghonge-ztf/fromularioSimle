<?php
// ============================================================
// config.php — Configuración de la base de datos
// ============================================================
// Aquí definimos los datos para conectarnos a MySQL.
// "define" crea constantes (valores fijos que no cambian).
define("DB_HOST", "localhost");   // Servidor de la base de datos
define("DB_USER", "ghz");        // Usuario de MySQL
define("DB_PASS", "QWEAsD");            // Contraseña (vacía en local)
define("DB_NAME", "formularioDB"); // Nombre de la base de datos
define("DB_PORT", 3306);          // Puerto por defecto de MySQL

// ------------------------------------------------------------
// Función: getConnection()
// Crea y devuelve una conexión activa a la base de datos.
// Si falla, detiene todo y devuelve un error en formato JSON.
// ------------------------------------------------------------
function getConnection() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

    // Si la conexión falló, enviamos error y detenemos el script
    if (!$conn) {
        http_response_code(500); // Código HTTP 500 = error del servidor
        die(json_encode(['error' => 'Error al conectarse: ' . mysqli_connect_error()]));
    }

    $conn->set_charset("utf8mb4"); // Soporte para caracteres especiales (tildes, ñ, etc.)
    return $conn;
}

// ------------------------------------------------------------
// Función: initDatabase()
// Crea la base de datos y las tablas si no existen todavía.
// Se llama automáticamente al inicio de cada petición.
// ------------------------------------------------------------
function initDatabase() {
    // Conectamos sin especificar base de datos (aún no existe)
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

    if (!$conn) {
        http_response_code(500);
        die(json_encode(['error' => 'Error al conectarse: ' . mysqli_connect_error()]));
    }

    // Crear la base de datos si no existe
    $conn->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME);

    // Seleccionar la base de datos recién creada (o ya existente)
    $conn->select_db(DB_NAME);

    // Crear tabla "productos" si no existe
    // AUTO_INCREMENT = el ID se asigna solo (1, 2, 3...)
    // NOT NULL = ese campo es obligatorio
    // DEFAULT = valor por defecto si no se envía
    $conn->query("
        CREATE TABLE IF NOT EXISTS productos (
            produc_id     INT AUTO_INCREMENT PRIMARY KEY,
            produc_nombre VARCHAR(255) NOT NULL,
            produc_cantid INT DEFAULT 0,
            produc_catego VARCHAR(100),
            produc_precio DECIMAL(10,2)
        )
    ");

    // Crear tabla "clientes" si no existe
    // UNIQUE en client_email = no puede haber dos clientes con el mismo correo
    $conn->query("
        CREATE TABLE IF NOT EXISTS clientes (
            client_id     INT AUTO_INCREMENT PRIMARY KEY,
            client_nombre VARCHAR(255) NOT NULL,
            client_email  VARCHAR(255) UNIQUE,
            client_telefo VARCHAR(20),
            client_apelli VARCHAR(255)
        )
    ");

    $conn->close();
}