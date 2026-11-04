
<?php
session_start();
require_once "funciones.php";

$mensaje = $_SESSION["mensaje"] ?? null;
unset($_SESSION["mensaje"]);

//aqui si ya el usuario existe que entre de una vez.
$usuarioActivo = $_SESSION["usuario"] ?? null;

//aqui pusimos algunas maquinas para dejar los envaces, pero de ejemplo y que sea visual
$maquinas = array(
    array("Canton" => "Cartago", "Ubicacion" => "Parque Central de Cartago", "Estado" => "Activa"),
    array("Canton" => "Paraíso", "Ubicacion" => "Parque de Paraíso", "Estado" => "Activa"),
    array("Canton" => "San José", "Ubicacion" => "Parque La Sabana, Sabana Sur", "Estado" => "Activa"),
    array("Canton" => "Heredia", "Ubicacion" => "Mercado Municipal de Heredia", "Estado" => "Próximamente")
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoRetorno CR - Inicio</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<div class="contenedor">
    <header>
        <h1>EcoRetorno CR</h1>
        <p>¿Reciclaje quen nos devuelve dinero?, incentivo de cada municipalidad!!</p>
    </header>

        <nav>
        <ul>
            <li><a href="index.php">Inicio</a></li>
            <li><a href="#como-funciona">¿Cómo funciona?</a></li>
            <li><a href="#maquinas">Máquinas de recicaje</a></li>

            <?php if ($usuarioActivo): ?>
                <li>Hola, <?= htmlspecialchars($usuarioActivo["Nombre"]) ?> (₡<?= $usuarioActivo["Saldo"] ?>)</li>
                <li><a href="../EMILY-depositos-recompensas/panel.php">Mi panel</a></li>
                <li>
                    <form method="post" action="procesar.php" style="display:inline;">
                        <input type="hidden" name="accion" value="cerrar_sesion">
                        <button class="btn btn-peligro" type="submit">Cerrar sesión</button>
                    </form>
                </li>
            <?php else: ?>
                
                <li><a href="registro.php">Registrarse</a></li>
                <li><a href="login.php">Iniciar sesión</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <?php if ($mensaje): ?>
        <div class="alerta alerta-<?= htmlspecialchars($mensaje["tipo"]) ?>">
            <?= htmlspecialchars($mensaje["texto"]) ?>
        </div>
    <?php endif; ?>

    <section id="como-funciona" class="tarjeta">
        <h2>¿Cómo funciona?</h2>
        <p>
            EcoRetorno CR se inspira en los sistemas de depósito y retorno que utilizan
            países nórdicos como Suecia: cada cosita que vos lleves y sea reciclable se devuelve, y se
            convierte en dinero real para vos!!!
        </p>
        <ol class="pasos">
            <li>Cree su cuenta gratis y obtenga su código único.</li>
            <li>Lleve sus botellas, latas u otros cositas a una máquina de su cantón.</li>
            <li>Identifíquese con su código y deposite los envases.</li>
            <li>El monto se acumula en su saldo dentro de la aplicación.</li>
            <li>Solicite su dinero por SINPE Móvil, transferencia o voucher.</li>
        </ol>
    </section>

    <section id="maquinas" class="tarjeta">
        <h2>Máquinas de Reciclaje por cada cantón</h2>
        <?php if (count($maquinas) === 0): ?>
            <p class="vacio">Todavía no hay máquinas registradas.</p>
        <?php else: ?>
            <div class="tabla-scroll">
                <table>
                    <thead>
                        <tr><th>Cantón</th><th>Ubicación</th><th>Estado</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($maquinas as $m): ?>
                            <tr>
                                <td><?= htmlspecialchars($m["Canton"]) ?></td>
                                <td><?= htmlspecialchars($m["Ubicacion"]) ?></td>
                                <td>
                                    <?php if ($m["Estado"] === "Activa"): ?>
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
        <?php endif; ?>
    </section>

    <?php if (!$usuarioActivo): ?>
        <section class="tarjeta">
            <h2>Empiece a reciclar con plata de verdad</h2>
            <div class="fila-botones">
                <a class="btn btn-primario" href="registro.php">Crear cuenta</a>
                <a class="btn btn-secundario" href="login.php">Ya tengo cuenta</a>
            </div>
        </section>
    <?php endif; ?>

    <footer>EcoRetorno CR, Creadores del proyecto, Domian, Daniel, Claudio y Emily</footer>
</div>
</body>
</html>

