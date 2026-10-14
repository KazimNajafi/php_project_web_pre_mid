document.addEventListener('DOMContentLoaded', function () {
    const flashBars = document.querySelectorAll('.flash-banner');

    flashBars.forEach(function (flashBar) {
        setTimeout(function () {
            flashBar.style.opacity = '0';
            flashBar.style.transition = 'opacity 0.35s ease';
            setTimeout(function () {
                flashBar.remove();
            }, 350);
        }, 4800);
    });

    const forms = document.querySelectorAll('form');
    forms.forEach(function (form) {
        form.addEventListener('submit', function () {
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                const originalText = submitButton.textContent;
                submitButton.textContent = 'Please wait...';
                submitButton.dataset.originalText = originalText;
            }
        });
    });
});
