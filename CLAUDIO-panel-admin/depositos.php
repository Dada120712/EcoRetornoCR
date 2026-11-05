
<?php
require_once __DIR__ . "/proteger_admin.php";

// Funciones del proyecto de Sebastián
require_once "../SEBASTIAN-deposito-retiro/funciones.php";

// Administrador de prueba
$correoAdmin = "carlos.prueba@example.com";

// Verificar acceso del administrador
if (
    !isset($_SESSION["usuario"]) ||
    !is_array($_SESSION["usuario"]) ||
    ($_SESSION["usuario"]["Correo"] ?? "") !== $correoAdmin
) {
    http_response_code(403);
    exit("Acceso restringido al administrador.");
}

// Arreglo para guardar los depósitos
$depositos = array();

$totalRecompensas = 0;
$totalUnidades = 0;

// Leer el archivo de depósitos de Sebastián
if (file_exists(RUTA_DEPOSITOS_P3)) {

    $archivo = fopen(RUTA_DEPOSITOS_P3, "r");

    while (($linea = fgets($archivo)) !== false) {

        $linea = trim($linea);

        if ($linea == "") {
            continue;
        }

        $datos = explode("|", $linea);

        if (count($datos) == 8) {

            $depositos[] = array(
                "Fecha" => $datos[0],
                "Correo" => $datos[1],
                "CodigoQR" => $datos[2],
                "Maquina" => $datos[3],
                "Material" => $datos[4],
                "Cantidad" => (int)$datos[5],
                "Valor" => (int)$datos[6],
                "Recompensa" => (int)$datos[7]
            );

            $totalUnidades += (int)$datos[5];
            $totalRecompensas += (int)$datos[7];
        }
    }

    fclose($archivo);
}

// Mostrar los depósitos más recientes primero
$depositos = array_reverse($depositos);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Historial de Depósitos - EcoRetornoCR</title>

    <link rel="stylesheet"
          href="../DANIELGV-inicio-registro-login/css/estilos.css">
</head>

<body>

<div class="contenedor">

    <header>
        <h1>EcoRetornoCR</h1>
        <p>Administración de Depósitos</p>
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

        <!-- RESUMEN DE DEPÓSITOS -->
        <section class="tarjeta">

            <h2>Resumen de depósitos</h2>

            <p>
                Total de depósitos registrados:
                <strong>
                    <?php echo count($depositos); ?>
                </strong>
            </p>

            <p>
                Total de unidades recicladas:
                <strong>
                    <?php echo $totalUnidades; ?>
                </strong>
            </p>

            <p>
                Total de recompensas generadas:
                <strong>
                    ₡<?php echo number_format(
                        $totalRecompensas, 0, ",", "."
                    ); ?>
                </strong>
            </p>

        </section>

        <!-- HISTORIAL DE DEPÓSITOS -->
        <section class="tarjeta">

            <h2>Historial de depósitos realizados</h2>

            <p>
                En esta sección se muestran los materiales
                reciclados por los ciudadanos y las
                recompensas económicas correspondientes.
            </p>

            <?php if (count($depositos) > 0) { ?>

                <div style="overflow-x: auto;">

                    <table border="1"
                           cellpadding="10"
                           cellspacing="0"
                           width="100%">

                        <tr>
                            <th>Fecha</th>
                            <th>Ciudadano</th>
                            <th>Código QR</th>
                            <th>Máquina</th>
                            <th>Material</th>
                            <th>Cantidad</th>
                            <th>Valor unitario</th>
                            <th>Recompensa</th>
                        </tr>

                        <?php foreach ($depositos as $deposito) { ?>

                            <tr>

                                <td>
                                    <?php echo htmlspecialchars(
                                        $deposito["Fecha"]
                                    ); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars(
                                        $deposito["Correo"]
                                    ); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars(
                                        $deposito["CodigoQR"]
                                    ); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars(
                                        $deposito["Maquina"]
                                    ); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars(
                                        $deposito["Material"]
                                    ); ?>
                                </td>

                                <td>
                                    <?php echo $deposito["Cantidad"]; ?>
                                </td>

                                <td>
                                    ₡<?php echo number_format(
                                        $deposito["Valor"],
                                        0, ",", "."
                                    ); ?>
                                </td>

                                <td>
                                    ₡<?php echo number_format(
                                        $deposito["Recompensa"],
                                        0, ",", "."
                                    ); ?>
                                </td>

                            </tr>

                        <?php } ?>

                    </table>

                </div>

            <?php } else { ?>

                <p>
                    No hay depósitos registrados actualmente.
                </p>

            <?php } ?>

        </section>

    </main>

    <footer>
        <p>EcoRetornoCR - Administración Municipal</p>
    </footer>

</div>

</body>
</html>
