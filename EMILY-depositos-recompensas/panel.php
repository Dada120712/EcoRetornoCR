
<?php
// PANEL DEL CIUDADANO

session_start();
require_once "funciones_persona2.php";

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION["usuario"])) {
    header("Location: ../DANIELGV-inicio-registro-login/login.php");
    exit;
}

// Obtener información actualizada del ciudadano
$correo = $_SESSION["usuario"]["Correo"];
$usuario = obtenerUsuarioActual($correo);

if ($usuario === null) {
    unset($_SESSION["usuario"]);
    header("Location: ../DANIELGV-inicio-registro-login/login.php");
    exit;
}

$historial = historialUsuario($correo);
$maquinas = obtenerMaquinas();
$retiros = retirosUsuario($correo);
$saldoDisponible = saldoDisponibleParaRetiro($correo, $usuario["Saldo"]);

$mensaje = $_SESSION["mensaje"] ?? null;
unset($_SESSION["mensaje"]);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoRetorno CR - Mi Panel</title>
    <link rel="stylesheet" href="../DANIELGV-inicio-registro-login/css/estilos.css">
    <link rel="stylesheet" href="css/persona2.css">
</head>
<body>
<div class="contenedor">

    <header>
        <h1>EcoRetorno CR</h1>
        <p>Mi panel de reciclaje y recompensas</p>
    </header>

    <!-- Menú de navegación -->
    <nav>
        <ul>
            <li><a href="../DANIELGV-inicio-registro-login/index.php">Inicio</a></li>
            <li><a href="panel.php">Mi panel</a></li>
            <li><a href="../SEBASTIAN-deposito-retiro/simulador.php">Simulador de máquina</a></li>
            <li><a href="../SEBASTIAN-deposito-retiro/retiro.php">Solicitar retiro</a></li>
            <li>
                <form method="post" action="../DANIELGV-inicio-registro-login/procesar.php">
                    <input type="hidden" name="accion" value="cerrar_sesion">
                    <button class="btn btn-peligro" type="submit">Cerrar sesión</button>
                </form>
            </li>
        </ul>
    </nav>

    <?php if ($mensaje): ?>
        <div class="alerta alerta-<?= htmlspecialchars($mensaje["tipo"]) ?>">
            <?= htmlspecialchars($mensaje["texto"]) ?>
        </div>
    <?php endif; ?>

    <!-- Información del ciudadano y código QR -->
    <section class="tarjeta">
        <h2>Bienvenido, <?= htmlspecialchars($usuario["Nombre"]) ?></h2>
        <p><strong>Correo electrónico:</strong> <?= htmlspecialchars($usuario["Correo"]) ?></p>
        <h3>Mi código de identificación</h3>
        <p>Este es su código único para identificarse al realizar una entrega de materiales reciclables.</p>

        <div class="codigo-usuario">
            <div id="codigoQR" style="display: flex; justify-content: center; margin-bottom: 15px;"></div>
            <strong id="textoCodigo"><?= htmlspecialchars($usuario["CodigoQR"]) ?></strong>
        </div>

        <p>Este código está asociado a su cuenta de EcoRetorno CR.</p>
    </section>

    <!-- Saldos y recompensas -->
    <section class="tarjeta">
        <h2>Mis recompensas</h2>
        <h3>Saldo acumulado</h3>
        <p class="saldo">₡<?= number_format($usuario["Saldo"], 0, ",", ".") ?></p>

        <h3>Disponible para solicitar retiro</h3>
        <p class="saldo-disponible">₡<?= number_format($saldoDisponible, 0, ",", ".") ?></p>

        <p>Cada vez que deposite materiales reciclables, recibirá una recompensa que se agregará a su saldo.</p>

        <div class="fila-botones">
            <a class="btn btn-primario" href="../SEBASTIAN-deposito-retiro/simulador.php">Ir al simulador de máquina</a>
            <a class="btn btn-secundario" href="../SEBASTIAN-deposito-retiro/retiro.php">Solicitar retiro</a>
        </div>
    </section>

    <!-- Historial de depósitos -->
