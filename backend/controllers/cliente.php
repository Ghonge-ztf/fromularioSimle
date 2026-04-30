<?php
// filepath: backend/controllers/cliente.php

function handleCliente($method, $id = null) {
    $conn = getConnection();
    $id = filter_var($id, FILTER_VALIDATE_INT);

    switch ($method) {
        case 'GET':
            if ($id) {
                // Obtener un cliente por ID
                $stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $cliente = $result->fetch_assoc();
                $stmt->close();

                if ($cliente) {
                    echo json_encode($cliente);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Cliente no encontrado']);
                }
            } else {
                // Obtener todos los clientes
                $result = $conn->query("SELECT * FROM clientes ORDER BY id DESC");
                $clientes = [];
                while ($row = $result->fetch_assoc()) {
                    $clientes[] = $row;
                }
                echo json_encode($clientes);
            }
            break;

        case 'POST':
            // Crear nuevo cliente
            $data = json_decode(file_get_contents("php://input"), true);

            if (!is_array($data)) {
                http_response_code(400);
                echo json_encode(['error' => 'JSON inválido']);
                break;
            }

            if (empty(trim($data['nombre'] ?? ''))) {
                http_response_code(400);
                echo json_encode(['error' => 'El nombre es requerido']);
                break;
            }

            $stmt = $conn->prepare("INSERT INTO clientes (nombre, apellido, email, telefono, direccion) VALUES (?, ?, ?, ?, ?)");
            $nombre = trim($data['nombre']);
            $apellido = $data['apellido'] ?? '';
            $email = trim($data['email'] ?? '');
            $email = $email === '' ? null : $email;
            $telefono = $data['telefono'] ?? '';
            $direccion = $data['direccion'] ?? '';

            $stmt->bind_param("sssss", $nombre, $apellido, $email, $telefono, $direccion);

            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode([
                    'id' => $conn->insert_id,
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'email' => $email,
                    'telefono' => $telefono,
                    'direccion' => $direccion
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al crear cliente']);
            }
            $stmt->close();
            break;

        case 'PUT':
            // Actualizar cliente
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID de cliente requerido']);
                break;
            }

            $data = json_decode(file_get_contents("php://input"), true);

            if (!is_array($data)) {
                http_response_code(400);
                echo json_encode(['error' => 'JSON inválido']);
                break;
            }

            $fields = [];
            $params = [];
            $types = "";

            if (isset($data['nombre'])) {
                $fields[] = "nombre = ?";
                $params[] = trim($data['nombre']);
                $types .= "s";
            }
            if (isset($data['apellido'])) {
                $fields[] = "apellido = ?";
                $params[] = $data['apellido'];
                $types .= "s";
            }
            if (isset($data['email'])) {
                $fields[] = "email = ?";
                $email = trim($data['email']);
                $params[] = $email === '' ? null : $email;
                $types .= "s";
            }
            if (isset($data['telefono'])) {
                $fields[] = "telefono = ?";
                $params[] = $data['telefono'];
                $types .= "s";
            }
            if (isset($data['direccion'])) {
                $fields[] = "direccion = ?";
                $params[] = $data['direccion'];
                $types .= "s";
            }

            if (empty($fields)) {
                http_response_code(400);
                echo json_encode(['error' => 'No hay campos para actualizar']);
                break;
            }

            $params[] = $id;
            $types .= "i";

            $sql = "UPDATE clientes SET " . implode(", ", $fields) . " WHERE id = ?";
            $stmt = $conn->prepare($sql);
            bindParams($stmt, $types, $params);

            if ($stmt->execute()) {
                echo json_encode(['message' => 'Cliente actualizado']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al actualizar cliente']);
            }
            $stmt->close();
            break;

        case 'DELETE':
            // Eliminar cliente
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID de cliente requerido']);
                break;
            }

            $stmt = $conn->prepare("DELETE FROM clientes WHERE id = ?");
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                if ($stmt->affected_rows === 0) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Cliente no encontrado']);
                } else {
                    echo json_encode(['message' => 'Cliente eliminado']);
                }
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al eliminar cliente']);
            }
            $stmt->close();
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            break;
    }

    $conn->close();
}

if (!function_exists('bindParams')) {
    function bindParams($stmt, $types, array &$params) {
        $refs = [];
        foreach ($params as $key => &$value) {
            $refs[$key] = &$value;
        }

        $stmt->bind_param($types, ...$refs);
    }
}
