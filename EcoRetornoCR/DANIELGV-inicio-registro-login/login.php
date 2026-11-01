<?php

session_start();
require_once "funciones.php";




$mensaje = $_SESSION["mensaje"] ?? null;
unset($_SESSION["mensaje"]);




$usuarioActivo = $_SESSION["usuario"] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoRetorno CR - Iniciar sesión</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<div class="contenedor">

    <header>
        <h1>EcoRetorno CR</h1>
        <p>¿Reciclaje quen nos devuelve dinero?, incentivo de cada municipalidad!!</p>
    </header>

    <nav>
        <ul>
            <li><a href="index.php">Inicio</a></li>
            <li><a href="registro.php">Registrarse</a></li>
            <li><a href="login.php">Iniciar sesión</a></li>
        </ul>
    </nav>

    <?php if ($mensaje): ?>
        <div class="alerta alerta-<?= htmlspecialchars($mensaje["tipo"]) ?>">
            <?= htmlspecialchars($mensaje["texto"]) ?>
        </div>
    <?php endif; ?>

    <?php if ($usuarioActivo): ?>

        <section class="tarjeta">
            <h2>Ya tiene una sesión activa</h2>
            <p>Ingresó como <?= htmlspecialchars($usuarioActivo["Nombre"]) ?>.</p>
            <a class="btn btn-primario" href="index.php">Volver al inicio</a>
        </section>

    <?php else: ?>

        
        <section class="tarjeta">
            <h2>Iniciar sesión</h2>

            <form method="post" action="procesar.php">
                <input type="hidden" name="accion" value="iniciar_sesion">

                <label for="correo">Correo electrónico</label>
                <input type="email" id="correo" name="correo" required>

                <label for="contraseña">Contraseña</label>
                <input type="password" id="contraseña" name="contraseña" required>

                <div class="fila-botones">
                    <button class="btn btn-primario" type="submit">Ingresar</button>
                    <a class="btn btn-secundario" href="registro.php">Crear cuenta nueva</a>
                </div>
            </form>
        </section>

    <?php endif; ?>

    <footer>EcoRetorno CR, Creadores del proyecto, Domian, Daniel, Claudio y Emily</footer>

</div>
</body>
</html>
