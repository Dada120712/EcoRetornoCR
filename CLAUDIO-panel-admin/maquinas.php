
<?php
require_once __DIR__ . "/proteger_admin.php";

// Archivo donde están guardadas las máquinas
$ruta = __DIR__ . "/datos/maquinas.txt";

// Arreglo para almacenar las máquinas
$maquinas = array();


 // Editar o eliminar una máquina
if ($_SERVER["REQUEST_METHOD"] == "POST" &&
    isset($_POST["accion"])) {

    $accion = $_POST["accion"];
    $id = (int)($_POST["id"] ?? 0);

    if (file_exists($ruta) && $id > 0) {

        $lineas = file($ruta, FILE_IGNORE_NEW_LINES |
                      FILE_SKIP_EMPTY_LINES);

        $nuevasLineas = array();

        foreach ($lineas as $linea) {

            $datos = explode("|", $linea);

            if (count($datos) != 4) {
                continue;
            }

            if ((int)$datos[0] == $id) {

                if ($accion == "eliminar") {
                    continue;
                }

                if ($accion == "editar") {

                    $canton = trim($_POST["canton"] ?? "");
                    $ubicacion = trim($_POST["ubicacion"] ?? "");
                    $estado = $_POST["estado"] ?? "";

                    $canton = str_replace(
                        array("|", "\r", "\n"), "", $canton
                    );

                    $ubicacion = str_replace(
                        array("|", "\r", "\n"), "", $ubicacion
                    );

                    if ($canton != "" && $ubicacion != "" &&
                        in_array($estado, array(
                            "Activa", "Inactiva", "Próximamente"
                        ))) {

                        $linea = $id . "|" . $canton . "|" .
                                 $ubicacion . "|" . $estado;
                    }
                }
            }

            $nuevasLineas[] = $linea;
        }

        file_put_contents(
            $ruta,
            implode(PHP_EOL, $nuevasLineas) . PHP_EOL,
            LOCK_EX
        );

        header("Location: maquinas.php");
        exit;
    }
}


 // Guardar una nueva máquina
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $canton = trim($_POST["canton"] ?? "");
    $ubicacion = trim($_POST["ubicacion"] ?? "");
    $estado = $_POST["estado"] ?? "";

    if ($canton != "" && $ubicacion != "" &&
        in_array($estado, array("Activa", "Inactiva", "Próximamente"))) {

        $ultimoId = 0;

        if (file_exists($ruta)) {
            $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lineas as $linea) {
                $datos = explode("|", $linea);

                if (count($datos) == 4 && (int)$datos[0] > $ultimoId) {
                    $ultimoId = (int)$datos[0];
                }
            }
        }

        $nuevoId = $ultimoId + 1;

        $canton = str_replace(array("|", "\r", "\n"), "", $canton);
        $ubicacion = str_replace(array("|", "\r", "\n"), "", $ubicacion);

        $registro = $nuevoId . "|" . $canton . "|" .
                    $ubicacion . "|" . $estado . PHP_EOL;

        file_put_contents($ruta, $registro, FILE_APPEND | LOCK_EX);

        header("Location: maquinas.php");
        exit;
    }
}


// Leer las máquinas registradas
if (file_exists($ruta)) {

    $archivo = fopen($ruta, "r");

    while (!feof($archivo)) {

        $linea = trim(fgets($archivo));

        if ($linea != "") {

            $datos = explode("|", $linea);

            if (count($datos) == 4) {
                $maquinas[] = array(
                    "Id" => $datos[0],
                    "Canton" => $datos[1],
                    "Ubicacion" => $datos[2],
                    "Estado" => $datos[3]
                );
            }
        }
    }

    fclose($archivo);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Gestión de Máquinas - EcoRetornoCR</title>

    <link rel="stylesheet"
          href="../DANIELGV-inicio-registro-login/css/estilos.css">
</head>

<body>
<div class="contenedor">

    <header>
        <h1>EcoRetornoCR</h1>
        <p>Gestión de Máquinas de Reciclaje</p>
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
            
<section class="tarjeta">

    <h2>Registrar nueva máquina</h2>

    <form action="maquinas.php" method="POST">

        <label for="canton">Cantón:</label>
        <input type="text" id="canton" name="canton"
               maxlength="100" required>

        <label for="ubicacion">Ubicación:</label>
        <input type="text" id="ubicacion" name="ubicacion"
               maxlength="200" required>

        <label for="estado">Estado:</label>
        <select id="estado" name="estado" required>
            <option value="Activa">Activa</option>
            <option value="Inactiva">Inactiva</option>
            <option value="Próximamente">Próximamente</option>
        </select>

        <button type="submit">Guardar máquina</button>

    </form>

</section>


            <h2>Máquinas registradas</h2>

            <p>
                Total de máquinas:
                <?php echo count($maquinas); ?>
            </p>

            <div style="overflow-x: auto;">

                <table border="1" cellpadding="10"
                       cellspacing="0" width="100%">

                    <tr>
                        <th>ID</th>
                        <th>Cantón</th>
                        <th>Ubicación</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>

                    <?php foreach ($maquinas as $maquina) { ?>

                        <tr>
                            <td>
                                <?php echo htmlspecialchars($maquina["Id"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($maquina["Canton"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($maquina["Ubicacion"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($maquina["Estado"]); ?>
                            </td>
                            
<td>
    <details>
        <summary>Editar</summary>

        <form action="maquinas.php" method="POST">

            <input type="hidden" name="accion" value="editar">

            <input type="hidden" name="id"
                   value="<?php echo (int)$maquina['Id']; ?>">

            <label>Cantón:</label>
            <input type="text" name="canton"
                   value="<?php echo htmlspecialchars($maquina['Canton']); ?>"
                   required>

            <label>Ubicación:</label>
            <input type="text" name="ubicacion"
                   value="<?php echo htmlspecialchars($maquina['Ubicacion']); ?>"
                   required>

            <label>Estado:</label>
            <select name="estado">
                <?php foreach (array("Activa", "Inactiva", "Próximamente") as $opcion) { ?>
                    <option value="<?php echo $opcion; ?>"
                        <?php if ($maquina['Estado'] == $opcion) echo "selected"; ?>>
                        <?php echo $opcion; ?>
                    </option>
                <?php } ?>
            </select>

            <button type="submit">Guardar cambios</button>
        </form>
    </details>

    <form action="maquinas.php" method="POST"
          onsubmit="return confirm('¿Seguro que desea eliminar esta máquina?');">

        <input type="hidden" name="accion" value="eliminar">

        <input type="hidden" name="id"
               value="<?php echo (int)$maquina['Id']; ?>">

        <button type="submit">Eliminar</button>
    </form>
</td>

                        </tr>

                    <?php } ?>

                </table>

            </div>

        </section>
    </main>

    <footer>
        <p>EcoRetornoCR - Administración Municipal</p>
    </footer>

</div>
</body>
</html>
