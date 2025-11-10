<?php
// manual.php
// Manual del Usuario con contenido ampliado y cajas para capturas.
// Este archivo está pensado para explicar la aplicación paso a paso.

session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Manual del Usuario</title>
    <!-- Hoja de estilos principal -->
    <link rel="stylesheet" href="estilo.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<header>
    <h1>📘 Manual del Usuario</h1>
</header>

<!-- Introducción general -->
<div class="panel-estado">
    <p>Este sistema permite realizar reservas de espacios de la institución de manera sencilla y ordenada.</p>
    <ul>
        <li>Completar datos personales y especificar fecha, horario y duración.</li>
        <li>Seleccionar el espacio deseado (ej.: Auditorio, Sala de reuniones).</li>
        <li>Evitar solapamientos: el sistema detecta reservas duplicadas por fecha, horario y espacio.</li>
        <li>Los administradores pueden editar o eliminar reservas existentes.</li>
    </ul>
</div>

<!-- Sección Paso a Paso -->
<section>
    <h2>Pasos para crear una reserva</h2>
    <ol>
        <li>Ir a la página de inicio y ubicar la sección “Nueva reserva”.</li>
        <li>Completar <strong>Nombre</strong>, <strong>Apellido</strong> y <strong>DNI</strong>.</li>
        <li>Seleccionar <strong>Cargo</strong> y luego la <strong>Fecha</strong>, <strong>Horario</strong> y <strong>Duración (minutos)</strong>.</li>
        <li>Elegir el <strong>Espacio</strong> a reservar y presionar <strong>Reservar</strong>.</li>
        <li>Verificar el mensaje de confirmación y, si es admin, revisar las acciones disponibles.</li>
    </ol>
</section>

<!-- Cajas para capturas de pantalla (placeholders) -->
<section>
    <h2>Imágenes de referencia (arrastre/pegue capturas)</h2>
    <div class="screenshot-grid">
        <figure class="screenshot-box" aria-label="Captura de Pantalla - Inicio">
            <figcaption>Pantalla de Inicio</figcaption>
            <!-- Reemplace por imagen/captura -->
            <!-- <img src="capturas/inicio.png" alt="Pantalla de inicio del sistema"> -->
            <div class="screenshot-placeholder">Coloque aquí la captura de la página de inicio</div>
        </figure>

        <figure class="screenshot-box" aria-label="Captura de Pantalla - Formulario">
            <figcaption>Formulario de Nueva Reserva</figcaption>
            <!-- <img src="capturas/formulario.png" alt="Formulario de nueva reserva"> -->
            <div class="screenshot-placeholder">Coloque aquí la captura del formulario</div>
        </figure>

        <figure class="screenshot-box" aria-label="Captura de Pantalla - Edición">
            <figcaption>Edición de Reserva (Admin)</figcaption>
            <!-- <img src="capturas/edicion.png" alt="Pantalla de edición de reserva"> -->
            <div class="screenshot-placeholder">Coloque aquí la captura de la edición</div>
        </figure>

        <figure class="screenshot-box" aria-label="Captura de Pantalla - Listado">
            <figcaption>Listado de Reservas</figcaption>
            <!-- <img src="capturas/listado.png" alt="Listado de reservas realizadas"> -->
            <div class="screenshot-placeholder">Coloque aquí la captura del listado</div>
        </figure>
    </div>
</section>

<!-- Consejos útiles -->
<section>
    <h2>Consejos y buenas prácticas</h2>
    <ul>
        <li>Verifique la fecha y el horario antes de confirmar la reserva.</li>
        <li>Use una duración realista (por ejemplo, 60–120 minutos).</li>
        <li>Si necesita modificar una reserva, use el ícono de edición (solo admin).
        </li>
        <li>Para cancelar una reserva, use el ícono de eliminar (solo admin),
            confirme el diálogo emergente y verifique el mensaje de éxito.</li>
    </ul>
</section>

<!-- Preguntas frecuentes -->
<section>
    <h2>Preguntas frecuentes (FAQ)</h2>
    <details>
        <summary>¿Puedo reservar más de un espacio a la misma hora?</summary>
        <p>El sistema evita duplicados por <em>fecha + horario + espacio</em>. Puede reservar distintos espacios en el mismo horario, pero no repetir el mismo.</p>
    </details>
    <details>
        <summary>¿Qué es la “Duración (minutos)”?</summary>
        <p>Es el tiempo estimado de uso del espacio. Facilita la organización y evita solapamientos más largos de lo necesario.</p>
    </details>
    <details>
        <summary>¿Quién puede editar o eliminar reservas?</summary>
        <p>Solo usuarios con perfil de administrador. En la vista de listado, aparecen botones de edición y eliminación.</p>
    </details>
</section>

<div style="margin-top:20px; text-align:center;">
    <a href="index.php" class="manual-btn">Volver al inicio</a>
    <!-- Enlace al inicio para revisar cambios desde la pantalla principal -->
    <!-- Puede reemplazarse por un botón si se prefiere -->
</div>
</body>
</html>
