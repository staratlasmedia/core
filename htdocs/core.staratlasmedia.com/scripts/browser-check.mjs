import { readFileSync } from 'node:fs';
import { chromium } from '@playwright/test';

function envValue(name, fallback = '') {
  try {
    const env = readFileSync('.env', 'utf8');
    const line = env.split('\n').find((entry) => entry.startsWith(`${name}=`));

    return line ? line.slice(name.length + 1).replace(/^"|"$/g, '') : fallback;
  } catch {
    return fallback;
  }
}

const target = process.argv[2] ?? envValue('APP_URL', 'https://core.staratlasmedia.com');

const browser = await chromium.launch({ headless: true });
const page = await browser.newPage({ viewport: { width: 1280, height: 720 } });
const response = await page.goto(target, { waitUntil: 'domcontentloaded', timeout: 15000 });

console.log(JSON.stringify({
  ok: response?.ok() ?? false,
  status: response?.status() ?? null,
  title: await page.title(),
  h1: await page.locator('h1').first().textContent().catch(() => null),
}));

await browser.close();
