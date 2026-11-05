<?php

// simulador

session_start();
require_once "funciones_persona3.php";

$mensaje = $_SESSION["mensaje"] ?? null;
unset($_SESSION["mensaje"]);

// aca se ve si la maquina ya identifico a un ciudadano
$ciudadano = null;

if (isset($_SESSION["ciudadano_simulador"])) {
    $ciudadano = buscarCiudadanoPorCodigo($_SESSION["ciudadano_simulador"], leerUsuarios());
}

$materiales = leerMaterialesSimulador();
$usuarioActivo = $_SESSION["usuario"] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoRetorno CR - Simulador de máquina</title>
    <link rel="stylesheet" href="../DANIELGV-inicio-registro-login/css/estilos.css">
    <link rel="stylesheet" href="css/persona3.css">
</head>
<body>
<div class="contenedor">

    <header>
        <h1>EcoRetorno CR</h1>
        <p>Simulador de máquina de reciclaje</p>
    </header>

    <nav>
        <ul>
            <li><a href="../DANIELGV-inicio-registro-login/index.php">Inicio</a></li>
            <li><a href="simulador.php">Simulador de máquina</a></li>
            <li><a href="retiro.php">Solicitar retiro</a></li>
            <li><a href="solicitudes.php">Mis solicitudes</a></li>
            <?php if ($usuarioActivo): ?>
                <li><a href="../EMILY-depositos-recompensas/panel.php">Mi panel</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <?php if ($mensaje): ?>
        <div class="alerta alerta-<?= htmlspecialchars($mensaje["tipo"]) ?>">
            <?= htmlspecialchars($mensaje["texto"]) ?>
        </div>
    <?php endif; ?>

    <?php if ($ciudadano === null): ?>

        <section class="tarjeta">
            <h2>Identificar ciudadano</h2>
            <p>Escriba el código único del ciudadano (el mismo que aparece en su código QR) para empezar a depositar.</p>

            <form method="post" action="procesar_simulador.php">
                <input type="hidden" name="accion" value="identificar">

                <label for="codigo">Código del ciudadano</label>
                <input type="text" id="codigo" name="codigo" required placeholder="ECR-XXXXXXXX">

                <div class="fila-botones">
                    <button class="btn btn-primario" type="submit">Identificar</button>
                </div>
            </form>
        </section>

    <?php else: ?>

        <section class="tarjeta">
            <h2>Ciudadano identificado</h2>
            <p><strong>Nombre:</strong> <?= htmlspecialchars($ciudadano["Nombre"]) ?></p>
            <div class="codigo-ciudadano"><?= htmlspecialchars($ciudadano["CodigoQR"]) ?></div>
            <p><strong>Saldo actual:</strong> ₡<?= number_format($ciudadano["Saldo"], 0, ",", ".") ?></p>
        </section>

        <section class="tarjeta">
            <h2>Registrar depósito</h2>
            <p>Seleccione el material que se está entregando y la cantidad de unidades.</p>

            <form method="post" action="procesar_simulador.php">
                <input type="hidden" name="accion" value="depositar">

                <label for="material">Material reciclable</label>
                <select id="material" name="material" required>
                    <option value="">Seleccione un material</option>
                    <?php foreach ($materiales as $m): ?>
                        <option value="<?= htmlspecialchars($m["Nombre"]) ?>">
                            <?= htmlspecialchars($m["Nombre"]) ?> - ₡<?= number_format($m["Valor"], 0, ",", ".") ?> por unidad
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="cantidad">Cantidad de unidades</label>
                <input type="number" id="cantidad" name="cantidad" min="1" step="1" required placeholder="Ejemplo: 5">

                <div class="fila-botones">
                    <button class="btn btn-primario" type="submit" <?= count($materiales) === 0 ? "disabled" : "" ?>>Registrar depósito</button>
                </div>
            </form>

            <?php if (count($materiales) === 0): ?>
                <p class="vacio">Todavía no hay materiales disponibles.</p>
            <?php endif; ?>

            <form method="post" action="procesar_simulador.php">
                <input type="hidden" name="accion" value="salir">
                <div class="fila-botones">
                    <button class="btn btn-secundario" type="submit">Terminar y cambiar de ciudadano</button>
                </div>
            </form>
        </section>

    <?php endif; ?>

    <section class="tarjeta">
        <h2>Información importante</h2>
        <p>La recompensa se calcula multiplicando el valor del material por la cantidad de unidades, y se suma al saldo del ciudadano.</p>
        <p>Este simulador es parte de un prototipo académico, no hay conexión con máquinas físicas.</p>
    </section>

    <footer>EcoRetorno CR, Creadores del proyecto, Domian, Daniel, Claudio y Emily</footer>

</div>
</body>
</html>
