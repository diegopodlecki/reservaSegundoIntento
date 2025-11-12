<?php
// admin_panel.php
// Panel de administración: listado de reservas con acciones de edición y borrado.
// Reutiliza funciones del CRUD y respeta el estilo visual del sitio.

session_start();
require_once __DIR__ . '/funciones.php';

// Solo administrador
if (empty($_SESSION['admin'])) {
    $_SESSION['error'] = 'Acceso restringido: inicie sesión como administrador.';
    header('Location: login.php');
    exit;
}

// Cargar reservas
try {
    $reservas = listarReservas();
    // Ordenar: primero pendientes (no expiradas), luego expiradas
    $pendientes = [];
    $expiradas = [];
    foreach ($reservas as $rr) {
        $durTmp = isset($rr['duracion']) ? (int)$rr['duracion'] : 60;
        $inicioTmp = strtotime($rr['fecha'] . ' ' . $rr['horario']);
        $finTmp = $inicioTmp + ($durTmp * 60);
        if (time() > $finTmp) {
            $expiradas[] = $rr;
        } else {
            $pendientes[] = $rr;
        }
    }
    $reservasOrdenadas = array_merge($pendientes, $expiradas);
} catch (Exception $e) {
    $reservas = [];
    $error_db = 'Error al cargar reservas: ' . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="estilo.css?v=2">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<header>
    <h1><i class="fas fa-tools"></i> Panel de Administración</h1>
    <div style="text-align:right;">
        Administrador | <a href="logout.php" style="color:white;">Cerrar sesión</a>
    </div>
    <div style="margin-top:8px;">
        <a class="manual-btn" href="index.php"><i class="fas fa-home"></i> Ir al inicio</a>
    </div>
 </header>

<?php if (isset($_SESSION['error'])): ?>
    <div class="msg-error"><?= htmlspecialchars($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['ok'])): ?>
    <div class="msg-ok"><?= htmlspecialchars($_SESSION['ok']) ?></div>
    <?php unset($_SESSION['ok']); ?>
<?php endif; ?>
<?php if (isset($error_db)): ?>
    <div class="msg-error"><?= htmlspecialchars($error_db) ?></div>
<?php endif; ?>

<h3>Reservas registradas</h3>
<!-- Panel de conflictos: se llena dinámicamente con JS cuando se detectan superposiciones -->
<section id="panel-conflictos" class="conflict-panel" hidden>
    <h4><i class="fas fa-exclamation-triangle"></i> Conflictos detectados</h4>
    <ul id="lista-conflictos"></ul>
    <div class="conflict-help">Sugerencias automáticas: ajuste la hora de inicio o cambie el espacio.</div>
 </section>

<table id="tabla-reservas" class="tabla-reservas">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>DNI</th>
            <th>Cargo</th>
            <th>Fecha</th>
            <th>Horario</th>
            <th>Espacio</th>
            <th>Duración</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($reservasOrdenadas ?? $reservas)): ?>
        <tr><td colspan="10" style="text-align:center;">No hay reservas cargadas.</td></tr>
    <?php else: ?>
        <?php foreach (($reservasOrdenadas ?? $reservas) as $r): ?>
            <tr>
                <td><?= (int)$r['id'] ?></td>
                <td><?= htmlspecialchars($r['nombre']) ?></td>
                <td><?= htmlspecialchars($r['apellido']) ?></td>
                <td><?= htmlspecialchars($r['dni']) ?></td>
                <td><?= htmlspecialchars($r['cargo']) ?></td>
                <td><?= htmlspecialchars(date('d/m/Y', strtotime($r['fecha']))) ?></td>
                <td><?= htmlspecialchars($r['horario']) ?></td>
                <td><?= htmlspecialchars($r['espacio']) ?></td>
                <?php
                    $dur = isset($r['duracion']) ? (int)$r['duracion'] : 60;
                    $inicioTs = strtotime($r['fecha'] . ' ' . $r['horario']);
                    $finTs = $inicioTs + ($dur * 60);
                    $expirada = (time() > $finTs);
                ?>
                <td>
                    <?= (int)$dur ?> min
                    <?php if ($expirada): ?>
                        <span class="badge-expirado">Expiró</span>
                    <?php endif; ?>
                </td>
                <td>
                    <form method="post" action="admin.php" style="display:inline;">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                        <button type="submit" class="icon-btn" title="Eliminar" onclick="return confirm('¿Eliminar la reserva #<?= (int)$r['id'] ?>?')"><i class="fas fa-trash"></i></button>
                    </form>
                    <form method="get" action="editar.php" style="display:inline;">
                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                        <button type="submit" class="icon-btn" title="Editar"><i class="fas fa-edit"></i></button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

</body>
<script>
// Detección de conflictos en la tabla de reservas y sugerencias básicas de resolución
document.addEventListener('DOMContentLoaded', function() {
    const tabla = document.getElementById('tabla-reservas');
    if (!tabla) return;

    const filas = Array.from(tabla.rows);
    const conflictos = [];

    // Recorremos pares de filas evitando duplicados (j > i)
    for (let i = 1; i < filas.length; i++) { // saltar header (0)
        const f1 = filas[i];
        const fecha1 = f1.cells[5]?.textContent?.trim();
        const horario1 = f1.cells[6]?.textContent?.trim();
        const espacio1 = f1.cells[7]?.textContent?.trim(); // columna 7 = Espacio
        const duracion1 = parseInt(f1.cells[8]?.textContent) || 60; // columna 8 = Duración
        const id1 = f1.cells[0]?.textContent?.trim();

        if (!fecha1 || !horario1 || !espacio1) continue;
        const [h1, m1] = horario1.split(':').map(Number);
        const inicio1 = h1 * 60 + m1;
        const fin1 = inicio1 + duracion1;

        for (let j = i + 1; j < filas.length; j++) {
            const f2 = filas[j];
            const fecha2 = f2.cells[5]?.textContent?.trim();
            const horario2 = f2.cells[6]?.textContent?.trim();
            const espacio2 = f2.cells[7]?.textContent?.trim();
            const duracion2 = parseInt(f2.cells[8]?.textContent) || 60;
            const id2 = f2.cells[0]?.textContent?.trim();

            if (!fecha2 || !horario2 || !espacio2) continue;
            if (fecha1 !== fecha2 || espacio1 !== espacio2) continue; // solo mismo día y mismo espacio

            const [h2, m2] = horario2.split(':').map(Number);
            const inicio2 = h2 * 60 + m2;
            const fin2 = inicio2 + duracion2;

            const seSuperponen = inicio1 < fin2 && inicio2 < fin1;
            if (seSuperponen) {
                // Determinar cuál empieza antes para sugerencia
                const primero = inicio1 <= inicio2 ? {id: id1, inicio: inicio1, fin: fin1, horario: horario1, fila: f1} : {id: id2, inicio: inicio2, fin: fin2, horario: horario2, fila: f2};
                const segundo = primero.id === id1 ? {id: id2, inicio: inicio2, fin: fin2, horario: horario2, fila: f2} : {id: id1, inicio: inicio1, fin: fin1, horario: horario1, fila: f1};

                // Sugerencia: mover inicio del segundo al fin del primero
                const nuevaHoraMin = primero.fin;
                const nuevaHora = `${String(Math.floor(nuevaHoraMin/60)).padStart(2,'0')}:${String(nuevaHoraMin%60).padStart(2,'0')}`;

                conflictos.push({
                    fecha: fecha1,
                    espacio: espacio1,
                    idA: id1,
                    horarioA: horario1,
                    idB: id2,
                    horarioB: horario2,
                    sugerencia: `Mover inicio de #${segundo.id} a ${nuevaHora} (tras #${primero.id})`
                });

                // Resaltar filas involucradas en la tabla
                primero.fila.classList.add('row-conflict');
                segundo.fila.classList.add('row-conflict');

                // Agregar badge "Conflicto" en la celda de Duración para ambos
                const addBadge = (fila) => {
                    const celdaDur = fila.cells[8];
                    if (celdaDur && !celdaDur.querySelector('.badge-conflicto')) {
                        const badge = document.createElement('span');
                        badge.className = 'badge-conflicto';
                        badge.textContent = 'Conflicto';
                        celdaDur.appendChild(badge);
                    }
                };
                addBadge(primero.fila);
                addBadge(segundo.fila);
            }
        }
    }

    if (conflictos.length > 0) {
        const panel = document.getElementById('panel-conflictos');
        const lista = document.getElementById('lista-conflictos');
        panel.hidden = false;
        lista.innerHTML = '';

        conflictos.forEach(c => {
            const li = document.createElement('li');
            li.className = 'conflict-item';
            li.innerHTML = `
                <div>
                    <strong>⚠️ ${c.fecha}</strong> — espacio <strong>${c.espacio}</strong>:
                    reservas <strong>#${c.idA}</strong> (${c.horarioA}) y <strong>#${c.idB}</strong> (${c.horarioB}) se superponen.
                </div>
                <div class="conflict-suggestion">Sugerencia: ${c.sugerencia}</div>
                <div class="conflict-actions">
                    <a class="manual-btn" href="editar.php?id=${c.idA}"><i class="fas fa-edit"></i> Editar #${c.idA}</a>
                    <a class="manual-btn" href="editar.php?id=${c.idB}"><i class="fas fa-edit"></i> Editar #${c.idB}</a>
                </div>
            `;
            lista.appendChild(li);
        });
    }
});
</script>
</body>
</html>