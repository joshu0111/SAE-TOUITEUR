
(function () {
    const textarea = document.getElementById('newTouite');
    const count = document.getElementById('count');
    if (textarea && count) {
        textarea.addEventListener('input', function () {
            const n = textarea.value.length;
            count.textContent = n + ' / 235';
            count.classList.toggle('warn', n > 210);
        });
    }
})();

(function () {
    const input      = document.getElementById('imageInput');
    const drop       = document.getElementById('imageDrop');
    const preview    = document.getElementById('imagePreview');
    const previewImg = document.getElementById('imagePreviewImg');
    const status     = document.getElementById('imageStatus');
    const remove     = document.getElementById('imageRemove');
    if (!input || !drop || !preview || !previewImg || !remove) return;

    const TAILLE_MAX = 2 * 1024 * 1024; // 2 Mo, comme côté PHP

    function formaterTaille(octets) {
        if (octets < 1024) return octets + ' o';
        if (octets < 1024 * 1024) return Math.round(octets / 1024) + ' Ko';
        return (octets / (1024 * 1024)).toFixed(1) + ' Mo';
    }

    function afficherApercu(file) {
        if (!file) return;

        if (!file.type.startsWith('image/')) {
            alert("Ce fichier n'est pas une image.");
            input.value = '';
            return;
        }
        if (file.size > TAILLE_MAX) {
            alert('Image trop lourde (2 Mo maximum).');
            input.value = '';
            return;
        }

        previewImg.src = URL.createObjectURL(file);
        if (status) {
            status.textContent = '✓ Image prête : ' + file.name + ' (' + formaterTaille(file.size) + ')';
        }
        preview.hidden = false;
        drop.hidden = true;
    }

    input.addEventListener('change', function () {
        afficherApercu(input.files[0]);
    });

    drop.addEventListener('dragover', function (e) {
        e.preventDefault();
        drop.classList.add('dragover');
    });
    drop.addEventListener('dragleave', function () {
        drop.classList.remove('dragover');
    });
    drop.addEventListener('drop', function (e) {
        e.preventDefault();
        drop.classList.remove('dragover');
        if (e.dataTransfer.files.length > 0) {
            input.files = e.dataTransfer.files;
            input.dispatchEvent(new Event('change'));
        }
    });

    remove.addEventListener('click', function () {
      input.value = '';
      // Liberation de la ram sinon est garder en memoire
      URL.revokeObjectURL(previewImg.src);
      previewImg.removeAttribute('src');
      if (status) status.textContent = '';
      preview.hidden = true;
      drop.hidden = false;
    });
})();

/* ---------- Recherche de tags en direct (la liste se met a jour sans recharger la page) ---------- */
(function () {
    const search = document.getElementById('tagSearch');
    const results = document.getElementById('tagResults');
    // Arrete l'execution si les elements  n'existent pas sur la page
    if (!search || !results) return;

    let minuteur = null;
    let derniere = 0;

  // Attente de 250 ms avant de lancer la nouvelle recherche
  search.addEventListener('input', function () {
      // Stop du timer
      clearTimeout(minuteur);
      minuteur = setTimeout(chercher, 250);
    });

    function chercher() {
        const q = search.value.trim();
        const tag = new URLSearchParams(window.location.search).get('tag') || '';
        const numero = ++derniere;
        // Convertit la reponse du serveur en texte
        fetch('index.php?action=rechercher-tags&q=' + encodeURIComponent(q) +
              '&tag=' + encodeURIComponent(tag), { credentials: 'same-origin' })
            .then(function (r) { return r.text(); })
            .then(function (html) {
                // Nouvelle lettre pendant que le serveur repondait, ancienne requête ignoree
                if (numero === derniere) results.innerHTML = html;
            });
    }
})();
