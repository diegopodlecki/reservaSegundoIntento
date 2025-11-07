<?php
session_start();

// Credenciales seguras con hash (admin/admin123)
$ADMIN_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; // admin123
$ADMIN_USER = 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $clave = $_POST['clave'] ?? '';

    if ($usuario === $ADMIN_USER && password_verify($clave, $ADMIN_HASH)) {
        $_SESSION['admin'] = true;
        session_regenerate_id(true); // Seguridad: regenerar ID de sesión
        header("Location: index.php");
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
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<header><h1>Login Administrador</h1></header>

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
    <a href="index.php" class="manual-btn">Volver al inicio</a>
</div>
</body>
</html>


