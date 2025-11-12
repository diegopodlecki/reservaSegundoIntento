<?php
// index.php
// Página principal: muestra panel de estado, formulario de nueva reserva y listado.
// NOTA: Este proyecto es pedagógico, se prioriza código claro y comentado.

session_start();
require_once __DIR__ . '/funciones.php';

// Obtener datos de la base de datos (reservas y métricas)
try {
    $reservas = listarReservas();
    $totalReservas = contarReservas();
    $hoy = date("Y-m-d");
    $reservasHoy = contarReservasPorFecha($hoy);
    $conflictos = detectarConflictos();
    // Construir conjunto de IDs involucrados en conflictos
    $idsConflicto = [];
    foreach ($conflictos as $c) {
        if (isset($c['idA'])) { $idsConflicto[(int)$c['idA']] = true; }
        if (isset($c['idB'])) { $idsConflicto[(int)$c['idB']] = true; }
    }
    // Reordenar: primero reservas pendientes (no expiradas), luego expiradas
    $pendientes = [];
    $expiradasList = [];
    foreach ($reservas as $rr) {
        $durTmp = isset($rr['duracion']) ? (int)$rr['duracion'] : 60;
        $inicioTmp = strtotime($rr['fecha'] . ' ' . $rr['horario']);
        $finTmp = $inicioTmp + ($durTmp * 60);
        if (time() > $finTmp) {
            $expiradasList[] = $rr;
        } else {
            $pendientes[] = $rr;
        }
    }
    $reservas = array_merge($pendientes, $expiradasList);
    // Construir conjunto de IDs involucrados en conflictos para resaltar en el listado
    $idsConflicto = [];
    foreach ($conflictos as $c) {
        if (isset($c['idA'])) { $idsConflicto[(int)$c['idA']] = true; }
        if (isset($c['idB'])) { $idsConflicto[(int)$c['idB']] = true; }
    }
} catch (Exception $e) {
    // Si hay error de BD, mostramos mensaje y evitamos romper la vista
    $reservas = [];
    $totalReservas = 0;
    $reservasHoy = 0;
    $conflictos = [];
    $error_db = "Error al cargar reservas: " . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Reservas</title>
    <link rel="stylesheet" href="estilo.css?v=2"><!-- estilos visuales del sistema -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<header>
    <h1>Sistema de Reservas</h1>
    <div style="text-align:right;">
        <?php if (!empty($_SESSION['admin'])): ?>
            Administrador | <a href="logout.php" style="color:white;">Cerrar sesión</a>
        <?php else: ?>
            <a href="login.php" style="color:white;">Iniciar sesión (Admin)</a>
        <?php endif; ?>
    </div>
</header>

<a href="manual.php" class="manual-btn">📘 Manual del Usuario</a>

<?php if (isset($error_db)): ?>
    <div class="msg-error"><?= htmlspecialchars($error_db) ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="msg-error"><?= htmlspecialchars($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['ok'])): ?>
    <div class="msg-ok"><?= htmlspecialchars($_SESSION['ok']) ?></div>
    <?php unset($_SESSION['ok']); ?>
<?php endif; ?>

<!-- Panel de estado: métricas rápidas del sistema -->
<div class="panel-estado">
    <strong>Panel de estado:</strong><br>
    📌 Total de reservas: <?= $totalReservas ?><br>
    📅 Reservas para hoy (<?= $hoy ?>): <?= $reservasHoy ?><br>
    ⚠️ Conflictos detectados: <?= count($conflictos) ?>
</div>

<!-- Formulario: creación de nueva reserva -->
<h3>Nueva reserva</h3>
<form method="post" action="admin.php" class="form-grid">
    <input type="hidden" name="accion" value="insertar">

    <div class="campo">
        <label>Nombre:</label>
        <input type="text" name="nombre" required>
    </div>
    <div class="campo">
        <label>Apellido:</label>
        <input type="text" name="apellido" required>
    </div>
    <div class="campo">
        <label>DNI:</label>
        <input type="text" name="dni" required>
    </div>
    <div class="campo">
        <label>Cargo:</label>
        <select name="cargo" required>
            <option>Estudiante</option>
            <option>Docente</option>
            <option>Preceptor</option>
            <option>Administrador</option>
            <option>Directivos</option>
        </select>
    </div>
    <div class="campo">
        <label>Fecha:</label>
        <input type="date" name="fecha" required>
    </div>
    <div class="campo">
        <label>Horario:</label>
        <input type="time" name="horario" required>
    </div>
    <div class="campo">
        <label>Duración (minutos):</label>
        <input type="number" name="duracion" min="10" max="480" step="5" required>
    </div>
    <div class="campo">
        <label>Espacio:</label>
        <select name="espacio" required>
            <option>Sala de reuniones</option>
            <option>Auditorio</option>
            <option>Laboratorio de informática</option>
        </select>
    </div>
    <div style="flex:1 1 100%; text-align:center;">
        <button type="submit">Reservar</button>
    </div>
</form>

<hr>

<!-- Listado: reservas existentes (si admin, muestra acciones) -->
<h3>Reservas realizadas</h3>
<ul>
    <?php foreach ($reservas as $r): ?>
        <?php
            // Calcular si la reserva ya expiró (fecha+hora+duración < ahora)
            $dur = isset($r['duracion']) ? (int)$r['duracion'] : 60;
            $inicioTs = strtotime($r['fecha'] . ' ' . $r['horario']);
            $finTs = $inicioTs + ($dur * 60);
            $expirada = (time() > $finTs);
        ?>
        <li class="<?= $expirada ? 'expirado' : '' ?> <?= isset($idsConflicto[(int)$r['id']]) ? 'en-conflicto' : '' ?>">
            <?= htmlspecialchars($r['nombre'].' '.$r['apellido']) ?>
            — <?= htmlspecialchars($r['espacio']) ?>
            (<?= htmlspecialchars(date('d/m/Y', strtotime($r['fecha'])) . ' ' . $r['horario']) ?>, <?= (int)$dur ?> min)
            <?php if ($expirada): ?>
                <span class="badge-expirado">Expiró</span>
            <?php endif; ?>

            <?php if (isset($idsConflicto[(int)$r['id']])): ?>
                <span class="badge-conflicto">Conflicto</span>
            <?php endif; ?>

            <?php if (!empty($_SESSION['admin'])): ?>
                <div class="reserva-acciones">
                    <form method="post" action="admin.php" style="display:inline;">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                        <button type="submit" class="icon-btn" onclick="return confirm('¿Está seguro de eliminar esta reserva?')"><i class="fas fa-trash"></i></button>
                    </form>
                    <form method="get" action="editar.php" style="display:inline;">
                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                        <button type="submit" class="icon-btn"><i class="fas fa-edit"></i></button>
                    </form>
                </div>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>
</body>
</html>
