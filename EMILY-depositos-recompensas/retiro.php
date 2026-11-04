
<?php
// SOLICITUD DE RETIRO

session_start();
require_once "funciones_persona2.php";

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION["usuario"])) {
    header("Location: ../DANIELGV-inicio-registro-login/login.php");
    exit;
}

// Obtener información del ciudadano
$correo = $_SESSION["usuario"]["Correo"];
$usuario = obtenerUsuarioActual($correo);

if ($usuario === null) {
    unset($_SESSION["usuario"]);
    header("Location: ../DANIELGV-inicio-registro-login/login.php");
    exit;
}

$saldoDisponible = saldoDisponibleParaRetiro($correo, $usuario["Saldo"]);
$mensaje = $_SESSION["mensaje"] ?? null;
unset($_SESSION["mensaje"]);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoRetorno CR - Solicitar Retiro</title>
    <link rel="stylesheet" href="../DANIELGV-inicio-registro-login/css/estilos.css">
    <link rel="stylesheet" href="css/persona2.css">
</head>
<body>
<div class="contenedor">

    <header>
        <h1>EcoRetorno CR</h1>
        <p>Solicitud de retiro de recompensas</p>
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
        <h2>Mi información</h2>
        <p><strong>Nombre:</strong> <?= htmlspecialchars($usuario["Nombre"]) ?></p>
        <p><strong>Correo:</strong> <?= htmlspecialchars($usuario["Correo"]) ?></p>

        <h3>Saldo acumulado</h3>
        <p class="saldo">₡<?= number_format($usuario["Saldo"], 0, ",", ".") ?></p>

        <h3>Saldo disponible para retiro</h3>
        <p class="saldo-disponible">₡<?= number_format($saldoDisponible, 0, ",", ".") ?></p>
        <p>El saldo disponible considera las solicitudes de retiro que todavía están pendientes.</p>
    </section>

    <!-- Formulario de solicitud de retiro -->
    <section class="tarjeta">
        <h2>Solicitar retiro de dinero</h2>
        <p>Complete el siguiente formulario para solicitar el retiro de sus recompensas acumuladas.</p>

        <form method="post" action="procesar_retiro.php">
            <label for="metodo">Método de retiro</label>
            <select id="metodo" name="metodo" required>
                <option value="">Seleccione un método</option>
                <option value="SINPE Móvil">SINPE Móvil</option>
                <option value="Transferencia bancaria">Transferencia bancaria</option>
            </select>

            <label for="destino">Número de teléfono o cuenta IBAN</label>
            <input type="text" id="destino" name="destino" maxlength="40" required
                   placeholder="Teléfono de 8 dígitos o IBAN CR">
            <p>Para SINPE Móvil, ingrese un teléfono de 8 dígitos. Para transferencia bancaria, utilice un IBAN costarricense de prueba.</p>

            <label for="monto">Monto que desea retirar (₡)</label>
            <input type="number" id="monto" name="monto" min="1" max="<?= $saldoDisponible ?>"
                   step="1" required placeholder="Ejemplo: 1000">

            <div class="fila-botones">
                <button class="btn btn-primario" type="submit" <?= $saldoDisponible <= 0 ? "disabled" : "" ?>>
                    Solicitar retiro
                </button>
                <a class="btn btn-secundario" href="panel.php">Volver a mi panel</a>
            </div>
        </form>

        <?php if ($saldoDisponible <= 0): ?>
            <p class="vacio">Actualmente no tiene saldo disponible para solicitar un retiro.</p>
        <?php endif; ?>
    </section>

    <!-- Información del prototipo -->
    <section class="tarjeta">
        <h2>Información importante</h2>
        <p>Las solicitudes de retiro forman parte de una simulación académica de EcoRetorno CR.</p>
        <p>Al realizar una solicitud, esta quedará registrada con el estado "Pendiente".</p>
        <p>No se realizará ninguna transferencia real de dinero mediante SINPE Móvil ni mediante entidades bancarias.</p>
    </section>

    <footer>EcoRetorno CR - Proyecto académico de reciclaje</footer>
</div>
</body>
</html>

