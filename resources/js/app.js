/*
 * جاوااسکریپت سایت عمدتاً در خود Livewire است. اینجا فقط چند کار سمت مرورگر:
 *  - تغییر حالت روشن/تیره
 *  - نمایش پیام‌های کوتاه (toast) که از کامپوننت‌ها با dispatch('toast') فرستاده می‌شوند
 *  - سازنده بنر «ما در نشونی هستیم» (فایل banner.js)
 */
import './banner';

// حالت تیره: انتخاب کاربر در localStorage ذخیره می‌شود
window.toggleTheme = () => {
    const dark = document.documentElement.classList.toggle('dark');
    try {
        localStorage.setItem('neshooni-theme', dark ? 'dark' : 'light');
    } catch (e) {}
};

// با wire:navigate کلاس‌های <html> از صفحه جدید می‌آید؛ حالت تیره دوباره اعمال می‌شود
document.addEventListener('livewire:navigated', () => {
    let theme = null;
    try {
        theme = localStorage.getItem('neshooni-theme');
    } catch (e) {}
    const dark = theme === 'dark' || (!theme && matchMedia('(prefers-color-scheme: dark)').matches);
    document.documentElement.classList.toggle('dark', dark);
});

document.addEventListener('alpine:init', () => {
    // نمونه استفاده در PHP:  $this->dispatch('toast', message: 'ذخیره شد', type: 'success');
    window.Alpine.data('toasts', () => ({
        items: [],
        add({ message, type = 'success' }) {
            const id = Date.now() + Math.random();
            this.items.push({ id, message, type });
            setTimeout(() => (this.items = this.items.filter((t) => t.id !== id)), 3500);
        },
    }));
});
