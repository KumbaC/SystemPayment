<?php

namespace App\Http\Controllers;

use App\Models\License;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class LicenseController extends Controller
{
	/**
	 * Activate license using a monthly rotating key.
	 * The expected plain key format is: MI-CLAVE-MM-YY (e.g. MI-CLAVE-06-26)
	 * The modal can receive either the plain key (for admin use) or an encrypted string
	 * produced by `php artisan license:monthly`/`license:encrypt`.
	 */
	public function activate(Request $request)
	{
		$request->validate([
			'code' => 'required|string',
		]);

		$input = trim($request->input('code'));

		// Try decrypting - if decryptable, use decrypted value; otherwise use raw input
		$candidate = $input;
		try {
			$candidate = Crypt::decryptString($input);
		} catch (\Throwable $e) {
			// not encrypted, use as-is
		}

		// Expected monthly key
		$expected = sprintf('MI-CLAVE-%02d-%02s', intval(date('m')), date('y'));

		if (! hash_equals($expected, $candidate)) {
			return redirect()->back()->withErrors(['code' => 'Clave inválida o incorrecta para este mes.']);
		}

		// Create or update license record to grant 30 days access
		$license = License::first();
		if (! $license) {
			$license = new License();
		}
		$license->code_encrypted = Crypt::encryptString($candidate);
		$license->expires_at = now()->addDays(30);
		$license->active = true;
		$license->save();

		session(['license_valid_until' => $license->expires_at->toDateTimeString()]);

		return redirect()->back()->with('license_success', 'Acceso concedido por 30 días. Gracias.');
	}
}
