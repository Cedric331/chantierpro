import { spawnSync } from 'node:child_process';

const args = process.argv.slice(2);
const cwd = process.cwd();

const runPhp = () =>
    spawnSync('php', ['artisan', 'wayfinder:generate', ...args], {
        stdio: 'inherit',
        cwd,
    });

const toWslPath = (path) => {
    const distro = process.env.WSL_DISTRO_NAME || 'Ubuntu';
    const prefix = `\\\\wsl.localhost\\${distro}\\`;
    if (path.startsWith(prefix)) {
        return `/${path.slice(prefix.length).replace(/\\\\/g, '/')}`;
    }
    return '/var/www/chantierpro';
};

if (process.platform === 'win32') {
    const wslProjectPath = toWslPath(cwd);
    const cmd = [
        '-d',
        'Ubuntu',
        '-e',
        'bash',
        '-lc',
        `cd ${wslProjectPath} && php artisan wayfinder:generate ${args.join(' ')}`,
    ];
    const result = spawnSync('wsl', cmd, { stdio: 'inherit' });
    process.exit(result.status ?? 1);
}

const result = runPhp();
process.exit(result.status ?? 1);

