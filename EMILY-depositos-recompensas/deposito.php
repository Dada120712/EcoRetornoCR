
<?php
// REGISTRO DE DEPOSITOS

session_start();
require_once "funciones_persona2.php";

// Verificamos que el usuario haya iniciado sesión
if (!isset($_SESSION["usuario"])) {
    header("Location: ../DANIELGV-inicio-registro-login/login.php");
    exit;
}

// Obtenemos la información del ciudadano
$correo = $_SESSION["usuario"]["Correo"];
$usuario = obtenerUsuarioActual($correo);

if ($usuario === null) {
    unset($_SESSION["usuario"]);
    header("Location: ../DANIELGV-inicio-registro-login/login.php");
    exit;
}

$maquinas = obtenerMaquinas();
$materiales = leerMateriales();
$mensaje = $_SESSION["mensaje"] ?? null;
unset($_SESSION["mensaje"]);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoRetorno CR - Registrar Depósito</title>
    <link rel="stylesheet" href="../DANIELGV-inicio-registro-login/css/estilos.css">
    <link rel="stylesheet" href="css/persona2.css">
</head>
<body>
<div class="contenedor">

    <header>
        <h1>EcoRetorno CR</h1>
        <p>Registro de materiales reciclables</p>
    </header>

    <!-- Menú de navegación -->
    <nav>
        <ul>
            <li><a href="../DANIELGV-inicio-registro-login/index.php">Inicio</a></li>
            <li><a href="panel.php">Mi panel</a></li>
            <li><a href="deposito.php">Registrar depósito</a></li>
            <li><a href="retiro.php">Solicitar retiro</a></li>
        </ul>
    </nav>

    <?php if ($mensaje): ?>
        <div class="alerta alerta-<?= htmlspecialchars($mensaje["tipo"]) ?>">
            <?= htmlspecialchars($mensaje["texto"]) ?>
        </div>
    <?php endif; ?>

    <!-- Información del ciudadano -->
    <section class="tarjeta">
        <h2>Información del ciudadano</h2>
        <p><strong>Nombre:</strong> <?= htmlspecialchars($usuario["Nombre"]) ?></p>
        <p><strong>Código de identificación:</strong> <?= htmlspecialchars($usuario["CodigoQR"]) ?></p>
        <p><strong>Saldo actual:</strong> ₡<?= number_format($usuario["Saldo"], 0, ",", ".") ?></p>
    </section>

    <!-- Formulario para registrar depósitos -->
    <section class="tarjeta">
        <h2>Registrar un depósito</h2>
        <p>Seleccione la máquina donde realizará la entrega, el material reciclable y la cantidad de unidades que desea depositar.</p>

        <form method="post" action="procesar_deposito.php">

            <label for="maquina">Máquina de reciclaje</label>
            <select id="maquina" name="maquina" required>
                <option value="">Seleccione una máquina</option>
                <?php foreach ($maquinas as $maquina): ?>
                    <?php if ($maquina["Estado"] === "Activa"): ?>
                        <option value="<?= htmlspecialchars($maquina["Ubicacion"]) ?>">
                            <?= htmlspecialchars($maquina["Canton"]) ?> - <?= htmlspecialchars($maquina["Ubicacion"]) ?>
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>

            <label for="material">Material reciclable</label>
            <select id="material" name="material" required>
                <option value="">Seleccione un material</option>
                <?php foreach ($materiales as $material): ?>
                    <option value="<?= htmlspecialchars($material["Nombre"]) ?>">
                        <?= htmlspecialchars($material["Nombre"]) ?> - ₡<?= number_format($material["Valor"], 0, ",", ".") ?> por unidad
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="cantidad">Cantidad de unidades</label>
            <input type="number" id="cantidad" name="cantidad" min="1" step="1" required placeholder="Ejemplo: 5">

            <div class="fila-botones">
                <button class="btn btn-primario" type="submit" <?= count($materiales) === 0 ? "disabled" : "" ?>>
                    Registrar depósito
                </button>
                <a class="btn btn-secundario" href="panel.php">Volver a mi panel</a>
            </div>
        </form>

        <?php if (count($materiales) === 0): ?>
            <p class="vacio">Todavía no hay materiales disponibles.</p>
        <?php endif; ?>
    </section>

    <!-- Información sobre las recompensas -->
    <section class="tarjeta">
        <h2>¿Cómo se calcula mi recompensa?</h2>
        <p>Cada material reciclable tiene un valor económico establecido. El sistema multiplica ese valor por la cantidad de unidades depositadas.</p>
        <p>La recompensa obtenida se agregará automáticamente al saldo del ciudadano.</p>
        <p>Este registro es una simulación académica. No existe una conexión con máquinas físicas.</p>
    </section>

    <footer>EcoRetorno CR - Proyecto académico de reciclaje</footer>
</div>
</body>
</html>
