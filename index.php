<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos y Clientes</title>
    <link rel="stylesheet" href="./css/styles.css">
</head>
<body>
    <main class="form-container">
        <!-- Encabezado -->
        <div class="panel-header">
            <h2>Productos y Clientes</h2>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" onclick="show('productos', this)">Productos</button>
            <button class="tab" onclick="show('clientes', this)">Clientes</button>
        </div>

        <!-- Panel Productos -->
        <div id="productos" class="panel active">
            <form id="form-productos" onsubmit="save('productos', event)">
                <input type="hidden" name="id">
                <div class="form-group">
                    <input name="produc_nombre" placeholder="Nombre" required>
                </div>
                <div class="form-group">
                    <input name="produc_catego" placeholder="Categoría" required>
                </div>
                <div class="form-group">
                    <input name="produc_precio" placeholder="Precio" type="number" min="0" step="0.01" required>
                </div>
                <div class="form-group">
                    <input name="produc_cantid" placeholder="Stock" type="number" min="0" required>
                </div>
                <button type="submit" class="btn-submit">Agregar Producto</button>
                <button type="button" class="btn-cancel" onclick="cancelEdit('productos')" hidden>Cancelar edición</button>
            </form>
            <p id="mensaje-productos" class="message" role="status"></p>
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="tabla-productos"></tbody>
            </table>
        </div>

        <!-- Panel Clientes -->
        <div id="clientes" class="panel">
            <form id="form-clientes" onsubmit="save('clientes', event)">
                <input type="hidden" name="id">
                <div class="form-group">
                    <input name="client_nombre" placeholder="Nombre" required>
                </div>
                <div class="form-group">
                    <input name="client_apelli" placeholder="Apellido" required>
                </div>
                <div class="form-group">
                    <input name="client_email" placeholder="Email" type="email" required>
                </div>
                <div class="form-group">
                    <input name="client_telefo" placeholder="Teléfono">
                </div>
                <button type="submit" class="btn-submit">Agregar Cliente</button>
                <button type="button" class="btn-cancel" onclick="cancelEdit('clientes')" hidden>Cancelar edición</button>
            </form>
            <p id="mensaje-clientes" class="message" role="status"></p>
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="tabla-clientes"></tbody>
            </table>
        </div>
    </main>

    <script src="./js/index.js"></script>
</body>
</html>
