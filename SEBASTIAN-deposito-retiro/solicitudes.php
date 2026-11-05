<?php

// historial 

session_start();
require_once "funciones.php";

if (!isset($_SESSION["usuario"])) {
    header("Location: ../DANIELGV-inicio-registro-login/login.php");
    exit;
}

$correo = $_SESSION["usuario"]["Correo"];
$solicitudes = solicitudesDeUsuario($correo);

$mensaje = $_SESSION["mensaje"] ?? null;
unset($_SESSION["mensaje"]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoRetorno CR - Mis solicitudes</title>
    <link rel="stylesheet" href="../DANIELGV-inicio-registro-login/css/estilos.css">
    <link rel="stylesheet" href="css/persona3.css">
</head>
<body>
<div class="contenedor">

    <header>
        <h1>EcoRetorno CR</h1>
        <p>Historial de solicitudes de retiro</p>
    </header>

    <nav>
        <ul>
            <li><a href="../DANIELGV-inicio-registro-login/index.php">Inicio</a></li>
            <li><a href="simulador.php">Simulador de máquina</a></li>
            <li><a href="retiro.php">Solicitar retiro</a></li>
            <li><a href="solicitudes.php">Mis solicitudes</a></li>
            <li><a href="../EMILY-depositos-recompensas/panel.php">Mi panel</a></li>
        </ul>
    </nav>

    <?php if ($mensaje): ?>
        <div class="alerta alerta-<?= htmlspecialchars($mensaje["tipo"]) ?>">
            <?= htmlspecialchars($mensaje["texto"]) ?>
        </div>
    <?php endif; ?>

    <section class="tarjeta">
        <h2>Mis solicitudes de retiro</h2>

        <?php if (count($solicitudes) === 0): ?>
            <p class="vacio">Todavía no ha realizado solicitudes de retiro.</p>
        <?php else: ?>
            <div class="tabla-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Método</th>
                            <th>Destino</th>
                            <th>Monto</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solicitudes as $s): ?>
                            <tr>
                                <td><?= htmlspecialchars($s["Fecha"]) ?></td>
                                <td><?= htmlspecialchars($s["Metodo"]) ?></td>
                                <td><?= htmlspecialchars($s["Destino"]) ?></td>
                                <td>₡<?= number_format($s["Monto"], 0, ",", ".") ?></td>
                                <td>
                                    <?php if ($s["Estado"] === "Pendiente"): ?>
                                        <span class="etiqueta etiqueta-no">Pendiente</span>
                                    <?php else: ?>
                                        <span class="etiqueta etiqueta-ok"><?= htmlspecialchars($s["Estado"]) ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="fila-botones">
            <a class="btn btn-primario" href="retiro.php">Nueva solicitud</a>
        </div>
    </section>

    <footer>EcoRetorno CR, Creadores del proyecto, Domian, Daniel, Claudio y Emily</footer>

</div>
</body>
</html>
