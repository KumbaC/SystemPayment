<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SupportController extends Controller
{
    public function __construct(protected WhatsAppService $whatsapp) {}

    public function index()
    {
        $number = Setting::get('support_whatsapp', Setting::get('company_phone', ''));
        $whatsappLink = $this->whatsapp->buildLink(
            $number,
            'Buenos dias, me podria ayudar con el aplicativo Krea Sistema Administrativo'
        );

        return view('pages.support.index', [
            'title' => 'Soporte',
            'supportEmail' => Setting::get('support_email', config('mail.from.address')),
            'whatsappLink' => $whatsappLink,
            'whatsappNumber' => $number,
        ]);
    }

    public function sendEmail(Request $request)
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $user = $request->user();
        $userName = $user?->name ?? 'Usuario';
        $userEmail = $user?->email ?? 'sin-correo';

        $to = Setting::get('support_email', config('mail.from.address'));

        Mail::raw(
            'Usuario: '.$userName.' ('.$userEmail.")\n\n".$data['message'],
            fn ($mail) => $mail
                ->to($to)
                ->from($userEmail, $userName)
                ->replyTo($userEmail, $userName)
                ->subject('[Soporte] '.$data['subject'])
        );

        return back()->with('success', 'Correo de soporte enviado correctamente.');
    }
}
