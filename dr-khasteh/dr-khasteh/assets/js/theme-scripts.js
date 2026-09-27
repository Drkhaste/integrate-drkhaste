document.addEventListener('DOMContentLoaded', () => {
    // Selectors
    const themeToggle = document.getElementById('theme-toggle');
    const themeIcon = document.getElementById('theme-icon');
    const fontToggle = document.getElementById('font-settings-toggle');
    const fontPanel = document.getElementById('font-settings-panel');
    const fontFamilySelect = document.getElementById('font-family-select');
    const fontSizeDisplay = document.getElementById('font-size-display');
    const fontSizeIncrease = document.getElementById('font-size-increase');
    const fontSizeDecrease = document.getElementById('font-size-decrease');
    const backToTopBtn = document.getElementById('back-to-top');
    const telegramLink = document.getElementById('telegram-link');

    // Initial State from LocalStorage
    const savedFont = localStorage.getItem('fontFamily') || "'IBM Plex Sans Arabic'";
    const savedFontSize = localStorage.getItem('fontSize') || '16';

    if (fontFamilySelect) fontFamilySelect.value = savedFont;
    if (fontSizeDisplay) fontSizeDisplay.textContent = savedFontSize;

    // Theme Toggle Logic
    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const isDark = document.body.classList.toggle('dark-mode');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            updateThemeIcon(isDark);
        });
    }

    function updateThemeIcon(isDark) {
        if (!themeIcon) return;
        if (isDark) {
            themeIcon.classList.remove('fa-moon');
            themeIcon.classList.add('fa-sun');
        } else {
            themeIcon.classList.remove('fa-sun');
            themeIcon.classList.add('fa-moon');
        }
    }

    // Set initial icon
    updateThemeIcon(document.body.classList.contains('dark-mode'));

    // Font Settings Logic
    if (fontToggle) {
        fontToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            fontPanel.classList.toggle('active');
        });
    }

    if (fontPanel) {
        fontPanel.addEventListener('click', (e) => {
            e.stopPropagation();
        });
    }

    window.addEventListener('click', () => {
        if (fontPanel) fontPanel.classList.remove('active');
    });

    if (fontFamilySelect) {
        fontFamilySelect.addEventListener('change', (e) => {
            const font = e.target.value;
            document.documentElement.style.setProperty('--current-font', font);
            localStorage.setItem('fontFamily', font);
        });
    }

    if (fontSizeIncrease) {
        fontSizeIncrease.addEventListener('click', () => {
            changeFontSize(1);
        });
    }

    if (fontSizeDecrease) {
        fontSizeDecrease.addEventListener('click', () => {
            changeFontSize(-1);
        });
    }

    function changeFontSize(delta) {
        let currentSize = parseInt(fontSizeDisplay.textContent);
        currentSize += delta;
        if (currentSize < 12) currentSize = 12;
        if (currentSize > 30) currentSize = 30;
        fontSizeDisplay.textContent = currentSize;
        document.documentElement.style.setProperty('--current-font-size', currentSize + 'px');
        localStorage.setItem('fontSize', currentSize);
    }

    // Back to Top Logic
    window.addEventListener('scroll', () => {
        if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
            backToTopBtn.style.display = "block";
        } else {
            backToTopBtn.style.display = "none";
        }
    });

    if (backToTopBtn) {
        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // Telegram Link Logic
    if (telegramLink) {
        telegramLink.addEventListener('click', (e) => {
            e.preventDefault();
            const tgLink = "tg://resolve?domain=abolfazllx";
            const webLink = "https://t.me/abolfazllx";
            const timeout = setTimeout(function () {
                window.location.href = webLink;
            }, 500);
            window.location.href = tgLink;
            window.onblur = function () {
                clearTimeout(timeout);
            };
        });
    }
});
