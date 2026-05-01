const API_BASE = "./backend/index.php";

const data = {
    productos: [],
    clientes: []
};

const resources = {
    productos: {
        fields: ["produc_nombre", "produc_catego", "produc_precio", "produc_cantid"],
        form: document.getElementById("form-productos"),
        table: document.getElementById("tabla-productos"),
        message: document.getElementById("mensaje-productos"),
        createText: "Agregar Producto",
        updateText: "Actualizar Producto"
    },
    clientes: {
        fields: ["client_nombre", "client_apelli", "client_email", "client_telefo"],
        form: document.getElementById("form-clientes"),
        table: document.getElementById("tabla-clientes"),
        message: document.getElementById("mensaje-clientes"),
        createText: "Agregar Cliente",
        updateText: "Actualizar Cliente"
    }
};

document.addEventListener("DOMContentLoaded", () => {
    loadAll();
});

async function loadAll() {
    await Promise.all([
        load("productos"),
        load("clientes")
    ]);
}

function show(id, el) {
    document.querySelectorAll(".panel").forEach(panel => panel.classList.remove("active"));
    document.querySelectorAll(".tab").forEach(tab => tab.classList.remove("active"));
    document.getElementById(id).classList.add("active");
    el.classList.add("active");
}

async function load(type) {
    try {
        setMessage(type, "Cargando datos...");
        data[type] = await request(type);
        render(type);
        setMessage(type, "");
    } catch (error) {
        setMessage(type, error.message, true);
    }
}

async function save(type, event) {
    event.preventDefault();

    const config = resources[type];
    const formData = new FormData(config.form);
    const id = formData.get("id");
    const payload = getPayload(type, formData);
    const method = id ? "PUT" : "POST";
    const endpoint = id ? `${type}/${id}` : type;

    console.log(payload);

    try {
        await request(endpoint, {
            method,
            body: JSON.stringify(payload)
        });
        cancelEdit(type);
        await load(type);
        setMessage(type, id ? "Registro actualizado." : "Registro agregado.");
    } catch (error) {
        setMessage(type, error.message, true);
    }
}

function edit(type, id) {
    const config = resources[type];
    const item = data[type].find(row => Number(row.produc_id || row.client_id) === Number(id));

    if (!item) {
        setMessage(type, "No se encontró el registro seleccionado.", true);
        return;
    }

    config.form.elements.id.value = item.produc_id || item.client_id;
    config.fields.forEach(field => {
        config.form.elements[field].value = item[field] ?? "";
    });

    config.form.querySelector(".btn-submit").textContent = config.updateText;
    config.form.querySelector(".btn-cancel").hidden = false;
    config.form.scrollIntoView({ behavior: "smooth", block: "start" });
}

async function remove(type, id) {
    const confirmed = window.confirm("¿Deseas eliminar este registro?");

    if (!confirmed) {
        return;
    }

    try {
        await request(`${type}/${id}`, { method: "DELETE" });
        await load(type);
        setMessage(type, "Registro eliminado.");
    } catch (error) {
        setMessage(type, error.message, true);
    }
}

function cancelEdit(type) {
    const config = resources[type];
    config.form.reset();
    config.form.elements.id.value = "";
    config.form.querySelector(".btn-submit").textContent = config.createText;
    config.form.querySelector(".btn-cancel").hidden = true;
}

function render(type) {
    const config = resources[type];

    if (data[type].length === 0) {
        config.table.innerHTML = `
            <tr>
                <td colspan="${config.fields.length + 1}" class="empty">Sin registros</td>
            </tr>
        `;
        return;
    }

    config.table.innerHTML = data[type].map(row => `
        <tr>
            ${config.fields.map(field => `<td>${escapeHtml(formatValue(row[field], field))}</td>`).join("")}
            <td class="actions">
                <button class="edit" type="button" onclick="edit('${type}', ${row.produc_id || row.client_id})">Editar</button>
                <button class="del" type="button" onclick="remove('${type}', ${row.produc_id || row.client_id})">Eliminar</button>
            </td>
        </tr>
    `).join("");
}

function getPayload(type, formData) {
    const payload = {};

    resources[type].fields.forEach(field => {
        payload[field] = formData.get(field)?.trim() ?? "";
    });

    if (type === "productos") {
        payload.produc_precio = Number(payload.produc_precio);
        payload.produc_cantid = Number.parseInt(payload.produc_cantid, 10);
    }
    console.log(payload);

    return payload;
}

async function request(endpoint, options = {}) {

    
    const response = await fetch(`${API_BASE}/${endpoint}`, {
        headers: {
            "Content-Type": "application/json"
        },
        ...options
    })
    .catch( error => console.log(error));

    const text = await response.text();
    const result = text ? JSON.parse(text) : null;

    if (!response.ok) {
        throw new Error(result?.error ?? "No se pudo completar la petición.");
    }

    return result;
}

function formatValue(value, field) {
    if (field === "produc_precio" && value !== "" && value !== null && value !== undefined) {
        return Number(value).toFixed(2);
    }

    return value ?? "";
}

function setMessage(type, message, isError = false) {
    const messageElement = resources[type].message;
    messageElement.textContent = message;
    messageElement.classList.toggle("error", isError);
}

function escapeHtml(value) {
    return String(value)
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}
