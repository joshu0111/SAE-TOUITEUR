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
    const remove     = document.getElementById('imageRemove');
    if (!input || !drop) return;

    const TAILLE_MAX = 2 * 1024 * 1024; // 2 Mo, comme côté PHP

    function afficherApercu(file) {
        if (!file) return;
        if (!file.type.startsWith('image/')) {
            alert('Ce fichier n\'est pas une image.');
            input.value = '';
            return;
        }
        if (file.size > TAILLE_MAX) {
            alert('Image trop lourde (2 Mo maximum).');
            input.value = '';
            return;
        }
        previewImg.src = URL.createObjectURL(file);
        preview.hidden = false;
        drop.hidden = true;
    }

    input.addEventListener('change', function () {
        afficherApercu(input.files[0]);
    });

    // Glisser-déposer
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

    // Retirer l'image
    remove.addEventListener('click', function () {
        input.value = '';
        URL.revokeObjectURL(previewImg.src);
        previewImg.removeAttribute('src');
        preview.hidden = true;
        drop.hidden = false;
    });
})();
