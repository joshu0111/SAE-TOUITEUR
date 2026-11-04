// Interactions uniquement : le rendu est fait côté PHP.
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