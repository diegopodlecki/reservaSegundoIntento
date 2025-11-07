<?php
session_start();

// Regenerar ID de sesión antes de destruirla (seguridad)
session_regenerate_id(true);

// Destruir todas las variables de sesión
$_SESSION = array();

// Destruir la sesión
session_destroy();

// Redirigir al inicio
header("Location: index.php");
exit;
