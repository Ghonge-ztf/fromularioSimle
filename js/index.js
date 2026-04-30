
const panelPrincipal = document.getElementById("contenido-main");
const titulo = document.getElementById("tituloPanel");
const btnCliente = document.getElementById("btn-cliente");
const btnUsuario = document.getElementById("btn-usuario");


const data = { productos: [], clientes: [] };

const cols = {
    productos: ['nombre', 'categoria', 'precio', 'stock'],
    clientes: ['nombre', 'apellido', 'email', 'telefono']
};


function mostrar(){
    
}

function show(id, el) {
    document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.getElementById(id).classList.add('active');
    el.classList.add('active');
}

function add(type, e) {
    e.preventDefault();
    const form = e.target;
    const row = {};
    cols[type].forEach(c => row[c] = form[c]?.value || '');
    data[type].push(row);
    render(type);
    form.reset();
}

function remove(type, i) {
    data[type].splice(i, 1);
    render(type);
}

function render(type) {
    document.getElementById('tabla-' + type).innerHTML =
        data[type].map((r, i) =>
            `<tr>${cols[type].map(c => `<td>${r[c]}</td>`).join('')}
           <td><button class="del" onclick="remove('${type}',${i})">✕</button></td></tr>`
        ).join('');
}