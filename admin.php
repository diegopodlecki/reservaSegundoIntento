<?php
// admin.php
// Controlador de acciones CRUD: insertar, actualizar y eliminar reservas.

session_start();
require_once __DIR__ . '/funciones.php';
// admin.php: controlador de acciones del formulario (crear, actualizar, eliminar)
// Proyecto pedagógico: sin CSRF para simplificar explicación del flujo

// Activar errores para depuración
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Verificamos qué acción se solicitó
$accion = $_POST['accion'] ?? $_GET['accion'] ?? null;

if ($accion === 'insertar') {
    // Insertar nueva reserva con validación básica
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $dni = trim($_POST['dni'] ?? '');
    $cargo = $_POST['cargo'] ?? '';
    $fecha = $_POST['fecha'] ?? '';
    $horario = $_POST['horario'] ?? '';
    $espacio = $_POST['espacio'] ?? '';
    $duracion = (int)($_POST['duracion'] ?? 0); // minutos
    
    // Validación básica
    if (empty($nombre) || empty($apellido) || empty($dni) || 
        empty($cargo) || empty($fecha) || empty($horario) || empty($espacio)) {
        $_SESSION['error'] = "Todos los campos son obligatorios.";
        header("Location: index.php");
        exit;
    }
    
    // Validar DNI (solo números)
    if (!preg_match('/^\d{7,8}$/', $dni)) {
        $_SESSION['error'] = "DNI inválido. Debe tener 7-8 dígitos.";
        header("Location: index.php");
        exit;
    }

    // Validar duración (10 a 480 min)
    if ($duracion < 10 || $duracion > 480) {
        $_SESSION['error'] = "Duración inválida (rango: 10 a 480 minutos).";
        header("Location: index.php");
        exit;
    }
    
    $data = [$nombre, $apellido, $dni, $cargo, $fecha, $horario, $espacio, $duracion];
    if (insertarReserva($data)) {
        $_SESSION['ok'] = "Reserva creada correctamente.";
    } else {
        $_SESSION['error'] = "Ya existe una reserva en ese espacio, fecha y horario.";
    }
    header("Location: index.php");
    exit;
}

if ($accion === 'actualizar') {
    // Actualizar reserva existente
    $data = [
        $_POST['nombre'] ?? '',
        $_POST['apellido'] ?? '',
        $_POST['dni'] ?? '',
        $_POST['cargo'] ?? '',
        $_POST['fecha'] ?? '',
        $_POST['horario'] ?? '',
        $_POST['espacio'] ?? '',
        (int)($_POST['duracion'] ?? 0),
        (int)($_POST['id'] ?? 0)
    ];
    // Validación rápida de duración
    if ($data[7] < 10 || $data[7] > 480) {
        $_SESSION['error'] = "Duración inválida al actualizar (10-480 minutos).";
        header("Location: index.php");
        exit;
    }
    if (actualizarReserva($data)) {
        $_SESSION['ok'] = "Reserva actualizada correctamente.";
    } else {
        $_SESSION['error'] = "Conflicto: ya existe otra reserva en ese espacio, fecha y horario.";
    }
    header("Location: index.php");
    exit;
}

if ($accion === 'eliminar') {
    // Eliminar reserva
    $id = (int)($_POST['id'] ?? 0);
    if (eliminarReserva($id)) {
        $_SESSION['ok'] = "Reserva eliminada.";
    } else {
        $_SESSION['error'] = "No se pudo eliminar la reserva.";
    }
    header("Location: index.php");
    exit;
}

// Si no hay acción válida
$_SESSION['error'] = "Acción no válida.";
header("Location: index.php");
exit;
