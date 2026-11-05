
<?php
// Panel principal del administrador municipal
require_once __DIR__ . "/proteger_admin.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador - EcoRetornoCR</title>

    <link rel="stylesheet"
          href="../DANIELGV-inicio-registro-login/css/estilos.css">
</head>

<body>
<div class="contenedor">

    <header>
        <h1>EcoRetornoCR</h1>
        <p>Panel de Administración Municipal</p>
    </header>

    <nav>
        <ul>
            <li><a href="panel_admin.php">Inicio</a></li>
            <li><a href="usuarios.php">Usuarios</a></li>
            <li><a href="maquinas.php">Máquinas</a></li>
            <li><a href="materiales.php">Materiales</a></li>
            <li><a href="recompensas.php">Recompensas</a></li>
            <li><a href="depositos.php">Depósitos</a></li>
            <li><a href="solicitudes.php">Solicitudes</a></li>
        </ul>
    </nav>

    <main>
        <section class="tarjeta">
            <h2>Bienvenido al Panel Administrador</h2>

            <p>
                Desde este panel la municipalidad podrá
                administrar los usuarios, las máquinas
                de reciclaje, los materiales y las recompensas.
            </p>

            <p>
                También podrá consultar los depósitos
                realizados y gestionar las solicitudes
                de retiro de los ciudadanos.
            </p>
        </section>

        <section class="tarjeta">
            <h2>Funciones del administrador</h2>

            <p>Seleccione una opción del menú superior.</p>
        </section>
    </main>

    <footer>
        <p>EcoRetornoCR - Administración Municipal</p>
    </footer>

</div>
</body>
</html>
