EcoRetorno CR

Proyecto final, avance II

Aplicación web inspirada en los sistemas de depósito y retorno de los países nórdicos: los
ciudadanos reciben una recompensa económica por devolver envases reciclables en máquinas
instaladas por las municipalidades.

| Integrante | Módulo | Carpeta |
|---|---|---|
| DANIEL G | Inicio, Registro e Inicio de sesión | DANIELGV-inicio-registro-login/ |
| EMILY | Panel Ciudadano (saldo, código, historial, máquinas) | EMILY-panel-ciudadano/ |
| SEBASTIAN DOMIAN | Depósito (simulador de máquina) y retiro de saldo | SEBASTIAN-deposito-retiro/ |
| CLAUDIO JIMENEZ | Panel Administrador municipal | CLAUDIO-panel-admin/ |

 ## Cómo está organizado
 Cada uno de nosotros lo que va a hacer es trabajar dentro de una carpeta propia, con nuestros
 propios archivos, que cada archivo lo que nos va a simular es la BD, mientras la conectamos con SQL, y
 asi cada uno de nosotros va a poder hacer los commits sin hacer errores con las partes de los demas.


Más adelante, cuando se integren los módulos y se conecte una base de datos real, estas
carpetas se unificarán en un solo sitio.

## Tecnologías utilizadas

HTML5, CSS3, JavaScript, PHP. Posterior lo conectamos con MYSQL



## Diagrama relacional 
Este se va a ver documentado y con la captura pero en el IEEE, segun lo que dice y pide este avance.




## Commits
Cada uno de nosotros va a ir haciendo sus commits correspondientes.




## Estado actual y pendientes

### Pendientes conocidos
- Las pantallas del administrador (carpeta `CLAUDIO-panel-admin`) todavía no tienen botón para cerrar sesión ni para volver al sitio. Por ahora, para salir hay que entrar al inicio (`index.php`) y cerrar sesión desde ahí.
- Las máquinas que administra el panel de administración se guardan en su propio archivo y todavía no se reflejan en la página de inicio ni en el panel del ciudadano, que usan una lista fija.
- El administrador se identifica con un correo fijo en el código, mientras no exista un campo de rol en la base de datos.

