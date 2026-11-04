
 // CODIGO QR DEL CIUDADANO

document.addEventListener("DOMContentLoaded", function () {
    // Busca elementos del código QR
    let contenedorQR = document.getElementById("codigoQR");
    let textoCodigo = document.getElementById("textoCodigo");
    if (contenedorQR && textoCodigo) {
        let codigo = textoCodigo.textContent.trim();
        // Genera código QR visual
        if (codigo !== "" && typeof QRCode !== "undefined") {

            new QRCode(contenedorQR, {
                text: codigo,
                width: 180,
                height: 180,
                colorDark: "#1b5e20",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });

        }
    }
});