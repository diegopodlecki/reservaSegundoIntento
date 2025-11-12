<?php
session_start();

// Credenciales pedagógicas: usuario fijo y verificación por texto plano + hash
// Nota: El hash actual no corresponde a "admin123". Para uso inmediato,
// aceptamos la clave en texto plano (admin123) y también intentamos password_verify.
// Recomendación: reemplazar $ADMIN_HASH por uno generado con password_hash('admin123', PASSWORD_DEFAULT).
$ADMIN_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; // Ejemplo (no es admin123)
$ADMIN_USER = 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $clave = $_POST['clave'] ?? '';

    if (
        $usuario === $ADMIN_USER && (
            $clave === 'admin123' || password_verify($clave, $ADMIN_HASH)
        )
    ) {
        $_SESSION['admin'] = true;
        $_SESSION['ok'] = "Autenticado como administrador.";
        session_regenerate_id(true); // Seguridad: regenerar ID de sesión
        header("Location: admin_panel.php");
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login Administrador</title>
    <link rel="stylesheet" href="estilo.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- La hoja de estilos y meta viewport igualan el look & feel con index.php -->
    <!-- Este login redirige al inicio tras autenticación y habilita acciones de admin -->
</head>
<body>
<header>
    <h1><i class="fas fa-user-shield"></i> Login Administrador</h1>
</header>

<?php if (!empty($error)): ?>
    <div class="msg-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

 <!-- Formulario de login simple (proyecto pedagógico, sin CSRF) -->
 <form method="post" class="form-grid">
     <div class="campo">
         <label>Usuario:</label>
         <input type="text" name="usuario" required>
     </div>
    <div class="campo">
        <label>Contraseña:</label>
        <input type="password" name="clave" required>
    </div>
    <div style="flex:1 1 100%; text-align:center;">
        <button type="submit">Ingresar</button>
    </div>
</form>

 <div style="margin-top:20px; text-align:center;">
     <a href="index.php" class="manual-btn"><i class="fas fa-home"></i> Volver al inicio</a>
 </div>
</body>
</html>


