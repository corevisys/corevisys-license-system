// Capture the public product page (/pricing/{slug}) across the 5 themes at
// 1280 and 360 wide, writing PNGs into docs/theme-screenshots/.
//
// Uses only Node built-ins (fetch + global WebSocket, Node 22+) plus a
// headless Chrome started over the DevTools Protocol. No new dependencies.
//
// Usage: node scripts/capture-product-screenshots.mjs [slug] [baseUrl]

import { spawn } from 'node:child_process';
import { mkdir, writeFile, rm } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';

const SLUG = process.argv[2] || 'hello';
const BASE = process.argv[3] || 'http://127.0.0.1:8002';
const PORT = 9222;
const OUT_DIR = 'docs/theme-screenshots';

const THEMES = ['terminal', 'dark-modern', 'light-modern', 'solarized-dark', 'tokyo-night'];
const WIDTHS = [1280, 360];

const CHROME_CANDIDATES = [
    'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
    join(process.env.LOCALAPPDATA || '', 'Google', 'Chrome', 'Application', 'chrome.exe'),
];

const chromePath = CHROME_CANDIDATES.find((p) => p && existsSync(p));
if (!chromePath) {
    console.error('Chrome not found in the usual locations.');
    process.exit(1);
}

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

async function waitForDevtools() {
    for (let attempt = 0; attempt < 60; attempt += 1) {
        try {
            const res = await fetch(`http://127.0.0.1:${PORT}/json/list`);
            const targets = await res.json();
            const page = targets.find((t) => t.type === 'page' && t.webSocketDebuggerUrl);
            if (page) return page;
        } catch {
            // devtools not up yet
        }
        await sleep(250);
    }
    throw new Error('Chrome DevTools endpoint did not become ready.');
}

function createClient(wsUrl) {
    const ws = new WebSocket(wsUrl);
    const pending = new Map();
    let nextId = 1;
    const events = [];

    const ready = new Promise((resolve, reject) => {
        ws.addEventListener('open', () => resolve());
        ws.addEventListener('error', (err) => reject(err));
    });

    ws.addEventListener('message', (event) => {
        const msg = JSON.parse(event.data);
        if (msg.id && pending.has(msg.id)) {
            const { resolve, reject } = pending.get(msg.id);
            pending.delete(msg.id);
            if (msg.error) reject(new Error(JSON.stringify(msg.error)));
            else resolve(msg.result);
        } else if (msg.method) {
            events.push(msg.method);
        }
    });

    const send = (method, params = {}) =>
        new Promise((resolve, reject) => {
            const id = nextId++;
            pending.set(id, { resolve, reject });
            ws.send(JSON.stringify({ id, method, params }));
        });

    return { ready, send, events, close: () => ws.close() };
}

async function waitForLoad(client) {
    // Wait until the document has finished loading, then give Inertia a beat.
    for (let i = 0; i < 80; i += 1) {
        const { result } = await client.send('Runtime.evaluate', {
            expression: 'document.readyState',
            returnByValue: true,
        });
        if (result.value === 'complete') break;
        await sleep(100);
    }
    await sleep(700);
}

async function main() {
    await mkdir(OUT_DIR, { recursive: true });

    const userDataDir = join(tmpdir(), `corevisys-shots-${Date.now()}`);
    const chrome = spawn(
        chromePath,
        [
            '--headless=new',
            '--disable-gpu',
            '--hide-scrollbars',
            '--no-first-run',
            '--no-default-browser-check',
            `--remote-debugging-port=${PORT}`,
            `--user-data-dir=${userDataDir}`,
            'about:blank',
        ],
        { stdio: 'ignore' },
    );

    const written = [];

    try {
        const target = await waitForDevtools();
        const client = createClient(target.webSocketDebuggerUrl);
        await client.ready;
        await client.send('Page.enable');
        await client.send('Runtime.enable');

        for (const width of WIDTHS) {
            for (const theme of THEMES) {
                await client.send('Emulation.setDeviceMetricsOverride', {
                    width,
                    height: 900,
                    deviceScaleFactor: 1,
                    mobile: width < 600,
                });

                const url = `${BASE}/pricing/${SLUG}`;
                await client.send('Page.navigate', { url });
                await waitForLoad(client);

                // Force the theme after mount (the app would normally read it
                // from settings.default_theme).
                await client.send('Runtime.evaluate', {
                    expression: `document.documentElement.setAttribute('data-theme', '${theme}')`,
                });
                await sleep(250);

                const { result: size } = await client.send('Runtime.evaluate', {
                    expression: 'Math.max(document.body.scrollHeight, document.documentElement.scrollHeight)',
                    returnByValue: true,
                });

                await client.send('Emulation.setDeviceMetricsOverride', {
                    width,
                    height: Math.max(900, Math.ceil(size.value)),
                    deviceScaleFactor: 1,
                    mobile: width < 600,
                });
                await sleep(250);

                const shot = await client.send('Page.captureScreenshot', {
                    format: 'png',
                    captureBeyondViewport: true,
                });

                const file = `product-${width}-${theme}.png`;
                await writeFile(join(OUT_DIR, file), Buffer.from(shot.data, 'base64'));
                written.push({ file, width, theme, fullHeight: Math.ceil(size.value) });
                console.log(`captured ${file} (${width}px, ${theme})`);
            }
        }

        client.close();
    } finally {
        chrome.kill();
        await sleep(300);
        await rm(userDataDir, { recursive: true, force: true }).catch(() => { });
    }

    console.log('\n' + JSON.stringify(written, null, 2));
}

main().catch((err) => {
    console.error(err);
    process.exit(1);
});