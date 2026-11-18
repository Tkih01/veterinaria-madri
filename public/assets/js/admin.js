/**
 * Script del Panel Administrativo - Veterinaria Madri
 * Genera el código QR localmente y gestiona la impresión del carnet.
 * Cumple con Content-Security-Policy (script-src 'self').
 */
document.addEventListener('DOMContentLoaded', function () {
    const qrBox = document.getElementById('qr-code-target');
    if (qrBox && typeof QRCode !== 'undefined') {
        const targetUrl = qrBox.getAttribute('data-url');
        if (targetUrl) {
            qrBox.innerHTML = '';
            new QRCode(qrBox, {
                text: targetUrl,
                width: 128,
                height: 128,
                colorDark: '#0F172A',
                colorLight: '#FFFFFF',
                correctLevel: QRCode.CorrectLevel.M
            });
        }
    }

    const printBtn = document.getElementById('btn-imprimir-carnet');
    if (printBtn) {
        printBtn.addEventListener('click', function () {
            window.print();
        });
    }
});