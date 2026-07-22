<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Efectivo = 'efectivo';
    case Transferencia = 'transferencia';
    case PagoMovil = 'pago_movil';
    case Zelle = 'zelle';
    case PuntoVenta = 'punto_venta';
    case Usdt = 'usdt';

    public function label(): string
    {
        return match ($this) {
            self::Efectivo => 'Efectivo',
            self::Transferencia => 'Transferencia',
            self::PagoMovil => 'Pago Móvil',
            self::Zelle => 'Zelle',
            self::PuntoVenta => 'Punto de Venta',
            self::Usdt => 'USDT',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(
            fn (self $method) => [$method->value => $method->label()]
        )->all();
    }
}
