<?php
// editar.php
// Página de edición de reservas (solo administrador).

session_start();
require_once __DIR__ . '/funciones.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Solo administrador
if (empty($_SESSION['admin'])) {
    echo "Acceso restringido. Solo el administrador puede editar reservas.";
    exit;
}

// ID de la reserva
$id = (int)($_GET['id'] ?? 0);
$reserva = obtenerReservaPorId($id);
if (!$reserva) {
    echo "Reserva no encontrada.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Reserva</title>
    <link rel="stylesheet" href="estilos.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<header>
    <h1>Editar Reserva</h1>
</header>

<!-- Formulario de edición: actualiza la reserva seleccionada -->
<form method="post" action="admin.php" class="form-grid">
    <input type="hidden" name="accion" value="actualizar">
    <input type="hidden" name="id" value="<?= (int)$reserva['id'] ?>">

    <div class="campo">
        <label>Nombre:</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($reserva['nombre']) ?>" required>
    </div>

    <div class="campo">
        <label>Apellido:</label>
        <input type="text" name="apellido" value="<?= htmlspecialchars($reserva['apellido']) ?>" required>
    </div>

    <div class="campo">
        <label>DNI:</label>
        <input type="text" name="dni" value="<?= htmlspecialchars($reserva['dni']) ?>" required>
    </div>

    <div class="campo">
        <label>Cargo:</label>
        <select name="cargo" required>
            <?php
            $cargos = ["Estudiante","Docente","Preceptor","Administrador","Directivos"];
            foreach ($cargos as $c) {
                $sel = ($reserva['cargo'] === $c) ? 'selected' : '';
                echo "<option $sel>" . htmlspecialchars($c) . "</option>";
            }
            ?>
        </select>
    </div>

    <div class="campo">
        <label>Fecha:</label>
        <input type="date" name="fecha" value="<?= htmlspecialchars($reserva['fecha']) ?>" required>
    </div>

    <div class="campo">
        <label>Horario:</label>
        <input type="time" name="horario" value="<?= htmlspecialchars($reserva['horario']) ?>" required>
    </div>
    <div class="campo">
        <label>Duración (minutos):</label>
        <input type="number" name="duracion" value="<?= (int)$reserva['duracion'] ?>" min="10" max="480" step="5" required>
    </div>

    <div class="campo">
        <label>Espacio:</label>
        <select name="espacio" required>
            <?php
            $espacios = ["Sala de reuniones","Auditorio","Laboratorio de informática","Sala multiusos","Gimnasio","Carro de netbooks"];
            foreach ($espacios as $e) {
                $sel = ($reserva['espacio'] === $e) ? 'selected' : '';
                echo "<option $sel>" . htmlspecialchars($e) . "</option>";
            }
            ?>
        </select>
    </div>

    <div style="flex:1 1 100%; text-align:center;">
        <button type="submit"><i class="fas fa-save"></i> Guardar cambios</button>
    </div>
</form>

<div style="margin-top:20px; text-align:center;">
    <a href="index.php" class="manual-btn"><i class="fas fa-home"></i> Volver al inicio</a>
</div>
</body>
</html>
