/*
 * سازنده بنر «ما در نشونی هستیم» (استوری، پست، نشان سایت).
 * اطلاعات پیج از کامپوننت Livewire (Dashboard\Banner) به شکل data-banner روی <canvas> می‌آید
 * و اینجا فقط روی canvas کشیده می‌شود تا کاربر PNG دانلود کند.
 */
import qrcode from 'qrcode-generator';

const SIZES = { story: [1080, 1920], post: [1080, 1080], badge: [1200, 400] };
const THEMES = {
    violet: { bg: ['#6d28d9', '#c026d3'], ink: '#ffffff', card: '#ffffff', cardInk: '#0f172a', sub: '#64748b', accent: '#7c3aed' },
    dark: { bg: ['#020617', '#312e81'], ink: '#ffffff', card: '#1e293b', cardInk: '#f8fafc', sub: '#94a3b8', accent: '#a78bfa' },
    light: { bg: ['#ffffff', '#ede9fe'], ink: '#1e1b4b', card: '#ffffff', cardInk: '#0f172a', sub: '#64748b', accent: '#7c3aed' },
};

// ---------- ابزارهای کشیدن ----------

function rect(ctx, x, y, w, h, r, fill) {
    ctx.beginPath();
    ctx.roundRect(x, y, w, h, r);
    ctx.fillStyle = fill;
    ctx.fill();
}

// متن را آن‌قدر کوچک می‌کند که در عرض maxW جا شود
function text(ctx, str, x, y, { size, weight = 400, color, align = 'center', maxW = 2000, dir = 'rtl' }) {
    let s = size;
    do {
        ctx.font = `${weight} ${s}px Vazirmatn, sans-serif`;
        s -= 2;
    } while (ctx.measureText(str).width > maxW && s > 12);
    ctx.direction = dir;
    ctx.textAlign = align;
    ctx.textBaseline = 'middle';
    ctx.fillStyle = color;
    ctx.fillText(str, x, y);
}

function star(ctx, cx, cy, r, fill) {
    ctx.beginPath();
    for (let i = 0; i < 10; i++) {
        const a = -Math.PI / 2 + (i * Math.PI) / 5;
        const rr = i % 2 ? r * 0.45 : r;
        ctx.lineTo(cx + rr * Math.cos(a), cy + rr * Math.sin(a));
    }
    ctx.closePath();
    ctx.fillStyle = fill;
    ctx.fill();
}

// پنج ستاره که از راست پر می‌شوند (مثل نمایش سایت)
function stars(ctx, right, cy, size, rating, on, off) {
    const gap = size * 1.15;
    for (let i = 0; i < 5; i++) star(ctx, right - i * gap, cy, size / 2, rating >= i + 0.75 ? on : off);
}

function logo(ctx, x, y, s) {
    const g = ctx.createLinearGradient(x, y, x + s, y + s);
    g.addColorStop(0, '#7c3aed');
    g.addColorStop(1, '#d946ef');
    rect(ctx, x, y, s, s, s * 0.28, g);
    text(ctx, 'ن', x + s / 2, y + s * 0.52, { size: s * 0.62, weight: 900, color: '#fff' });
}

function qr(ctx, data, x, y, s) {
    const q = qrcode(0, 'M');
    q.addData(data);
    q.make();
    const n = q.getModuleCount();
    const pad = s * 0.07;
    const cell = (s - pad * 2) / n;
    rect(ctx, x, y, s, s, s * 0.08, '#ffffff');
    ctx.fillStyle = '#0f172a';
    for (let r = 0; r < n; r++) {
        for (let c = 0; c < n; c++) {
            if (q.isDark(r, c)) ctx.fillRect(x + pad + c * cell, y + pad + r * cell, Math.ceil(cell), Math.ceil(cell));
        }
    }
}

function loadImage(src) {
    return new Promise((resolve) => {
        const img = new Image();
        img.onload = () => resolve(img);
        img.onerror = () => resolve(null);
        img.src = src;
    });
}

// ---------- کشیدن بنر ----------

async function draw(canvas) {
    const d = JSON.parse(canvas.dataset.banner);
    await Promise.all([400, 700, 900].map((w) => document.fonts.load(`${w} 40px Vazirmatn`))).catch(() => {});
    const img = d.image ? await loadImage(d.image) : null;

    const [W, H] = SIZES[d.format];
    const T = THEMES[d.theme];
    const ctx = canvas.getContext('2d');
    canvas.width = W;
    canvas.height = H;

    const bg = ctx.createLinearGradient(0, 0, W, H);
    bg.addColorStop(0, T.bg[0]);
    bg.addColorStop(1, T.bg[1]);
    rect(ctx, 0, 0, W, H, d.format === 'badge' ? 48 : 0, bg);

    const avatar = (cx, cy, r) => {
        ctx.save();
        ctx.beginPath();
        ctx.arc(cx, cy, r, 0, Math.PI * 2);
        ctx.clip();
        if (img) {
            ctx.drawImage(img, cx - r, cy - r, r * 2, r * 2);
        } else {
            const g = ctx.createLinearGradient(cx - r, cy - r, cx + r, cy + r);
            g.addColorStop(0, '#8b5cf6');
            g.addColorStop(1, '#d946ef');
            ctx.fillStyle = g;
            ctx.fillRect(cx - r, cy - r, r * 2, r * 2);
            text(ctx, d.title.trim()[0], cx, cy + r * 0.05, { size: r, weight: 900, color: '#fff' });
        }
        ctx.restore();
    };
    const offStar = d.theme === 'dark' ? '#334155' : '#e2e8f0';
    const shortUrl = d.url.replace('https://', '');

    if (d.format === 'story') {
        logo(ctx, W / 2 - 60, 110, 120);
        text(ctx, 'نشونی', W / 2, 290, { size: 52, weight: 900, color: T.ink });
        text(ctx, 'ما در نشونی هستیم!', W / 2, 430, { size: 100, weight: 900, color: T.ink, maxW: W - 120 });
        text(ctx, 'ما را در نشونی پیدا کنید، دنبال کنید و نظر بدهید', W / 2, 520, { size: 40, color: T.ink, maxW: W - 160 });
        rect(ctx, 90, 700, W - 180, 720, 56, T.card);
        avatar(W / 2, 700, 140);
        text(ctx, d.title, W / 2, 940, { size: 68, weight: 900, color: T.cardInk, maxW: W - 260 });
        text(ctx, d.handle, W / 2, 1020, { size: 44, color: T.sub, dir: 'ltr' });
        text(ctx, d.meta, W / 2, 1095, { size: 40, weight: 700, color: T.accent, maxW: W - 260 });
        stars(ctx, W / 2 + 147, 1185, 64, d.rating, '#fbbf24', offStar);
        text(ctx, d.ratingLine, W / 2, 1260, { size: 38, color: T.sub });
        if (d.verified) text(ctx, '✓ کسب‌وکار تأییدشده', W / 2, 1335, { size: 36, weight: 700, color: '#0ea5e9' });
        qr(ctx, d.url, W / 2 - 160, 1470, 320);
        text(ctx, shortUrl, W / 2, 1860, { size: 46, weight: 700, color: T.ink, dir: 'ltr' });
    } else if (d.format === 'post') {
        logo(ctx, W - 170, 70, 96);
        text(ctx, 'نشونی', W - 200, 120, { size: 46, weight: 900, color: T.ink, align: 'right' });
        text(ctx, 'ما در نشونی هستیم!', W / 2, 270, { size: 86, weight: 900, color: T.ink, maxW: W - 120 });
        rect(ctx, 80, 360, W - 160, 440, 48, T.card);
        avatar(W - 250, 580, 120);
        text(ctx, d.title, W - 410, 490, { size: 58, weight: 900, color: T.cardInk, align: 'right', maxW: W - 560 });
        text(ctx, d.handle, W - 410, 560, { size: 38, color: T.sub, align: 'right', dir: 'ltr', maxW: W - 560 });
        text(ctx, d.meta, W - 410, 625, { size: 34, weight: 700, color: T.accent, align: 'right', maxW: W - 560 });
        stars(ctx, W - 437, 700, 46, d.rating, '#fbbf24', offStar);
        text(ctx, d.ratingLine, W - 410, 760, { size: 30, color: T.sub, align: 'right' });
        qr(ctx, d.url, 80, 850, 170);
        text(ctx, 'اسکن کنید یا در نشونی جستجو کنید', W - 80, 905, { size: 36, color: T.ink, align: 'right', maxW: W - 320 });
        text(ctx, shortUrl, W - 80, 975, { size: 42, weight: 700, color: T.ink, align: 'right', dir: 'ltr' });
    } else {
        rect(ctx, W - 300, 80, 240, 240, 68, 'rgba(255,255,255,0.95)');
        logo(ctx, W - 290, 90, 220);
        text(ctx, 'ما در نشونی هستیم', W - 330, 150, { size: 78, weight: 900, color: T.ink, align: 'right', maxW: 520 });
        text(ctx, d.title, W - 330, 240, { size: 44, weight: 700, color: T.ink, align: 'right', maxW: 520 });
        stars(ctx, W - 352, 315, 44, d.rating, '#fbbf24', d.theme === 'light' ? '#ddd6fe' : 'rgba(255,255,255,.3)');
        qr(ctx, d.url, 60, 60, 280);
    }
}

// هر canvas با data-banner بعد از هر به‌روزرسانی Livewire دوباره کشیده می‌شود
function drawAll() {
    document.querySelectorAll('canvas[data-banner]').forEach((canvas) => {
        if (canvas.dataset.drawn !== canvas.dataset.banner) {
            canvas.dataset.drawn = canvas.dataset.banner;
            draw(canvas);
        }
    });
}

document.addEventListener('DOMContentLoaded', drawAll);
// جابه‌جایی بین صفحه‌ها با wire:navigate صفحه را دوباره بارگذاری نمی‌کند
document.addEventListener('livewire:navigated', drawAll);
document.addEventListener('livewire:init', () => {
    // بعد از هر درخواست موفق Livewire (مثلاً تغییر قالب یا رنگ)
    window.Livewire.hook('commit', ({ succeed }) => succeed(() => setTimeout(drawAll)));
});

window.downloadBanner = (canvas, filename) => {
    const link = document.createElement('a');
    link.download = filename;
    link.href = canvas.toDataURL('image/png');
    link.click();
};
