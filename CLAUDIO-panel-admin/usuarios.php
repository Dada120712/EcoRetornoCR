
<?php
require_once __DIR__ . "/proteger_admin.php";

// Utilizamos las funciones que creó Daniel
require_once "../DANIELGV-inicio-registro-login/funciones.php";

// Obtenemos los usuarios registrados
$usuarios = leerUsuarios();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Gestión de Usuarios - EcoRetornoCR</title>

    <link rel="stylesheet"
          href="../DANIELGV-inicio-registro-login/css/estilos.css">
</head>

<body>
<div class="contenedor">

    <header>
        <h1>EcoRetornoCR</h1>
        <p>Gestión de Usuarios - Administrador Municipal</p>
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

            <h2>Usuarios registrados</h2>

            <p>
                Total de usuarios:
                <?php echo count($usuarios); ?>
            </p>

            <?php if (count($usuarios) > 0) { ?>

                <div style="overflow-x: auto;">

                    <table border="1" cellpadding="10"
                           cellspacing="0" width="100%">

                        <tr>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Teléfono</th>
                            <th>Código QR</th>
                            <th>Saldo</th>
                        </tr>

                        <?php foreach ($usuarios as $usuario) { ?>

                            <tr>
                                <td><?php echo htmlspecialchars($usuario["Nombre"]); ?></td>

                                <td><?php echo htmlspecialchars($usuario["Correo"]); ?></td>

                                <td><?php echo htmlspecialchars($usuario["Telefono"]); ?></td>

                                <td><?php echo htmlspecialchars($usuario["CodigoQR"]); ?></td>

                                <td>
                                    ₡<?php echo number_format($usuario["Saldo"], 2); ?>
                                </td>
                            </tr>

                        <?php } ?>

                    </table>

                </div>

            <?php } else { ?>

                <p>No hay usuarios registrados actualmente.</p>

            <?php } ?>

        </section>
    </main>

    <footer>
        <p>EcoRetornoCR - Administración Municipal</p>
    </footer>

</div>
</body>
</html>
