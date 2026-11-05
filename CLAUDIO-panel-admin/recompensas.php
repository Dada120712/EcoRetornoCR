
<?php
require_once __DIR__ . "/proteger_admin.php";

require_once "../SEBASTIAN-deposito-retiro/funciones.php";

// Administrador de prueba
$correoAdmin = "carlos.prueba@example.com";

if (
    !isset($_SESSION["usuario"]) ||
    !is_array($_SESSION["usuario"]) ||
    ($_SESSION["usuario"]["Correo"] ?? "") !== $correoAdmin
) {
    http_response_code(403);
    exit("Acceso restringido al administrador.");
}

// Consultar los materiales y sus recompensas
$materiales = leerMaterialesSimulador();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Recompensas - EcoRetornoCR</title>

    <link rel="stylesheet"
          href="../DANIELGV-inicio-registro-login/css/estilos.css">
</head>

<body>

<div class="contenedor">

    <header>
        <h1>EcoRetornoCR</h1>
        <p>Administración de Recompensas</p>
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

            <h2>Recompensas por material</h2>

            <p>
                En esta sección se muestran los valores
                económicos asignados a cada material reciclable.
            </p>

            <table border="1"
                   cellpadding="10"
                   cellspacing="0"
                   width="100%">

                <tr>
                    <th>Material</th>
                    <th>Valor por unidad</th>
                    <th>Ejemplo: 10 unidades</th>
                </tr>

                <?php foreach ($materiales as $material) { ?>

                    <tr>

                        <td>
                            <?php echo htmlspecialchars(
                                $material["Nombre"]
                            ); ?>
                        </td>

                        <td>
                            ₡<?php echo number_format(
                                $material["Valor"], 0, ",", "."
                            ); ?>
                        </td>

                        <td>
                            ₡<?php echo number_format(
                                $material["Valor"] * 10, 0, ",", "."
                            ); ?>
                        </td>

                    </tr>

                <?php } ?>

            </table>

        </section>

        <section class="tarjeta">

            <h2>Administrar valores</h2>

            <p>
                Para modificar el valor de una recompensa,
                ingrese al módulo de materiales.
            </p>

            <a href="materiales.php">
                Modificar recompensas
            </a>

        </section>

    </main>

    <footer>
        <p>EcoRetornoCR - Administración Municipal</p>
    </footer>

</div>

</body>
</html>
