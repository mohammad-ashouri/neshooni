/*
 * جلوه‌های حرکتی سایت (همراه با بخش «جلوه‌های بصری» در app.css)
 *  - نور دنبال‌کننده و کج شدن کارت‌ها با حرکت ماوس (کلاس‌های spotlight و tilt)
 *  - شمارنده متحرک اعداد:   <span x-data="countUp(1250)">۱٬۲۵۰</span>
 *  - انتقال دایره‌ای هنگام تغییر حالت تیره/روشن
 * اگر کاربر «کاهش حرکت» را در سیستم‌عامل روشن کرده باشد، حرکت‌ها اجرا نمی‌شوند.
 */
const reduceMotion = () => matchMedia('(prefers-reduced-motion: reduce)').matches;

// یک شنونده برای کل صفحه؛ پس با wire:navigate و رندر دوباره Livewire هم کار می‌کند
let frame = null;
document.addEventListener('pointermove', (e) => {
    if (e.pointerType !== 'mouse' || frame) return;
    frame = requestAnimationFrame(() => {
        frame = null;
        const card = e.target.closest?.('.spotlight, .tilt');
        if (!card) return;
        const r = card.getBoundingClientRect();
        const x = e.clientX - r.left;
        const y = e.clientY - r.top;
        card.style.setProperty('--mx', `${x}px`);
        card.style.setProperty('--my', `${y}px`);
        if (card.classList.contains('tilt') && !reduceMotion()) {
            card.style.setProperty('--ry', `${((x / r.width) - 0.5) * 6}deg`);
            card.style.setProperty('--rx', `${(0.5 - (y / r.height)) * 6}deg`);
        }
    });
}, { passive: true });

document.addEventListener('pointerout', (e) => {
    const card = e.target.closest?.('.tilt');
    if (card && !card.contains(e.relatedTarget)) {
        card.style.removeProperty('--rx');
        card.style.removeProperty('--ry');
    }
});

// تغییر حالت تیره با دایره‌ای که از محل دکمه باز می‌شود
export const withThemeTransition = (toggle) => (event) => {
    if (!document.startViewTransition || reduceMotion()) return toggle();

    const x = event?.clientX ?? innerWidth / 2;
    const y = event?.clientY ?? 0;
    const radius = Math.hypot(Math.max(x, innerWidth - x), Math.max(y, innerHeight - y));

    document.startViewTransition(toggle).ready.then(() => {
        document.documentElement.animate(
            { clipPath: [`circle(0 at ${x}px ${y}px)`, `circle(${radius}px at ${x}px ${y}px)`] },
            { duration: 550, easing: 'ease-in-out', pseudoElement: '::view-transition-new(root)' },
        );
    });
};

document.addEventListener('alpine:init', () => {
    const format = new Intl.NumberFormat('fa-IR');

    // عدد از صفر تا مقدار نهایی می‌شمارد، وقتی به صفحه دیده شود
    window.Alpine.data('countUp', (target, duration = 1400) => ({
        init() {
            if (reduceMotion() || !('IntersectionObserver' in window)) return;
            const io = new IntersectionObserver(([entry]) => {
                if (!entry.isIntersecting) return;
                io.disconnect();
                const start = performance.now();
                const step = (now) => {
                    const t = Math.min((now - start) / duration, 1);
                    const eased = 1 - Math.pow(1 - t, 3);
                    this.$el.textContent = format.format(Math.round(target * eased));
                    if (t < 1) requestAnimationFrame(step);
                };
                requestAnimationFrame(step);
            });
            io.observe(this.$el);
        },
    }));
});
