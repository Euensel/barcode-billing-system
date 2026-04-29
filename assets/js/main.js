
document.addEventListener('DOMContentLoaded', function() {
    // Menu burger
    const menuToggle = document.getElementById('menu-toggle');
    const mainNav = document.getElementById('main-nav');
    
    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', function() {
            mainNav.classList.toggle('open');
            // Changer l'icône du burger
            const icon = menuToggle.querySelector('i');
            if (icon) {
                if (mainNav.classList.contains('open')) {
                    icon.className = 'bx bx-x';
                } else {
                    icon.className = 'bx bx-menu';
                }
            }
        });
        
        // Fermer le menu quand on clique sur un lien
        const navLinks = mainNav.querySelectorAll('a');
        navLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                mainNav.classList.remove('open');
                const icon = menuToggle.querySelector('i');
                if (icon) {
                    icon.className = 'bx bx-menu';
                }
            });
        });
    }
});