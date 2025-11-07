<?php
// funciones.php
// CRUD + métricas de estado para el sistema de reservas.
// Requiere db.php para $db (PDO SQLite).

require_once __DIR__ . '/db.php';

/**
 * NOTAS IMPORTANTES:
 * - Todas las funciones usan "global $db" (instancia PDO) que viene de db.php.
 * - Asegúrate que db.php define $db correctamente y NO devuelve/termina antes.
 * - Las funciones que reciben arrays (insertar/actualizar) esperan orden estricto:
 *   [nombre, apellido, dni, cargo, fecha, horario, espacio, duracion] (+ id al final en actualizar).
 */

/* =======================
   UTILIDAD: duplicados
   ======================= */

/**
 * Verifica si existe una reserva duplicada (mismo fecha + horario + espacio).
 * Si $idExcluir se pasa, excluye ese ID (útil al actualizar).
 */
function existeReserva(string $fecha, string $horario, string $espacio, ?int $idExcluir = null): bool {
    global $db;

    if (!$db instanceof PDO) {
        throw new RuntimeException('Conexión a base de datos no inicializada ($db).');
    }

    if ($idExcluir !== null) {
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM reservas
             WHERE fecha = ? AND horario = ? AND espacio = ? AND id <> ?"
        );
        $stmt->execute([$fecha, $horario, $espacio, $idExcluir]);
    } else {
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM reservas
             WHERE fecha = ? AND horario = ? AND espacio = ?"
        );
        $stmt->execute([$fecha, $horario, $espacio]);
    }

    return (int)$stmt->fetchColumn() > 0;
}

/* ============
   CREATE
   ============ */

/**
 * Inserta una nueva reserva.
 * $data debe ser: [nombre, apellido, dni, cargo, fecha, horario, espacio, duracion]
 */
function insertarReserva(array $data): bool {
    global $db;

    if (!$db instanceof PDO) {
        throw new RuntimeException('Conexión a base de datos no inicializada ($db).');
    }

    // Validación mínima de longitud del array
    if (count($data) < 8) {
        throw new InvalidArgumentException('insertarReserva: $data incompleto (se esperan 8 elementos).');
    }

    // Sanitización de datos
    $data[0] = htmlspecialchars(trim($data[0]), ENT_QUOTES, 'UTF-8'); // nombre
    $data[1] = htmlspecialchars(trim($data[1]), ENT_QUOTES, 'UTF-8'); // apellido
    $data[2] = preg_replace('/\D/', '', $data[2]); // dni: solo números
    $data[3] = htmlspecialchars(trim($data[3]), ENT_QUOTES, 'UTF-8'); // cargo
    $data[4] = htmlspecialchars(trim($data[4]), ENT_QUOTES, 'UTF-8'); // fecha
    $data[5] = htmlspecialchars(trim($data[5]), ENT_QUOTES, 'UTF-8'); // horario
    $data[6] = htmlspecialchars(trim($data[6]), ENT_QUOTES, 'UTF-8'); // espacio
    $data[7] = (int)$data[7]; // duración en minutos

    // Validar formato de fecha
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data[4])) {
        throw new InvalidArgumentException('Formato de fecha inválido.');
    }

    // Validar formato de hora
    if (!preg_match('/^\d{2}:\d{2}$/', $data[5])) {
        throw new InvalidArgumentException('Formato de horario inválido.');
    }

    // Validar duplicado
    if (existeReserva($data[4], $data[5], $data[6])) {
        return false;
    }

    $stmt = $db->prepare("
        INSERT INTO reservas (nombre, apellido, dni, cargo, fecha, horario, espacio, duracion)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    return $stmt->execute($data);
}

/* ============
   UPDATE
   ============ */

/**
 * Actualiza una reserva existente.
 * $data debe ser: [nombre, apellido, dni, cargo, fecha, horario, espacio, duracion, id]
 */
function actualizarReserva(array $data): bool {
    global $db;

    if (!$db instanceof PDO) {
        throw new RuntimeException('Conexión a base de datos no inicializada ($db).');
    }

    // Validación mínima
    if (count($data) < 9) {
        throw new InvalidArgumentException('actualizarReserva: $data incompleto (se esperan 9 elementos).');
    }

    $id = (int)$data[8];

    // Evitar duplicado distinto del mismo ID
    if (existeReserva($data[4], $data[5], $data[6], $id)) {
        return false;
    }

    $stmt = $db->prepare("
        UPDATE reservas
        SET nombre = ?, apellido = ?, dni = ?, cargo = ?, fecha = ?, horario = ?, espacio = ?, duracion = ?
        WHERE id = ?
    ");

    return $stmt->execute($data);
}

/* ============
   DELETE
   ============ */

function eliminarReserva(int $id): bool {
    global $db;

    if (!$db instanceof PDO) {
        throw new RuntimeException('Conexión a base de datos no inicializada ($db).');
    }

    $stmt = $db->prepare("DELETE FROM reservas WHERE id = ?");
    return $stmt->execute([$id]);
}

/* ============
   READ
   ============ */

function listarReservas(): array {
    global $db;

    if (!$db instanceof PDO) {
        throw new RuntimeException('Conexión a base de datos no inicializada ($db).');
    }

    $stmt = $db->query("SELECT * FROM reservas ORDER BY id DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function obtenerReservaPorId(int $id) {
    global $db;

    if (!$db instanceof PDO) {
        throw new RuntimeException('Conexión a base de datos no inicializada ($db).');
    }

    $stmt = $db->prepare("SELECT * FROM reservas WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/* ===========================
   MÉTRICAS PARA EL PANEL
   =========================== */

function contarReservas(): int {
    global $db;

    if (!$db instanceof PDO) {
        throw new RuntimeException('Conexión a base de datos no inicializada ($db).');
    }

    $stmt = $db->query("SELECT COUNT(*) FROM reservas");
    return (int)$stmt->fetchColumn();
}

function contarReservasPorFecha(string $fecha): int {
    global $db;

    if (!$db instanceof PDO) {
        throw new RuntimeException('Conexión a base de datos no inicializada ($db).');
    }

    $stmt = $db->prepare("SELECT COUNT(*) FROM reservas WHERE fecha = ?");
    $stmt->execute([$fecha]);
    return (int)$stmt->fetchColumn();
}

function detectarConflictos(): array {
    global $db;

    if (!$db instanceof PDO) {
        throw new RuntimeException('Conexión a base de datos no inicializada ($db).');
    }

    $sql = "
        SELECT fecha, horario, espacio, COUNT(*) AS cantidad
        FROM reservas
        GROUP BY fecha, horario, espacio
        HAVING cantidad > 1
    ";
    $stmt = $db->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
