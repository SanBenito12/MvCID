<?php
session_start();
if (!isset($_SESSION['id_cliente']) || !isset($_SESSION['llave_secreta'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - EduPlatform</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <script>
        const id_cliente = "<?= $_SESSION['id_cliente'] ?>";
        const llave_secreta = "<?= $_SESSION['llave_secreta'] ?>";
    </script>
</head>
<body>
    <div class="container">
        <h1>Dashboard de Cursos</h1>
        <a href="logout.php">Cerrar Sesión</a>

        <div id="mensaje" class="mensaje"></div>

        <section id="crearCurso">
            <h2>Crear nuevo curso</h2>
            <form id="formCurso">
                <input type="text" name="titulo" placeholder="Título" required><br>
                <input type="text" name="instructor" placeholder="Instructor" required><br>
                <input type="file" name="imagen" accept="image/*" required><br>
                <input type="number" name="precio" placeholder="Precio" required><br>
                <textarea name="descripcion" placeholder="Descripción" required></textarea><br>
                <button type="submit">Crear Curso</button>
            </form>
        </section>

        <section id="misCursos">
            <h2>Mis Cursos</h2>
            <div id="listaCursos"></div>
        </section>

        <section id="comprarCursos">
            <h2>Comprar Cursos</h2>
            <div id="cursosDisponibles"></div>
        </section>

        <section id="misCompras">
            <h2>Mis Compras</h2>
            <div id="listaCompras"></div>
        </section>
    </div>

    <script>
    let id_creador = null;

    document.addEventListener('DOMContentLoaded', async () => {
        // Validar sesión
        const auth = await fetch(`/backend/api/auth.php?id_cliente=${id_cliente}&llave_secreta=${llave_secreta}`);
        const data = await auth.json();

        if (!data.valido) {
            alert("Sesión inválida. Redirigiendo...");
            location.href = "login.php";
            return;
        }

        id_creador = data.id_creador;

        cargarCursos();
        cargarCursosDisponibles();
        cargarMetodosPago();
        cargarCompras();

        document.getElementById('formCurso').addEventListener('submit', crearCurso);
    });

    async function cargarCursos() {
        const res = await fetch(`/backend/api/cursos.php?id_creador=${id_creador}`);
        const cursos = await res.json();
        const cont = document.getElementById('listaCursos');
        cont.innerHTML = '';
        cursos.forEach(curso => {
            cont.innerHTML += `
                <div>
                    <strong>${curso.titulo}</strong> - $${curso.precio}<br>
                    <img src="${curso.imagen}" width="150"><br>
                    <button onclick="eliminarCurso(${curso.id})">Eliminar</button>
                </div>
            `;
        });
    }

    async function eliminarCurso(id) {
        if (!confirm("¿Eliminar curso?")) return;
        await fetch(`/backend/api/cursos.php?id=${id}`, { method: "DELETE" });
        cargarCursos();
    }

    async function crearCurso(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const imagen = formData.get("imagen");

        // Subir imagen a Supabase manualmente
        const nombre = Date.now() + "_" + imagen.name;
        const upload = await fetch(`https://gvpkeksbujfdszckpuki.supabase.co/storage/v1/object/cursos/${nombre}`, {
            method: "POST",
            headers: {
                "Authorization": "Bearer <?= SUPABASE_SERVICE_ROLE_KEY ?>",
                "x-upsert": "true",
                "Content-Type": imagen.type
            },
            body: imagen
        });

        const imagenURL = `https://gvpkeksbujfdszckpuki.supabase.co/storage/v1/object/public/cursos/${nombre}`;

        // Crear curso con imagen
        const nuevoCurso = {
            titulo: formData.get("titulo"),
            instructor: formData.get("instructor"),
            descripcion: formData.get("descripcion"),
            precio: parseFloat(formData.get("precio")),
            imagen: imagenURL,
            id_creador,
            created_at: new Date().toISOString(),
            updated_at: new Date().toISOString()
        };

        await fetch('/backend/api/cursos.php', {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(nuevoCurso)
        });

        form.reset();
        cargarCursos();
    }

    async function cargarCursosDisponibles() {
        const resTodos = await fetch(`/backend/api/cursos.php`);
        const resCompras = await fetch(`/backend/api/compras.php?id_cliente=${id_creador}`);
        const cursos = await resTodos.json();
        const compras = await resCompras.json();
        const yaComprados = compras.map(c => c.id_curso);

        const disponibles = cursos.filter(c => !yaComprados.includes(c.id));
        const cont = document.getElementById('cursosDisponibles');
        cont.innerHTML = '';
        disponibles.forEach(curso => {
            cont.innerHTML += `
                <div>
                    <strong>${curso.titulo}</strong> - $${curso.precio}<br>
                    <img src="${curso.imagen}" width="150"><br>
                    <select id="pago_${curso.id}"></select>
                    <button onclick="comprarCurso(${curso.id})">Comprar</button>
                </div>
            `;
        });
    }

    let metodos = [];
    async function cargarMetodosPago() {
        const res = await fetch(`/backend/api/metodos_pago.php`);
        metodos = await res.json();
    }

    async function comprarCurso(idCurso) {
        const select = document.getElementById(`pago_${idCurso}`);
        if (!select.value) return alert("Selecciona método de pago");

        const cursoRes = await fetch(`/backend/api/cursos.php`);
        const cursos = await cursoRes.json();
        const curso = cursos.find(c => c.id === idCurso);

        await fetch('/backend/api/compras.php', {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                id_cliente: id_creador,
                id_curso: idCurso,
                id_metodo_pago: select.value,
                precio_pagado: curso.precio,
                fecha_compra: new Date().toISOString()
            })
        });

        cargarCursosDisponibles();
        cargarCompras();
    }

    async function cargarCompras() {
        const res = await fetch(`/backend/api/compras.php?id_cliente=${id_creador}`);
        const compras = await res.json();
        const cont = document.getElementById('listaCompras');
        cont.innerHTML = '';
        compras.forEach(c => {
            cont.innerHTML += `
                <div>
                    <strong>${c.cursos.titulo}</strong> - ${c.precio_pagado}<br>
                    Método: ${c.metodos_pago?.nombre ?? 'No especificado'}<br>
                    <img src="${c.cursos.imagen}" width="150">
                </div>
            `;
        });
    }
    </script>
</body>
</html>
