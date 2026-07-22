@php
	$whatsapp = env('LICENSE_WHATSAPP', '+584242768464');
	$amount = env('LICENSE_AMOUNT', '10.00');
	$isUnlocked = session('license_valid_until') && \Carbon\Carbon::parse(session('license_valid_until'))->isFuture();
@endphp

<div x-data="{ open: {{ $isUnlocked ? 'false' : 'true' }} }"
	 x-show="open"
	 style="display:none"
	 class="fixed inset-0 z-50 flex items-center justify-center bg-black/60">
	<div class="w-full max-w-lg p-6 bg-white rounded shadow-lg dark:bg-gray-900 text-gray-900 dark:text-white">
		<div class="flex items-center justify-between mb-4">
			<h3 class="text-lg font-semibold">Acceso restringido</h3>
			<span class="text-sm text-gray-500">Pago mensual: ${{ $amount }}</span>
		</div>

		<p class="mb-4">Para continuar usando el sistema necesita introducir la clave mensual que el administrador le facilitará. Usted recibirá la clave encriptada; pegue la clave encriptada aquí.  Solicite la clave al administrador vía WhatsApp: <a href="https://wa.me/{{ ltrim($whatsapp, '+') }}" target="_blank" class="text-brand-500">{{ $whatsapp }}</a>.</p>

		@if($errors->has('code'))
			<div class="mb-3 text-sm text-red-600">{{ $errors->first('code') }}</div>
		@endif

		@if(session('license_success'))
			<div class="mb-3 text-sm text-green-600">{{ session('license_success') }}</div>
		@endif

		<form method="POST" action="{{ url('/license/activate') }}">
			@csrf
			<label class="block mb-2 text-sm">Clave encriptada</label>
			<input name="code" type="text" required class="w-full p-2 mb-4 border rounded" />
			<div class="flex items-center justify-end gap-2">
				<button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded">Activar 30 días</button>
			</div>
		</form>
		<p class="mt-4 text-xs text-gray-500">Si ya pagó y la clave no funciona, contacte soporte por WhatsApp.</p>
	</div>
</div>

<script>
	// Block interactions while modal is visible
	document.addEventListener('alpine:init', () => {
		Alpine.data('licenseLock', () => ({ }));
	});
</script>

