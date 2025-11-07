<?php
// manual.php
// Página de manual de usuario, con estilo consistente.

session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Manual del Usuario</title>
    <link rel="stylesheet" href="estilos.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<header>
    <h1>📘 Manual del Usuario</h1>
</header>

<div class="panel-estado">
    <p>Este sistema permite realizar reservas de espacios de la institución.</p>
    <ul>
        <li>Complete el formulario con sus datos personales.</li>
        <li>Seleccione fecha, horario y espacio.</li>
        <li>Evite duplicar reservas: el sistema valida automáticamente.</li>
        <li>Los administradores pueden editar o eliminar reservas.</li>
    </ul>
</div>

<div style="margin-top:20px; text-align:center;">
    <a href="index.php" class="manual-btn">Volver al inicio</a>
</div>
</body>
</html>
