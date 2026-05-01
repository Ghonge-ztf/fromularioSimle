<?php
// ============================================================
// controllers/cliente.php — Operaciones sobre clientes
// ============================================================
// CRUD = Create (crear), Read (leer), Update (actualizar), Delete (eliminar)
// Campos: client_id, client_nombre, client_email, client_telefo, client_apelli

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

function handleCliente($method, $id = null) {

    $conn = getConnection();
    $id   = filter_var($id, FILTER_VALIDATE_INT);

    switch ($method) {

        // --------------------------------------------------------
        // GET — Leer clientes
        // --------------------------------------------------------
        case 'GET':
            if ($id) {
                $stmt = $conn->prepare("SELECT * FROM clientes WHERE client_id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $cliente = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($cliente) {
                    echo json_encode($cliente);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Cliente no encontrado']);
                }
            } else {
                $result   = $conn->query("SELECT * FROM clientes ORDER BY client_id DESC");
                $clientes = [];
                while ($row = $result->fetch_assoc()) {
                    $clientes[] = $row;
                }
                echo json_encode($clientes);
            }
            break;

        // --------------------------------------------------------
        // POST — Crear un nuevo cliente
        // --------------------------------------------------------
        case 'POST':
            $data = json_decode(file_get_contents("php://input"), true);

            if (!is_array($data) || empty(trim($data['client_nombre'] ?? ''))) {
                http_response_code(400);
                echo json_encode(['error' => 'El nombre es requerido']);
                break;
            }

            $client_nombre   = trim($data['client_nombre']);
            $client_apelli  = $data['client_apelli']  ?? '';
            $client_email   = trim($data['client_email'] ?? '');
            $client_email   = $client_email === '' ? null : $client_email;
            $client_telefo  = $data['client_telefo']  ?? '';

            $stmt = $conn->prepare("INSERT INTO clientes (client_nombre, client_apelli, client_email, client_telefo) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $client_nombre, $client_apelli, $client_email, $client_telefo);

            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode([
                    'client_id'     => $conn->insert_id,
                    'client_nombre' => $client_nombre,
                    'client_apelli' => $client_apelli,
                    'client_email'  => $client_email,
                    'client_telefo' => $client_telefo
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al crear cliente']);
            }
            $stmt->close();
            break;

        // --------------------------------------------------------
        // PUT — Actualizar un cliente existente
        // --------------------------------------------------------
        case 'PUT':
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
            $types  = "";

            if (isset($data['client_nombre'])) { $fields[] = "client_nombre = ?"; $params[] = trim($data['client_nombre']); $types .= "s"; }
            if (isset($data['client_apelli'])) { $fields[] = "client_apelli = ?"; $params[] = $data['client_apelli']; $types .= "s"; }
            if (isset($data['client_email'])) {
                $email = trim($data['client_email']);
                $fields[] = "client_email = ?";
                $params[] = $email === '' ? null : $email;
                $types .= "s";
            }
            if (isset($data['client_telefo'])) { $fields[] = "client_telefo = ?"; $params[] = $data['client_telefo']; $types .= "s"; }

            if (empty($fields)) {
                http_response_code(400);
                echo json_encode(['error' => 'No hay campos para actualizar']);
                break;
            }

            $params[] = $id;
            $types   .= "i";

            $sql  = "UPDATE clientes SET " . implode(", ", $fields) . " WHERE client_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...array_values($params));

            if ($stmt->execute()) {
                echo json_encode(['message' => 'Cliente actualizado']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al actualizar cliente']);
            }
            $stmt->close();
            break;

        // --------------------------------------------------------
        // DELETE — Eliminar un cliente
        // --------------------------------------------------------
        case 'DELETE':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID de cliente requerido']);
                break;
            }

            $stmt = $conn->prepare("DELETE FROM clientes WHERE client_id = ?");
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
