#!/usr/bin/env python3
"""
Minimal installer script for Windows.

This script checks for required tools (php, composer, node, pnpm, mysql/mariadb),
attempts to guide installation via winget when possible, and then runs the
project setup commands: composer install, php artisan migrate --seed, pnpm install, pnpm run build

Run as: python scripts/installer.py
"""
import os
import shutil
import subprocess
import sys

ROOT = os.path.dirname(os.path.dirname(__file__))

def which(cmd):
    return shutil.which(cmd) is not None

def run(cmd, cwd=ROOT):
    print('> ' + cmd)
    completed = subprocess.run(cmd, shell=True, cwd=cwd)
    if completed.returncode != 0:
        raise SystemExit(f'Command failed: {cmd}')

def main():
    print('Instalador: Comprobando herramientas necesarias...')

    checks = {
        'php': which('php'),
        'composer': which('composer'),
        'node': which('node'),
        'pnpm': which('pnpm'),
        'mysql': which('mysql'),
    }

    for name, ok in checks.items():
        print(f' - {name}: {'OK' if ok else 'MISSING'})

    if not checks['php']:
        print('\nPHP no encontrado. En Windows se puede instalar con:')
        print('  winget install PHP.PHP.8_3')

    if not checks['composer']:
        print('\nComposer no encontrado. Puede instalarlo desde https://getcomposer.org/')

    if not checks['node']:
        print('\nNode.js no encontrado. Instálelo desde https://nodejs.org/')

    if not checks['pnpm']:
        print('\npnpm no encontrado. Instale con: npm i -g pnpm')

    if not checks['mysql']:
        print('\nMySQL/MariaDB no encontrado. En Windows puede usar: winget install MariaDB')

    input('\nPresione Enter para continuar con la instalación (el script ejecutará los comandos de proyecto).')

    # Generate .env if missing
    env_path = os.path.join(ROOT, '.env')
    if not os.path.exists(env_path):
        print('Copiando .env.example a .env')
        example = os.path.join(ROOT, '.env.example')
        if os.path.exists(example):
            shutil.copyfile(example, env_path)
        else:
            print('No se encontró .env.example; por favor cree .env manualmente.')

    # Run composer install
    if which('composer'):
        run('composer install --no-interaction')

    # Generate app key
    if which('php'):
        run('php artisan key:generate')

    # Run migrations and seed
    if which('php'):
        run('php artisan migrate --seed --force')

    # Install pnpm packages and build
    if which('pnpm'):
        run('pnpm install')
        # Prefer production build
        run('pnpm run build')

    print('\nInstalación completada. Puede abrir la aplicación en: http://localhost')

if __name__ == '__main__':
    main()
