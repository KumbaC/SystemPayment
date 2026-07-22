<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRate;
use App\Models\Setting;
use App\Services\ExchangeRateService;
use Illuminate\Http\Request;

class ConfigurationController extends Controller
{
    public function __construct(protected ExchangeRateService $exchangeRate) {}

    public function index()
    {
        return view('pages.configuration.index', [
            'title' => 'Configuración',
            'settings' => [
                'company_name' => Setting::get('company_name', ''),
                'company_rif' => Setting::get('company_rif', ''),
                'company_address' => Setting::get('company_address', ''),
                'company_phone' => Setting::get('company_phone', ''),
                'tax_rate' => Setting::get('tax_rate', '16'),
                'invoice_prefix' => Setting::get('invoice_prefix', 'F'),
                'exchange_rate_usd_ves' => $this->exchangeRate->currentRate(),
                'exchange_rate_eur_usd' => Setting::get('exchange_rate_eur_usd', '1.08'),
                'support_whatsapp' => Setting::get('support_whatsapp', ''),
                'support_email' => Setting::get('support_email', ''),
                'scanner_enabled' => Setting::get('scanner_enabled', '1'),
                'scanner_scope' => Setting::get('scanner_scope', 'both'),
                'scanner_min_length' => Setting::get('scanner_min_length', '4'),
                'credit_system_enabled' => Setting::get('credit_system_enabled', '1'),
                'credit_late_fee_usd' => Setting::get('credit_late_fee_usd', '1'),
                'credit_initial_by_percentage' => Setting::get('credit_initial_by_percentage', '0'),
                'credit_initial_percentage' => Setting::get('credit_initial_percentage', '10'),
            ],
            'rateHistory' => ExchangeRate::query()->with('user')->latest()->limit(20)->get(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_rif' => ['nullable', 'string', 'max:20'],
            'company_address' => ['nullable', 'string'],
            'company_phone' => ['nullable', 'string', 'max:30'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'invoice_prefix' => ['required', 'string', 'max:10'],
            'exchange_rate_usd_ves' => ['required', 'numeric', 'min:0.0001'],
            'exchange_rate_eur_usd' => ['nullable', 'numeric', 'min:0.0001'],
            'support_whatsapp' => ['nullable', 'string', 'max:30'],
            'support_email' => ['nullable', 'email'],
            'scanner_enabled' => ['nullable', 'boolean'],
            'scanner_scope' => ['required', 'in:sales,purchases,both'],
            'scanner_min_length' => ['required', 'integer', 'min:3', 'max:30'],
            'credit_system_enabled' => ['nullable', 'boolean'],
            'credit_late_fee_usd' => ['required', 'numeric', 'min:0', 'max:100'],
            'credit_initial_by_percentage' => ['nullable', 'boolean'],
            'credit_initial_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'rate_notes' => ['nullable', 'string'],
        ]);

        Setting::set('company_name', $data['company_name']);
        Setting::set('company_rif', $data['company_rif'] ?? '');
        Setting::set('company_address', $data['company_address'] ?? '');
        Setting::set('company_phone', $data['company_phone'] ?? '');
        Setting::set('tax_rate', $data['tax_rate']);
        Setting::set('invoice_prefix', $data['invoice_prefix']);
        Setting::set('exchange_rate_eur_usd', $data['exchange_rate_eur_usd'] ?? '1.08');
        Setting::set('support_whatsapp', $data['support_whatsapp'] ?? '');
        Setting::set('support_email', $data['support_email'] ?? '');
        Setting::set('scanner_enabled', $request->boolean('scanner_enabled') ? '1' : '0');
        Setting::set('scanner_scope', $data['scanner_scope']);
        Setting::set('scanner_min_length', (string) $data['scanner_min_length']);
        Setting::set('credit_system_enabled', $request->boolean('credit_system_enabled') ? '1' : '0');
        Setting::set('credit_late_fee_usd', (string) $data['credit_late_fee_usd']);
        Setting::set('credit_initial_by_percentage', $request->boolean('credit_initial_by_percentage') ? '1' : '0');
        Setting::set('credit_initial_percentage', (string) ($data['credit_initial_percentage'] ?? '10'));

        $currentRate = $this->exchangeRate->currentRate();
        if ((float) $data['exchange_rate_usd_ves'] !== (float) $currentRate) {
            $this->exchangeRate->updateRate(
                (float) $data['exchange_rate_usd_ves'],
                $request->user()?->id,
                $data['rate_notes'] ?? 'Actualización manual desde configuración'
            );
        }

        return back()->with('success', 'Configuración actualizada correctamente.');
    }
}
