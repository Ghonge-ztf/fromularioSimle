<?php
// filepath: backend/config.php

define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_NAME", "formularioDB");
define("DB_PORT", 3306);

function getConnection() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

    if (!$conn || $conn->connect_error) {
        http_response_code(500);
        die(json_encode(['error' => 'Error al conectarse: ' . mysqli_connect_error()]));
    }

    // Configurar charset UTF-8
    $conn->set_charset("utf8mb4");

    return $conn;
}

// Función para inicializar la base de datos
function initDatabase($verbose = false) {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, "", DB_PORT);

    if (!$conn || $conn->connect_error) {
        http_response_code(500);
        die(json_encode(['error' => 'Error al conectarse: ' . mysqli_connect_error()]));
    }

    $messages = [];

    // Crear base de datos si no existe
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
    if ($conn->query($sql) === TRUE) {
        $messages[] = "Base de datos creada o verificada";
    } else {
        http_response_code(500);
        die(json_encode(['error' => 'Error al crear base de datos: ' . $conn->error]));
    }

    // Seleccionar la base de datos
    $conn->select_db(DB_NAME);

    // Crear tabla de productos si no existe
    $sqlProductos = "CREATE TABLE IF NOT EXISTS productos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(255) NOT NULL,
        descripcion TEXT,
        precio DECIMAL(10,2),
        stock INT DEFAULT 0,
        categoria VARCHAR(100),
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    if ($conn->query($sqlProductos) === TRUE) {
        $messages[] = "Tabla productos creada o verificada";
    } else {
        http_response_code(500);
        die(json_encode(['error' => 'Error al crear tabla productos: ' . $conn->error]));
    }

    addColumnIfMissing($conn, 'productos', 'descripcion', 'TEXT');
    addColumnIfMissing($conn, 'productos', 'stock', 'INT DEFAULT 0');

    // Crear tabla de clientes si no existe
    $sqlClientes = "CREATE TABLE IF NOT EXISTS clientes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(255) NOT NULL,
        apellido VARCHAR(255),
        email VARCHAR(255) UNIQUE,
        telefono VARCHAR(20),
        direccion TEXT,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    if ($conn->query($sqlClientes) === TRUE) {
        $messages[] = "Tabla clientes creada o verificada";
    } else {
        http_response_code(500);
        die(json_encode(['error' => 'Error al crear tabla clientes: ' . $conn->error]));
    }

    addColumnIfMissing($conn, 'clientes', 'apellido', 'VARCHAR(255)');
    addColumnIfMissing($conn, 'clientes', 'direccion', 'TEXT');

    $conn->close();

    return $verbose ? $messages : true;
}

function addColumnIfMissing($conn, $table, $column, $definition) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?
    ");
    $dbName = DB_NAME;
    $stmt->bind_param("sss", $dbName, $table, $column);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ((int) $result['total'] === 0) {
        $sql = "ALTER TABLE `$table` ADD COLUMN `$column` $definition";
        if (!$conn->query($sql)) {
            http_response_code(500);
            die(json_encode(['error' => "Error al agregar columna $column: " . $conn->error]));
        }
    }
}
