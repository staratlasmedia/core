import { spawnSync } from 'node:child_process';
import { readFileSync } from 'node:fs';

function envValue(name, fallback = '') {
  try {
    const env = readFileSync('.env', 'utf8');
    const line = env.split('\n').find((entry) => entry.startsWith(`${name}=`));

    return line ? line.slice(name.length + 1).replace(/^"|"$/g, '') : fallback;
  } catch {
    return fallback;
  }
}

function agentBrowser(args) {
  const result = spawnSync('npx', ['agent-browser', ...args], {
    encoding: 'utf8',
    stdio: ['ignore', 'pipe', 'pipe'],
  });

  if (result.status !== 0) {
    throw new Error((result.stderr || result.stdout || 'agent-browser failed').trim());
  }

  return result.stdout.trim();
}

function parseValue(output) {
  try {
    return JSON.parse(output);
  } catch {
    return output;
  }
}

const target = process.argv[2] ?? envValue('APP_URL', 'https://core.staratlasmedia.com');

try {
  agentBrowser(['open', target]);

  const status = parseValue(agentBrowser(['eval', "fetch(location.href, { method: 'HEAD' }).then(r => r.status)"]));
  const title = parseValue(agentBrowser(['eval', 'document.title']));
  const h1 = parseValue(agentBrowser(['eval', "document.querySelector('h1')?.textContent ?? null"]));

  console.log(JSON.stringify({
    ok: Number(status) >= 200 && Number(status) < 400,
    status,
    title,
    h1,
  }));
} finally {
  spawnSync('npx', ['agent-browser', 'close'], {
    encoding: 'utf8',
    stdio: 'ignore',
  });
}
