<?php

session_start();
require_once "funciones.php";


$mensaje = $_SESSION["mensaje"] ?? null;
unset($_SESSION["mensaje"]);


//AQUI SI ALGUIEN YA TIENE USUARIO PARA QUE NO TENGA Q VOLVER A REGISTRARSE.
$usuarioActivo = $_SESSION["usuario"] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoRetorno CR - Registro</title>
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
            <p>Ya ingresó como <?= htmlspecialchars($usuarioActivo["Nombre"]) ?>.</p>
            <a class="btn btn-primario" href="index.php">Volver al inicio</a>
        </section>

    <?php else: ?>

        
        <section class="tarjeta">
            <h2>Crear su propia cuenta</h2>
            <p>Complete sus datos para que tenga su propio código y empezar a reciclar con dinero de por medio.</p>

            <form method="post" action="procesar.php">
                <input type="hidden" name="accion" value="registrar">

                <label for="nombre">Nombre completo</label>
                <input type="text" id="nombre" name="nombre" required minlength="3">

                <label for="correo">Correo electrónico</label>
                <input type="email" id="correo" name="correo" required>

                <label for="telefono">Teléfono</label>
                <input type="text" id="telefono" name="telefono" required
                       placeholder="8888-8888">

                <label for="contraseña">Contraseña</label>
                <input type="password" id="contrasena" name="contraseña" required minlength="10">

                <div class="fila-botones">
                    <button class="btn btn-primario" type="submit">Crear cuenta</button>
                    <a class="btn btn-secundario" href="login.php">Ya tengo cuenta</a>
                </div>
            </form>
        </section>

    <?php endif; ?>

    <footer>EcoRetorno CR, Creadores del proyecto, Domian, Daniel, Claudio y Emily</footer>

</div>
</body>
</html>
