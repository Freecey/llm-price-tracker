{{-- Easter Eggs & Fun Features --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // === KONAMI CODE ===
    const konamiCode = ['ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight', 'b', 'a'];
    let konamiIndex = 0;
    
    document.addEventListener('keydown', (e) => {
        if (e.key === konamiCode[konamiIndex]) {
            konamiIndex++;
            if (konamiIndex === konamiCode.length) {
                activateHackerMode();
                konamiIndex = 0;
            }
        } else {
            konamiIndex = 0;
        }
    });
    
    function activateHackerMode() {
        document.body.style.transition = 'all 0.5s';
        document.body.style.background = '#000';
        document.body.style.fontFamily = '"Courier New", monospace';
        
        // Changer toutes les couleurs
        document.querySelectorAll('.card, .table, .navbar, .btn, .badge').forEach(el => {
            el.style.borderColor = '#0f0';
            el.style.color = '#0f0';
            el.style.background = '#001a00';
        });
        
        document.querySelectorAll('a').forEach(el => {
            el.style.color = '#0f0';
        });
        
        // Message
        const msg = document.createElement('div');
        msg.style.cssText = 'position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:#000;border:2px solid #0f0;padding:20px;z-index:9999;font-family:monospace;color:#0f0;text-align:center;';
        msg.innerHTML = `
            <h2 style="margin:0 0 10px 0;">⌬ HACKER MODE ACTIVATED</h2>
            <p style="margin:0;">Kyra approves. Welcome to the matrix, Cey.</p>
            <p style="margin:10px 0 0 0;font-size:0.8em;opacity:0.7;">Press F5 to exit</p>
        `;
        document.body.appendChild(msg);
        
        setTimeout(() => msg.remove(), 5000);
        
        // Stocker dans localStorage
        localStorage.setItem('kyra_hacker_mode', 'activated');
    }
    
    // Vérifier si déjà activé
    if (localStorage.getItem('kyra_hacker_mode') === 'activated') {
        // Ajouter un petit badge discret
        const badge = document.createElement('div');
        badge.style.cssText = 'position:fixed;bottom:10px;left:10px;background:#000;border:1px solid #0f0;padding:5px 10px;font-family:monospace;font-size:0.7em;color:#0f0;z-index:9998;opacity:0.5;';
        badge.textContent = '⌬ hacker mode';
        badge.title = 'Activé via Konami Code';
        document.body.appendChild(badge);
    }
    
    // === BOUTON SLOT MACHINE ===
    const slotMachineBtn = document.getElementById('slotMachineBtn');
    if (slotMachineBtn) {
        slotMachineBtn.addEventListener('click', () => {
            // Animation de "roulette"
            const names = [
                'GPT-4', 'Claude', 'Llama 3', 'Gemma', 'Qwen',
                'Mistral', 'Mixtral', 'Yi', 'Command R', 'DeepSeek'
            ];
            let iterations = 0;
            const maxIterations = 15;
            const interval = setInterval(() => {
                slotMachineBtn.textContent = `🎰 ${names[Math.floor(Math.random() * names.length)]}`;
                iterations++;
                if (iterations >= maxIterations) {
                    clearInterval(interval);
                    // Rediriger vers un modèle aléatoire
                    fetch('/api/random-model')
                        .then(res => res.json())
                        .then(data => {
                            window.location.href = `/model/${data.id}`;
                        })
                        .catch(() => {
                            window.location.href = '/';
                        });
                }
            }, 100);
        });
    }
});
</script>
