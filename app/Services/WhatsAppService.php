<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Setting;
use App\Models\License;

class WhatsAppService
{
    public function saleThankYouMessage(Sale $sale): string
    {
        $sale->loadMissing(['customer', 'items.product']);

        $company = Setting::get('company_name', 'nuestra tienda');
        $lines = ["¡Hola".($sale->customer ? " {$sale->customer->name}" : '')."!"];
        $lines[] = "Gracias por comprar en {$company}. 🙏";
        $lines[] = '';
        $lines[] = "Factura: {$sale->invoice_number}";
        $lines[] = "Fecha: {$sale->sale_date->format('d/m/Y')}";
        $lines[] = '';
        $lines[] = 'Detalle de tu compra:';

        foreach ($sale->items as $item) {
            $lines[] = "• {$item->product->name} x{$item->quantity} — Bs. ".number_format($item->subtotal_ves, 2, ',', '.');
        }

        $lines[] = '';
        $lines[] = 'Total: Bs. '.number_format($sale->total_ves, 2, ',', '.');
        $lines[] = '';
        $lines[] = '¡Esperamos verte pronto!';

        return implode("\n", $lines);
    }

    public function buildLink(?string $phone, string $message): ?string
    {
        if (! $phone) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone);

        if (str_starts_with($digits, '0')) {
            $digits = '58'.substr($digits, 1);
        } elseif (! str_starts_with($digits, '58')) {
            $digits = '58'.$digits;
        }

        return 'https://wa.me/'.$digits.'?text='.urlencode($message);
    }

    /**
     * Send a message using CallMeBot WhatsApp API
     * Returns HTTP status code on success, or false on failure.
     */
    public function sendViaCallMeBot(?string $phone, string $message, $apiKey = 8234723)
    {
        if (! $phone) return false;

        $digits = preg_replace('/\D/', '', $phone);
        // ensure country code (default 58 if not present)
        if (str_starts_with($digits, '0')) {
            $digits = '58'.substr($digits, 1);
        } elseif (! str_starts_with($digits, '58')) {
            $digits = '58'.$digits;
        }

        $apiKey = $apiKey ?? env('CALLMEBOT_APIKEY');
        $phoneParam = $digits;

        $url = 'https://api.callmebot.com/whatsapp.php?source=php&phone='.$phoneParam.'&text='.urlencode($message).'&apikey='.urlencode($apiKey);

        if (! function_exists('curl_init')) {
            return false;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $html = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $status ?: false;
    }

    /**
     * Notify admin if license is expiring soon or expired.
     * Sends message to admin phone configured in env `ADMIN_WHATSAPP` or Setting `admin_whatsapp`.
     */
    public function notifyAdminLicenseExpiryIfNeeded()
    {
        $license = License::first();
        if (! $license) return false;

        $adminPhone = env('ADMIN_WHATSAPP', Setting::get('admin_whatsapp', null) ?? env('LICENSE_WHATSAPP', '+584242768464'));

        $expires = $license->expires_at;
        if (! $expires) {
            $message = "La licencia del aplicativo no tiene fecha de vencimiento configurada. Para renovar debe pagar $10 y solicitar la nueva clave por WhatsApp.";
            return $this->sendViaCallMeBot($adminPhone, $message);
        }

        $now = now();
        $diffDays = $now->diffInDays($expires, false);

        // Notify if expires in 7 days or less, or already expired
        if ($diffDays <= 7) {
            $date = \Carbon\Carbon::parse($expires)->format('d/m/Y');
            $message = "Atención: la licencia del aplicativo se vence el {$date}. Para renovar debe pagar $10 y solicitar la nueva clave por WhatsApp al número +584242768464  +584241971693";
            return $this->sendViaCallMeBot($adminPhone, $message);
        }

        return false;
    }
}
