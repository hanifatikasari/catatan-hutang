// Prevent accidental value changes when the mouse wheel is over a numeric input.
document.addEventListener('wheel', function (event) {
    if (event.target instanceof HTMLInputElement && event.target.type === 'number') {
        event.preventDefault();
    }
}, { passive: false });
