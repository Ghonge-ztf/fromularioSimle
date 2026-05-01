<?php
// ============================================================
// controllers/producto.php — Operaciones sobre productos
// ============================================================
// CRUD = Create (crear), Read (leer), Update (actualizar), Delete (eliminar)
// Esta función recibe el método HTTP y el ID (si aplica),
// y ejecuta la operación correspondiente en la base de datos.

function handleProducto($method, $id = null) {

    // Obtener conexión a la base de datos (definida en config.php)
    $conn = getConnection();

    // Validar que el ID sea un número entero (evita inyecciones SQL)
    // filter_var devuelve false si no es un entero válido
    $id = filter_var($id, FILTER_VALIDATE_INT);

    switch ($method) {

        // --------------------------------------------------------
        // GET — Leer productos
        // --------------------------------------------------------
        case 'GET':
            if ($id) {
                // Buscar UN producto por su ID
                // "?" es un marcador de posición seguro (prepared statement)
                $stmt = $conn->prepare("SELECT * FROM productos WHERE produc_id = ?");
                $stmt->bind_param("i", $id); // "i" = integer (entero)
                $stmt->execute();
                $producto = $stmt->get_result()->fetch_assoc(); // Obtener fila como array
                $stmt->close();

                if ($producto) {
                    echo json_encode($producto); // Devolver el producto encontrado
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Producto no encontrado']);
                }
            } else {
                // Obtener TODOS los productos, del más nuevo al más antiguo
                $result    = $conn->query("SELECT * FROM productos ORDER BY produc_id DESC");
                $productos = [];
                while ($row = $result->fetch_assoc()) {
                    $productos[] = $row; // Agregar cada fila al array
                }
                echo json_encode($productos);
            }
            break;

        // --------------------------------------------------------
        // POST — Crear un nuevo producto
        // --------------------------------------------------------
        case 'POST':
            // Leer el cuerpo de la petición (viene en formato JSON)
            // php://input es el flujo de datos crudos que envía el cliente
            $data = json_decode(file_get_contents("php://input"), true);

            // Validar que el JSON sea válido y que tenga nombre
            if (!is_array($data) || empty(trim($data['produc_nombre'] ?? ''))) {
                http_response_code(400); // 400 = petición incorrecta
                echo json_encode(['error' => 'El nombre es requerido', 'data' => $data]);
                break;
            }

            // Extraer y limpiar los datos recibidos
            $produc_nombre = trim($data['produc_nombre']);
            $produc_cantid = (int)    ($data['produc_cantid'] ?? 0); // Convertir a entero
            $produc_catego = $data['produc_catego'] ?? '';
            $produc_precio = (float)  ($data['produc_precio'] ?? 0); // Convertir a decimal

            // Insertar en la base de datos con prepared statement
            // "ssdi" = string, string, integer, double (tipos de cada parámetro)
            $stmt = $conn->prepare("INSERT INTO productos (produc_nombre, produc_cantid, produc_catego, produc_precio) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssid", $produc_nombre, $produc_cantid, $produc_catego, $produc_precio);

            if ($stmt->execute()) {
                http_response_code(201); // 201 = creado exitosamente
                echo json_encode([
                    'produc_id'     => $conn->insert_id,
                    'produc_nombre' => $produc_nombre,
                    'produc_cantid' => $produc_cantid,
                    'produc_catego' => $produc_catego,
                    'produc_precio' => $produc_precio
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al crear producto']);
            }
            $stmt->close();
            break;

        // --------------------------------------------------------
        // PUT — Actualizar un producto existente
        // --------------------------------------------------------
        case 'PUT':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID de producto requerido']);
                break;
            }

            $data = json_decode(file_get_contents("php://input"), true);

            if (!is_array($data)) {
                http_response_code(400);
                echo json_encode(['error' => 'JSON inválido']);
                break;
            }

            // Construir dinámicamente la consulta UPDATE
            // Solo actualizamos los campos que vienen en la petición
            $fields = []; // Partes del SET: "produc_nombre = ?", "produc_precio = ?", etc.
            $params = []; // Valores que reemplazarán los "?"
            $types  = ""; // Tipos de cada valor: "s"=string, "d"=decimal, "i"=integer

            if (isset($data['produc_nombre'])) { $fields[] = "produc_nombre = ?"; $params[] = trim($data['produc_nombre']); $types .= "s"; }
            if (isset($data['produc_cantid'])) { $fields[] = "produc_cantid = ?"; $params[] = (int) $data['produc_cantid']; $types .= "i"; }
            if (isset($data['produc_catego'])) { $fields[] = "produc_catego = ?"; $params[] = $data['produc_catego']; $types .= "s"; }
            if (isset($data['produc_precio'])) { $fields[] = "produc_precio = ?"; $params[] = (float) $data['produc_precio']; $types .= "d"; }

            if (empty($fields)) {
                http_response_code(400);
                echo json_encode(['error' => 'No hay campos para actualizar']);
                break;
            }

            // Agregar el ID al final (para el WHERE produc_id = ?)
            $params[] = $id;
            $types   .= "i";

            // Armar la consulta final: UPDATE productos SET campo1=?, campo2=? WHERE produc_id=?
            $sql  = "UPDATE productos SET " . implode(", ", $fields) . " WHERE produc_id = ?";
            $stmt = $conn->prepare($sql);

            // bind_param no acepta arrays directamente, usamos spread operator (...)
            $stmt->bind_param($types, ...array_values($params));

            if ($stmt->execute()) {
                echo json_encode(['message' => 'Producto actualizado']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al actualizar producto']);
            }
            $stmt->close();
            break;

        // --------------------------------------------------------
        // DELETE — Eliminar un producto
        // --------------------------------------------------------
        case 'DELETE':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID de producto requerido']);
                break;
            }

            $stmt = $conn->prepare("DELETE FROM productos WHERE produc_id = ?");
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                if ($stmt->affected_rows === 0) {
                    // affected_rows = filas afectadas; si es 0, el ID no existía
                    http_response_code(404);
                    echo json_encode(['error' => 'Producto no encontrado']);
                } else {
                    echo json_encode(['message' => 'Producto eliminado']);
                }
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al eliminar producto']);
            }
            $stmt->close();
            break;

        default:
            http_response_code(405); // 405 = método no permitido
            echo json_encode(['error' => 'Método no permitido']);
            break;
    }

    $conn->close(); // Cerrar la conexión al terminar
}