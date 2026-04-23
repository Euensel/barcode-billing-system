// assets/js/scanner.js
document.addEventListener('DOMContentLoaded', function() {
    const scanBtn = document.getElementById('scan-btn');
    const videoElem = document.getElementById('scanner-video');
    const resultDiv = document.getElementById('scanner-result');
    const codeInput = document.getElementById('code_barre');
    let codeReader = null;

    if (scanBtn) {
        scanBtn.addEventListener('click', async () => {
            if (!codeReader) {
                codeReader = new ZXing.BrowserMultiFormatReader();
            }

            // 1. CACHER LE BOUTON ET AFFICHER LA VIDÉO
            scanBtn.style.display = 'none';
            videoElem.style.display = 'block';
            resultDiv.innerHTML = '<i class="bx bx-camera"></i> Initialisation de la caméra...';

            try {
                // 2. FORCER LA CAMÉRA ARRIÈRE (en mode 'environment')
                //    Envoyer 'undefined' force ZXing à utiliser la caméra par défaut, qui est souvent l'arrière.
                //    C'est la clé pour utiliser la meilleure caméra.
                await codeReader.decodeFromVideoDevice(undefined, 'scanner-video', (result, err) => {
                    if (result) {
                        // Code-barres trouvé !
                        const code = result.getText();
                        resultDiv.innerHTML = `<i class='bx bx-check-circle'></i> Code scanné : ${code}`;
                        if (codeInput) {
                            codeInput.value = code;
                            // Redirige pour vérifier l'existence du produit
                            window.location.href = `?code_barre=${encodeURIComponent(code)}`;
                        }
                    } else if (err && !(err instanceof ZXing.NotFoundException)) {
                        console.error(err);
                        resultDiv.innerHTML = `<i class='bx bx-error-circle'></i> Erreur de scan: ${err.message}`;
                    }
                });
                
                resultDiv.innerHTML = '<i class="bx bx-camera"></i> Caméra active. Placez un code-barres devant l\'objectif.';

            } catch (error) {
                console.error("Erreur lors de l'accès à la caméra :", error);
                resultDiv.innerHTML = '<i class="bx bx-error-circle"></i> Impossible d\'accéder à la caméra. Vérifiez les permissions.';
                // En cas d'erreur, on réaffiche le bouton
                scanBtn.style.display = 'inline-flex';
                videoElem.style.display = 'none';
            }
        });
    }
});