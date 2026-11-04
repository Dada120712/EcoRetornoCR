
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
            <li><a href="deposito.php">Registrar depósito</a></li>
            <li><a href="retiro.php">Solicitar retiro</a></li>
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
            <a class="btn btn-primario" href="deposito.php">Registrar depósito</a>
            <a class="btn btn-secundario" href="retiro.php">Solicitar retiro</a>
        </div>
    </section>

    <!-- Historial de depósitos -->
    <section class="tarjeta">
        <h2>Mi historial de depósitos</h2>

        <?php if (count($historial) === 0): ?>
            <p class="vacio">Todavía no ha registrado depósitos de reciclaje.</p>
        <?php else: ?>
            <div class="tabla-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Máquina</th>
                            <th>Material</th>
                            <th>Cantidad</th>
                            <th>Recompensa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historial as $deposito): ?>
                            <tr>
                                <td><?= htmlspecialchars($deposito["Fecha"]) ?></td>
                                <td><?= htmlspecialchars($deposito["Maquina"]) ?></td>
                                <td><?= htmlspecialchars($deposito["Material"]) ?></td>
                                <td><?= $deposito["Cantidad"] ?></td>
                                <td>₡<?= number_format($deposito["Recompensa"], 0, ",", ".") ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <!-- Máquinas disponibles -->
    <section class="tarjeta">
        <h2>Máquinas de reciclaje disponibles</h2>
        <p>Consulte las máquinas de reciclaje y sus ubicaciones para realizar sus depósitos.</p>

        <div class="tabla-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Cantón</th>
                        <th>Ubicación</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($maquinas as $maquina): ?>
                        <tr>
                            <td><?= htmlspecialchars($maquina["Canton"]) ?></td>
                            <td><?= htmlspecialchars($maquina["Ubicacion"]) ?></td>
                            <td>
                                <?php if ($maquina["Estado"] === "Activa"): ?>
                                    <span class="etiqueta etiqueta-ok">Activa</span>
                                <?php else: ?>
                                    <span class="etiqueta etiqueta-no">Próximamente</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Solicitudes de retiro -->
    <section class="tarjeta">
        <h2>Mis solicitudes de retiro</h2>

        <?php if (count($retiros) === 0): ?>
            <p class="vacio">Todavía no ha realizado solicitudes de retiro.</p>
        <?php else: ?>
            <div class="tabla-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Método</th>
                            <th>Monto</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($retiros as $retiro): ?>
                            <tr>
                                <td><?= htmlspecialchars($retiro["Fecha"]) ?></td>
                                <td><?= htmlspecialchars($retiro["Metodo"]) ?></td>
                                <td>₡<?= number_format($retiro["Monto"], 0, ",", ".") ?></td>
                                <td><?= htmlspecialchars($retiro["Estado"]) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <footer>EcoRetorno CR - Proyecto académico de reciclaje</footer>
</div>

<!-- Generación del código QR -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="js/ecoqr.js"></script>
</body>
</html>
