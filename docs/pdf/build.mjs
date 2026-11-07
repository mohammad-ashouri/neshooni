// ساخت PDF راهنما: فونت‌ها داخل HTML جاسازی و با Chrome بدون رابط چاپ می‌شود.
// اجرا از ریشه پروژه:  node docs/pdf/build.mjs
import { readFileSync, writeFileSync, existsSync } from 'node:fs';
import { execFileSync } from 'node:child_process';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';

const dir = dirname(fileURLToPath(import.meta.url));
const html = readFileSync(join(dir, 'guide.html'), 'utf8').replace(/url\("([^"]+\.woff2)"\)/g, (_, p) =>
    `url("data:font/woff2;base64,${readFileSync(resolve(dir, p)).toString('base64')}")`);

const built = join(dir, 'guide.built.html');
writeFileSync(built, html);

const chrome = [
    process.env.CHROME,
    'C:/Program Files/Google/Chrome/Application/chrome.exe',
    'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',
    '/usr/bin/google-chrome',
    '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
].find((p) => p && existsSync(p));

if (!chrome) throw new Error('Chrome/Edge پیدا نشد؛ مسیرش را در متغیر CHROME بگذارید.');

execFileSync(chrome, ['--headless=new', '--disable-gpu', '--no-pdf-header-footer', '--virtual-time-budget=5000',
    `--print-to-pdf=${join(dir, 'neshooni-guide.pdf')}`, pathToFileURL(built).href], { stdio: 'inherit' });
