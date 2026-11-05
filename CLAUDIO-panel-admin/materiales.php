
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

// Token de seguridad
if (!isset($_SESSION["token_admin"])) {
    $_SESSION["token_admin"] = bin2hex(random_bytes(32));
}

$error = "";

// Procesar los formularios
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $token = $_POST["token"] ?? "";

    if (!is_string($token) ||
        !hash_equals($_SESSION["token_admin"], $token)) {
        http_response_code(403);
        exit("Solicitud no autorizada.");
    }

    $accion = $_POST["accion"] ?? "";

    // Abrir el archivo de materiales
    $archivo = fopen(RUTA_MATERIALES_P3, "c+");

    if ($archivo === false || !flock($archivo, LOCK_EX)) {
        $error = "No se pudo abrir el archivo de materiales.";

    } else {

        // Leer materiales existentes
        rewind($archivo);
        $lineas = array();
        $materialesActuales = array();

        while (($linea = fgets($archivo)) !== false) {
            $linea = trim($linea);

            if ($linea === "") {
                continue;
            }

            $datos = explode("|", $linea);

            if (count($datos) == 2 && is_numeric($datos[1])) {
                $materialesActuales[] = array(
                    "Nombre" => $datos[0],
                    "Valor" => (int)$datos[1]
                );
            }
        }

        $guardar = false;

        // AGREGAR MATERIAL
        if ($accion == "agregar") {

            $nombre = trim($_POST["nombre"] ?? "");

            $valor = filter_var(
                $_POST["valor"] ?? null,
                FILTER_VALIDATE_INT
            );

            $existe = false;

            foreach ($materialesActuales as $material) {
                if (strcasecmp($material["Nombre"], $nombre) == 0) {
                    $existe = true;
                    break;
                }
            }

            if (
                $nombre == "" ||
                strlen($nombre) > 100 ||
                strpbrk($nombre, "|\r\n") !== false
            ) {
                $error = "Ingrese un nombre válido.";

            } elseif ($valor === false || $valor === null ||
                      $valor < 0) {
                $error = "Ingrese una recompensa válida.";

            } elseif ($existe) {
                $error = "El material ya está registrado.";

            } else {
                $materialesActuales[] = array(
                    "Nombre" => $nombre,
                    "Valor" => $valor
                );

                $guardar = true;
            }
        }

        // EDITAR RECOMPENSA
        elseif ($accion == "editar") {

            $nombreOriginal = $_POST["nombre_original"] ?? "";

            $nuevoValor = filter_var(
                $_POST["valor"] ?? null,
                FILTER_VALIDATE_INT
            );

            if ($nuevoValor === false || $nuevoValor === null ||
                $nuevoValor < 0) {
                $error = "Ingrese una recompensa válida.";

            } else {

                $encontrado = false;

                foreach ($materialesActuales as &$material) {

                    if ($material["Nombre"] === $nombreOriginal) {
                        $material["Valor"] = $nuevoValor;
                        $encontrado = true;
                        break;
                    }
                }

                unset($material);

                if ($encontrado) {
                    $guardar = true;
                } else {
                    $error = "No se encontró el material.";
                }
            }
        }

        // ELIMINAR MATERIAL
        elseif ($accion == "eliminar") {

            $nombreOriginal = $_POST["nombre_original"] ?? "";

            $nuevaLista = array();
            $encontrado = false;

            foreach ($materialesActuales as $material) {

                if ($material["Nombre"] === $nombreOriginal) {
                    $encontrado = true;
                    continue;
                }

                $nuevaLista[] = $material;
            }

            if ($encontrado) {
                $materialesActuales = $nuevaLista;
                $guardar = true;
            } else {
                $error = "No se encontró el material.";
            }
        }

        else {
            $error = "Acción no válida.";
        }

        // GUARDAR LOS CAMBIOS EN EL TXT
        if ($guardar) {

            $contenido = "";

            foreach ($materialesActuales as $material) {
                $contenido .= $material["Nombre"] . "|" .
                              $material["Valor"] . PHP_EOL;
            }

            rewind($archivo);

            if (
                !ftruncate($archivo, 0) ||
                fwrite($archivo, $contenido) !== strlen($contenido)
            ) {
                $error = "No se pudieron guardar los cambios.";
            }

            fflush($archivo);
        }

        flock($archivo, LOCK_UN);
        fclose($archivo);

        if ($guardar && $error == "") {
            header("Location: materiales.php");
            exit;
        }
    }
}

// Consultar los materiales registrados
$materiales = leerMaterialesSimulador();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Gestión de Materiales - EcoRetornoCR</title>

    <link rel="stylesheet"
          href="../DANIELGV-inicio-registro-login/css/estilos.css">
</head>

<body>

<div class="contenedor">

    <header>
        <h1>EcoRetornoCR</h1>
        <p>Gestión de Materiales Reciclables</p>
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

        <!-- REGISTRAR MATERIAL -->
        <section class="tarjeta">

            <h2>Registrar nuevo material</h2>

            <p>
                Ingrese el nombre del material reciclable
                y la recompensa económica por unidad.
            </p>

            <?php if ($error != "") { ?>

                <p style="color: red;">
                    <?php echo htmlspecialchars($error); ?>
                </p>

            <?php } ?>

            <form action="materiales.php" method="POST">

                <input type="hidden" name="accion" value="agregar">

                <input type="hidden" name="token"
                       value="<?php echo htmlspecialchars(
                           $_SESSION["token_admin"]
                       ); ?>">

                <label for="nombre">Nombre del material:</label>

                <input type="text"
                       id="nombre"
                       name="nombre"
                       maxlength="100"
                       required>

                <label for="valor">Recompensa por unidad (₡):</label>

                <input type="number"
                       id="valor"
                       name="valor"
                       min="0"
                       step="1"
                       required>

                <button type="submit">
                    Guardar material
                </button>

            </form>

        </section>

        <!-- TABLA DE MATERIALES -->
        <section class="tarjeta">

            <h2>Materiales registrados</h2>

            <p>
                Total de materiales:
                <?php echo count($materiales); ?>
            </p>

            <?php if (count($materiales) > 0) { ?>

                <div style="overflow-x: auto;">

                    <table border="1"
                           cellpadding="10"
                           cellspacing="0"
                           width="100%">

                        <tr>
                            <th>Material</th>
                            <th>Recompensa por unidad</th>
                            <th>Acciones</th>
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
                                        $material["Valor"],
                                        0,
                                        ",",
                                        "."
                                    ); ?>
                                </td>

                                <td>

                                    <!-- EDITAR MATERIAL -->
                                    <details>

                                        <summary>Editar</summary>

                                        <form action="materiales.php"
                                              method="POST">

                                            <input type="hidden"
                                                   name="accion"
                                                   value="editar">

                                            <input type="hidden"
                                                   name="token"
                                                   value="<?php echo htmlspecialchars(
                                                       $_SESSION["token_admin"]
                                                   ); ?>">

                                            <input type="hidden"
                                                   name="nombre_original"
                                                   value="<?php echo htmlspecialchars(
                                                       $material["Nombre"],
                                                       ENT_QUOTES,
                                                       "UTF-8"
                                                   ); ?>">

                                            <label>
                                                Nueva recompensa (₡):
                                            </label>

                                            <input type="number"
                                                   name="valor"
                                                   min="0"
                                                   step="1"
                                                   value="<?php echo (int)$material["Valor"]; ?>"
                                                   required>

                                            <button type="submit">
                                                Guardar cambios
                                            </button>

                                        </form>

                                    </details>

                                    <!-- ELIMINAR MATERIAL -->
                                    <form action="materiales.php"
                                          method="POST"
                                          onsubmit="return confirm('¿Seguro que desea eliminar este material?');">

                                        <input type="hidden"
                                               name="accion"
                                               value="eliminar">

                                        <input type="hidden"
                                               name="token"
                                               value="<?php echo htmlspecialchars(
                                                   $_SESSION["token_admin"]
                                               ); ?>">

                                        <input type="hidden"
                                               name="nombre_original"
                                               value="<?php echo htmlspecialchars(
                                                   $material["Nombre"],
                                                   ENT_QUOTES,
                                                   "UTF-8"
                                               ); ?>">

                                        <button type="submit">
                                            Eliminar
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        <?php } ?>

                    </table>

                </div>

            <?php } else { ?>

                <p>No hay materiales registrados actualmente.</p>

            <?php } ?>

        </section>

    </main>

    <footer>
        <p>EcoRetornoCR - Administración Municipal</p>
    </footer>

</div>

</body>
</html>
