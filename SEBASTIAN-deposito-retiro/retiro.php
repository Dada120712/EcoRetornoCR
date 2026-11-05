<?php

// solicitud de retiro 

session_start();
require_once "funciones.php";

//aqui el que quiere retirar tiene que haber iniciado sesion
if (!isset($_SESSION["usuario"])) {
    header("Location: ../DANIELGV-inicio-registro-login/login.php");
    exit;
}

$correo = $_SESSION["usuario"]["Correo"];
$usuario = buscarUsuarioPorCorreo($correo, leerUsuarios());

if ($usuario === null) {
    unset($_SESSION["usuario"]);
    header("Location: ../DANIELGV-inicio-registro-login/login.php");
    exit;
}

$disponible = saldoDisponible($correo, $usuario["Saldo"]);

$mensaje = $_SESSION["mensaje"] ?? null;
unset($_SESSION["mensaje"]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoRetorno CR - Solicitar retiro</title>
    <link rel="stylesheet" href="../DANIELGV-inicio-registro-login/css/estilos.css">
    <link rel="stylesheet" href="css/persona3.css">
</head>
<body>
<div class="contenedor">

    <header>
        <h1>EcoRetorno CR</h1>
        <p>Solicitud de retiro de recompensas</p>
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
        <h2>Mi saldo</h2>
        <p><strong>Nombre:</strong> <?= htmlspecialchars($usuario["Nombre"]) ?></p>

        <h3>Saldo acumulado</h3>
        <p class="saldo">₡<?= number_format($usuario["Saldo"], 0, ",", ".") ?></p>

        <h3>Disponible para retiro</h3>
        <p class="saldo-disponible">₡<?= number_format($disponible, 0, ",", ".") ?></p>
        <p>El saldo disponible ya descuenta las solicitudes que están pendientes.</p>
    </section>

    <section class="tarjeta">
        <h2>Solicitar retiro</h2>

        <form method="post" action="procesar_retiro.php">
            <input type="hidden" name="accion" value="solicitar">

            <label for="metodo">Método de retiro</label>
            <select id="metodo" name="metodo" required>
                <option value="">Seleccione un método</option>
                <option value="SINPE Móvil">SINPE Móvil</option>
                <option value="Transferencia bancaria">Transferencia bancaria</option>
            </select>

            <label for="destino">Número de teléfono o cuenta IBAN</label>
            <input type="text" id="destino" name="destino" maxlength="40" required
                   placeholder="Teléfono de 8 dígitos o IBAN CR">
            <p>Para SINPE Móvil digite un teléfono de 8 dígitos. Para transferencia bancaria, un IBAN de Costa Rica (CR + 20 números).</p>

            <label for="monto">Monto a retirar (₡)</label>
            <input type="number" id="monto" name="monto" min="1" max="<?= $disponible ?>" step="1" required
                   placeholder="Ejemplo: 1000">

            <div class="fila-botones">
                <button class="btn btn-primario" type="submit" <?= $disponible <= 0 ? "disabled" : "" ?>>Solicitar retiro</button>
                <a class="btn btn-secundario" href="solicitudes.php">Ver mis solicitudes</a>
            </div>
        </form>

        <?php if ($disponible <= 0): ?>
            <p class="vacio">Por ahora no tiene saldo disponible para solicitar un retiro.</p>
        <?php endif; ?>
    </section>

    <section class="tarjeta">
        <h2>Información importante</h2>
        <p>Al enviar la solicitud queda registrada con el estado "Pendiente" hasta que el administrador municipal la gestione.</p>
        <p>Es un prototipo académico: no se hace ninguna transferencia real por SINPE Móvil ni por ningún banco.</p>
    </section>

    <footer>EcoRetorno CR, Creadores del proyecto, Domian, Daniel, Claudio y Emily</footer>

</div>
</body>
</html>
