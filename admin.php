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
    // Verificación explícita de solapamiento con detalle
    $det = encontrarSolapamientoReserva($fecha, $horario, $duracion, $espacio);
    if ($det) {
        $_SESSION['error'] = sprintf(
            "Conflicto con la reserva #%d (%s, %d min). Se superpone entre %02d:%02d y %02d:%02d.",
            $det['id'], $det['horario'], $det['duracion'],
            intdiv($det['inicio_nuevo'], 60), $det['inicio_nuevo'] % 60,
            intdiv($det['fin_nuevo'], 60), $det['fin_nuevo'] % 60
        );
    } else if (insertarReserva($data)) {
        $_SESSION['ok'] = "Reserva creada correctamente.";
    } else {
        $_SESSION['error'] = "Conflicto: se superpone con otra reserva en el mismo espacio y fecha.";
    }
    header("Location: index.php");
    exit;
}

if ($accion === 'actualizar') {
    // Solo administrador puede actualizar
    if (empty($_SESSION['admin'])) {
        $_SESSION['error'] = "Acceso restringido: solo administradores pueden actualizar reservas.";
        header("Location: index.php");
        exit;
    }
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
        $_SESSION['error'] = "Conflicto: se superpone con otra reserva en el mismo espacio y fecha.";
    }
    header("Location: index.php");
    exit;
}

if ($accion === 'eliminar') {
    // Solo administrador puede eliminar
    if (empty($_SESSION['admin'])) {
        $_SESSION['error'] = "Acceso restringido: solo administradores pueden eliminar reservas.";
        header("Location: index.php");
        exit;
    }
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
