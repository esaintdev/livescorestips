// Renders promo.html to PNG/JPEG frames with headless Chrome, one frame per seek.
// Usage: node render.cjs <framesDir> [fps] [onlyTimesCsvSeconds]
// Needs puppeteer-core resolvable (NODE_PATH) and Google Chrome installed.
const puppeteer = require('puppeteer-core');
const path = require('path');
const fs = require('fs');

(async () => {
    const out = process.argv[2];
    const fps = Number(process.argv[3] || 30);
    const only = process.argv[4] ? process.argv[4].split(',').map(Number) : null;
    fs.mkdirSync(out, { recursive: true });

    const browser = await puppeteer.launch({
        executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
        headless: 'new',
        args: ['--allow-file-access-from-files', '--hide-scrollbars', '--force-color-profile=srgb'],
    });
    const page = await browser.newPage();
    await page.setViewport({ width: 1080, height: 1920, deviceScaleFactor: 1 });
    await page.goto('file://' + path.join(__dirname, 'promo.html'));
    await page.evaluate(() => document.fonts.ready);

    const duration = await page.evaluate(() => window.DURATION);
    const times = only ? only.map(s => s * 1000) : Array.from({ length: Math.round(duration / 1000 * fps) }, (_, i) => i * 1000 / fps);

    for (let i = 0; i < times.length; i++) {
        await page.evaluate(ms => window.seek(ms), times[i]);
        const name = only ? `t${String(only[i]).replace('.', '_')}.jpg` : `f${String(i).padStart(5, '0')}.jpg`;
        await page.screenshot({ path: path.join(out, name), type: 'jpeg', quality: only ? 80 : 96 });
    }
    await browser.close();
    console.log(`rendered ${times.length} frames`);
})();
