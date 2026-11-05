
<?php
require_once __DIR__ . "/proteger_admin.php";
require_once "../SEBASTIAN-deposito-retiro/funciones.php";

// Administrador de prueba
$correoAdmin = "carlos.prueba@example.com";

// Verificar acceso
if (
    !isset($_SESSION["usuario"]) ||
    !is_array($_SESSION["usuario"]) ||
    ($_SESSION["usuario"]["Correo"] ?? "") !== $correoAdmin
) {
    http_response_code(403);
    exit("Acceso restringido al administrador.");
}

// Token de seguridad
if (!isset($_SESSION["token_admin"])) {
    $_SESSION["token_admin"] = bin2hex(random_bytes(32));
}

$error = "";
$exito = $_SESSION["exito_admin"] ?? "";
unset($_SESSION["exito_admin"]);

// Procesar aprobación o rechazo
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $token = $_POST["token"] ?? "";
    $accion = $_POST["accion"] ?? "";
    $indice = filter_var(
        $_POST["indice"] ?? null,
        FILTER_VALIDATE_INT
    );

    if (!is_string($token) ||
        !hash_equals($_SESSION["token_admin"], $token)) {
        http_response_code(403);
        exit("Solicitud no autorizada.");
    }

    if (
        !in_array($accion, array("aprobar", "rechazar"), true) ||
        $indice === false ||
        $indice === null ||
        $indice < 0
    ) {
        $error = "La acción seleccionada no es válida.";

    } else {

        $archivoRetiros = null;
        $archivoUsuarios = null;

        try {
            // Bloquear solicitudes mientras se modifican
            $archivoRetiros = fopen(RUTA_RETIROS_P3, "c+");

            if (!$archivoRetiros ||
                !flock($archivoRetiros, LOCK_EX)) {
                throw new Exception(
                    "No se pudo abrir el archivo de solicitudes."
                );
            }

            rewind($archivoRetiros);
            $lineas = array();

            while (($linea = fgets($archivoRetiros)) !== false) {
                $linea = trim($linea);

                if ($linea !== "") {
                    $lineas[] = $linea;
                }
            }

            if (!isset($lineas[$indice])) {
                throw new Exception("Solicitud no encontrada.");
            }

            $datos = explode("|", $lineas[$indice]);

            if (count($datos) !== 6 ||
                $datos[5] !== "Pendiente") {
                throw new Exception(
                    "Esta solicitud ya fue procesada o no es válida."
                );
            }

            $correo = $datos[1];
            $monto = filter_var(
                $datos[4],
                FILTER_VALIDATE_INT
            );

            if ($monto === false || $monto <= 0) {
                throw new Exception("Monto no válido.");
            }

            // Aprobar: descontar saldo del ciudadano
            if ($accion === "aprobar") {

                $archivoUsuarios = fopen(RUTA_USUARIOS, "c+");

                if (!$archivoUsuarios ||
                    !flock($archivoUsuarios, LOCK_EX)) {
                    throw new Exception(
                        "No se pudo abrir el archivo de usuarios."
                    );
                }

                rewind($archivoUsuarios);

                $usuarios = array();

                while (($linea = fgets($archivoUsuarios)) !== false) {
                    $linea = trim($linea);

                    if ($linea !== "") {
                        $usuarios[] = $linea;
                    }
                }

                $encontrado = false;

                foreach ($usuarios as $i => $linea) {

                    $usuario = explode("|", $linea);

                    if (count($usuario) !== 6) {
                        continue;
                    }

                    if ($usuario[1] === $correo) {

                        $encontrado = true;
                        $saldo = (int)$usuario[5];

                        if ($saldo < $monto) {
                            throw new Exception(
                                "El ciudadano no tiene saldo suficiente."
                            );
                        }

                        $usuario[5] = $saldo - $monto;
                        $usuarios[$i] = implode("|", $usuario);
                        break;
                    }
                }

                if (!$encontrado) {
                    throw new Exception(
                        "No se encontró el ciudadano."
                    );
                }
            }

            // Preparar nuevo estado de solicitud
            $datos[5] = $accion === "aprobar"
                ? "Aprobada"
                : "Rechazada";

            $lineas[$indice] = implode("|", $datos);

            $contenidoRetiros = implode(PHP_EOL, $lineas);
            if ($contenidoRetiros !== "") {
                $contenidoRetiros .= PHP_EOL;
            }

            // Primero actualizar saldo cuando se aprueba
            if ($accion === "aprobar") {

                $contenidoUsuarios = implode(PHP_EOL, $usuarios);

                if ($contenidoUsuarios !== "") {
                    $contenidoUsuarios .= PHP_EOL;
                }

                // Conservar copia para recuperación
                rewind($archivoUsuarios);
                $respaldo = stream_get_contents($archivoUsuarios);

                rewind($archivoUsuarios);
                $escrito = fwrite(
                    $archivoUsuarios,
                    $contenidoUsuarios
                );

                if ($escrito !== strlen($contenidoUsuarios) ||
                    !ftruncate(
                        $archivoUsuarios,
                        strlen($contenidoUsuarios)
                    )) {
                    throw new Exception(
                        "No se pudo actualizar el saldo."
                    );
                }

                fflush($archivoUsuarios);
            }

            // Guardar el estado de la solicitud
            rewind($archivoRetiros);

            $escrito = fwrite(
                $archivoRetiros,
                $contenidoRetiros
            );

            $guardado = (
                $escrito === strlen($contenidoRetiros) &&
                ftruncate(
                    $archivoRetiros,
                    strlen($contenidoRetiros)
                )
            );

            if (!$guardado) {

                // Intentar recuperar el saldo anterior
                if ($accion === "aprobar" &&
                    $archivoUsuarios !== null) {

                    rewind($archivoUsuarios);
                    fwrite($archivoUsuarios, $respaldo);
                    ftruncate(
                        $archivoUsuarios,
                        strlen($respaldo)
                    );
                    fflush($archivoUsuarios);
                }

                throw new Exception(
                    "No se pudo actualizar la solicitud."
                );
            }

            fflush($archivoRetiros);

            $_SESSION["exito_admin"] =
                "Solicitud " . strtolower($datos[5]) .
                " correctamente.";

        } catch (Throwable $e) {

            $error = $e->getMessage();

        } finally {

            if (is_resource($archivoUsuarios)) {
                flock($archivoUsuarios, LOCK_UN);
                fclose($archivoUsuarios);
            }

            if (is_resource($archivoRetiros)) {
                flock($archivoRetiros, LOCK_UN);
                fclose($archivoRetiros);
            }
        }

        if ($error === "") {
            header("Location: solicitudes.php");
            exit;
        }
    }
}

// Consultar solicitudes
$solicitudes = leerSolicitudes();

// Totales
$pendientes = 0;
$aprobadas = 0;
$rechazadas = 0;

foreach ($solicitudes as $solicitud) {

    if ($solicitud["Estado"] === "Pendiente") {
        $pendientes++;
    } elseif ($solicitud["Estado"] === "Aprobada") {
        $aprobadas++;
    } elseif ($solicitud["Estado"] === "Rechazada") {
        $rechazadas++;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Solicitudes de Retiro - EcoRetornoCR</title>

    <link rel="stylesheet"
          href="../DANIELGV-inicio-registro-login/css/estilos.css">
</head>

<body>
<div class="contenedor">

    <header>
        <h1>EcoRetornoCR</h1>
        <p>Administración de Solicitudes de Retiro</p>
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

            <h2>Resumen de solicitudes</h2>

            <p>Total de solicitudes:
                <strong><?php echo count($solicitudes); ?></strong>
            </p>

            <p>Pendientes:
                <strong><?php echo $pendientes; ?></strong>
            </p>

            <p>Aprobadas:
                <strong><?php echo $aprobadas; ?></strong>
            </p>

            <p>Rechazadas:
                <strong><?php echo $rechazadas; ?></strong>
            </p>

        </section>

        <section class="tarjeta">

            <h2>Solicitudes de retiro registradas</h2>

            <?php if ($error !== "") { ?>
                <p style="color: red;">
                    <?php echo htmlspecialchars($error); ?>
                </p>
            <?php } ?>

            <?php if ($exito !== "") { ?>
                <p style="color: green;">
                    <?php echo htmlspecialchars($exito); ?>
                </p>
            <?php } ?>

            <?php if (count($solicitudes) > 0) { ?>

                <div style="overflow-x: auto;">

                    <table border="1" cellpadding="10"
                           cellspacing="0" width="100%">

                        <tr>
                            <th>Fecha</th>
                            <th>Ciudadano</th>
                            <th>Método</th>
                            <th>Destino</th>
                            <th>Monto</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>

                        <?php foreach ($solicitudes as $i => $s) { ?>

                            <tr>

                                <td>
                                    <?php echo htmlspecialchars($s["Fecha"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($s["Correo"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($s["Metodo"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($s["Destino"]); ?>
                                </td>

                                <td>
                                    ₡<?php echo number_format(
                                        $s["Monto"], 0, ",", "."
                                    ); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($s["Estado"]); ?>
                                </td>

                                <td>

                                    <?php if ($s["Estado"] === "Pendiente") { ?>

                                        <form method="POST"
                                              action="solicitudes.php">

                                            <input type="hidden"
                                                   name="token"
                                                   value="<?php echo htmlspecialchars(
                                                       $_SESSION["token_admin"]
                                                   ); ?>">

                                            <input type="hidden"
                                                   name="indice"
                                                   value="<?php echo $i; ?>">

                                            <button type="submit"
                                                    name="accion"
                                                    value="aprobar"
                                                    onclick="return confirm('¿Aprobar esta solicitud simulada?');">
                                                Aprobar
                                            </button>

                                            <button type="submit"
                                                    name="accion"
                                                    value="rechazar"
                                                    onclick="return confirm('¿Rechazar esta solicitud?');">
                                                Rechazar
                                            </button>

                                        </form>

                                    <?php } else { ?>

                                        Procesada

                                    <?php } ?>

                                </td>

                            </tr>

                        <?php } ?>

                    </table>

                </div>

            <?php } else { ?>

                <p>No hay solicitudes de retiro registradas.</p>

            <?php } ?>

        </section>

    </main>

    <footer>
        <p>EcoRetornoCR - Administración Municipal</p>
    </footer>

</div>
</body>
</html>
