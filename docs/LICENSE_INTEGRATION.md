Instrucciones para bloqueo/licencias y el instalador

1) Flujo de bloqueo mensual
- La clave del mes se genera con el formato `MI-CLAVE-MM-YY` (por ejemplo, `MI-CLAVE-06-26`).
- La clave cambia automáticamente cada mes según la fecha del servidor.
- Usted (administrador) generará y compartirá con los usuarios la clave encriptada; los usuarios pegarán la clave encriptada en el modal para obtener acceso por 30 días.

2) Validación en el backend
- El modal acepta tanto la clave encriptada (recomendada para usuarios) como la clave en texto plano (sólo para administradores si lo desea). El servidor intentará desencriptar primero; si falla, comparará con la clave esperada para el mes.
- Si la clave es correcta, el sistema crea/actualiza el registro en `licenses`, establece `expires_at` a +30 días y `active = true`.

3) Comandos Artisan
- Generar la clave mensual (muestra plain y encriptado):
	php artisan license:monthly
- Opciones:
	- `--month=MM --year=YY` para generar una clave de un mes específico
	- `--encrypt-only` para imprimir sólo la cadena encriptada

4) Uso manual
1. Ejecutar migraciones si no está hecho:
	 php artisan migrate
2. Generar la clave encriptada para el mes actual y copiarla para enviar al usuario:
	 php artisan license:monthly --encrypt-only
	Note: if `license:monthly` no aparece en su `php artisan` (dependiendo de su Kernel), puede generar la clave y cifrarla manualmente:

	1. Calcule la clave del mes manualmente: `MI-CLAVE-` seguido de `MM-YY` (por ejemplo `MI-CLAVE-06-26`).
	2. Cifre la clave con el comando existente:
		 php artisan license:encrypt "MI-CLAVE-06-26"
3. El usuario pega la cadena encriptada en el modal y pulsa "Activar 30 días".

5) Notas sobre seguridad
- La encriptación usa `APP_KEY` de Laravel. Mantenga `APP_KEY` seguro y consistente entre entornos si requiere interoperabilidad.
- El administrador puede usar la clave sin encriptar si lo necesita, pero se recomienda usar la versión encriptada para distribuirla a usuarios.
