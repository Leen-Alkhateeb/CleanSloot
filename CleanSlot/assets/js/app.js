
const themeButton = document.getElementById('theme-toggle');
if (themeButton) {
    themeButton.addEventListener('click', () => {
        // إذا الوضع الحالي dark بيصير light والعكس
        const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
        document.documentElement.dataset.theme = next;

        // حفظ الاختيار بذاكرة المتصفح عشان يضل بعد إغلاق الصفحة
        localStorage.setItem('theme', next);
    });
}

// 2) أزرار الكمية (+ و -) على كروت الخدمات
document.querySelectorAll('.qty').forEach((box) => {
    const input = box.querySelector('input');

    box.querySelectorAll('button').forEach((button) => {
        button.addEventListener('click', () => {
            // القيمة الحالية (أو 1 إذا فاضية) + الخطوة من data-step (1 أو -1)
            const value = (parseInt(input.value, 10) || 1) + Number(button.dataset.step);

            // الكمية محصورة بين 1 و 20
            input.value = Math.min(20, Math.max(1, value));
        });
    });
});

// 3) عدّاد الأحرف بفورمات الرسائل
const area = document.querySelector('textarea[data-count]');
const counter = document.getElementById('count');
if (area && counter) {
    // بيكتب عدد الأحرف الحالي بالعدّاد
    const update = () => { counter.textContent = area.value.length; };

    area.addEventListener('input', update); // يتحدث مع كل حرف
    update();                               // يعرض الرقم الصحيح من أول لحظة
}

// 4) معاينة الصورة بصفحة الأدمن (الخدمات والمتجر)
const photo = document.getElementById('photo');
const preview = document.getElementById('preview');
if (photo && preview) {
    photo.addEventListener('change', () => {
        // رابط مؤقت للملف من جهازك، بدون رفعه للسيرفر
        if (photo.files[0]) preview.src = URL.createObjectURL(photo.files[0]);
    });
}

// 5) طريقة الدفع: إظهار صندوق البطاقة أو CliQ (واجهة فقط، الطلبات دايماً بتندفع عند التسليم)
document.querySelectorAll('input[name="pay_ui"]').forEach((radio) => {
    radio.addEventListener('change', () => {
        // hidden = true بيخفي العنصر
        document.getElementById('pay-card').hidden = radio.value !== 'card';
        document.getElementById('pay-cliq').hidden = radio.value !== 'cliq';
    });
});