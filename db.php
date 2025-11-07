<?php
// db.php
// Conexión a SQLite y creación de la tabla de reservas.
// Incluye manejo de errores y configuración recomendada.

ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    $dbPath = __DIR__ . '/reservas.db';
    $db = new PDO('sqlite:' . $dbPath);

    // Activar excepciones para capturar errores
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Configuraciones opcionales de SQLite
    $db->exec("PRAGMA foreign_keys = ON;");
    $db->exec("PRAGMA journal_mode = WAL;");
    $db->exec("PRAGMA synchronous = NORMAL;");

    // Crear tabla si no existe (incluye columna 'duracion' en minutos)
    $db->exec("
        CREATE TABLE IF NOT EXISTS reservas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL,
            apellido TEXT NOT NULL,
            dni TEXT NOT NULL,
            cargo TEXT NOT NULL,
            fecha TEXT NOT NULL,
            horario TEXT NOT NULL,
            espacio TEXT NOT NULL,
            duracion INTEGER NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Intentar migración suave: agregar 'duracion' si la tabla ya existía sin esa columna
    try {
        $db->exec("ALTER TABLE reservas ADD COLUMN duracion INTEGER NOT NULL DEFAULT 60");
    } catch (Exception $ignored) {
        // Si falla, probablemente la columna ya existe; se ignora para mantener compatibilidad
    }
    
    // Crear índices para mejorar el rendimiento
    $db->exec("CREATE INDEX IF NOT EXISTS idx_reservas_fecha ON reservas(fecha)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_reservas_fecha_horario_espacio ON reservas(fecha, horario, espacio)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_reservas_dni ON reservas(dni)");
} catch (Exception $e) {
    die('Error conectando a la base de datos: ' . htmlspecialchars($e->getMessage()));
}
