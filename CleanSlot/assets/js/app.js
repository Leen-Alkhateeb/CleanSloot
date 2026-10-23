// 1) Light / dark mode button (the first choice is set in the header, before the page paints)
const themeButton = document.getElementById('theme-toggle');
if (themeButton) {
    themeButton.addEventListener('click', () => {
        const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
        document.documentElement.dataset.theme = next;
        localStorage.setItem('theme', next);
    });
}

// 2) Quantity buttons (+ and -) on the service cards
document.querySelectorAll('.qty').forEach((box) => {
    const input = box.querySelector('input');
    box.querySelectorAll('button').forEach((button) => {
        button.addEventListener('click', () => {
            const value = (parseInt(input.value, 10) || 1) + Number(button.dataset.step);
            input.value = Math.min(20, Math.max(1, value));
        });
    });
});

// 3) Character counter on the message forms
const area = document.querySelector('textarea[data-count]');
const counter = document.getElementById('count');
if (area && counter) {
    const update = () => { counter.textContent = area.value.length; };
    area.addEventListener('input', update);
    update();
}

// 4) Live preview of the picture chosen in Admin > Services & Shop
const photo = document.getElementById('photo');
const preview = document.getElementById('preview');
if (photo && preview) {
    photo.addEventListener('change', () => {
        if (photo.files[0]) preview.src = URL.createObjectURL(photo.files[0]);
    });
}

// 5) Payment method: show the (inactive) card or CliQ box. Orders are always paid on delivery.
document.querySelectorAll('input[name="pay_ui"]').forEach((radio) => {
    radio.addEventListener('change', () => {
        document.getElementById('pay-card').hidden = radio.value !== 'card';
        document.getElementById('pay-cliq').hidden = radio.value !== 'cliq';
    });
});
