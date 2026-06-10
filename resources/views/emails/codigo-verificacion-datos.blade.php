<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin: 0; padding: 0; background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f4f6f9; padding: 2rem 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #1B3A5C 0%, #2D4E75 100%); padding: 2rem; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 1.5rem; font-weight: 700;">Universidad Nacional del Altiplano</h1>
                            <p style="margin: .5rem 0 0; color: rgba(255,255,255,.7); font-size: .875rem;">Sistema de Admisión 2026</p>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td style="padding: 2rem;">
                            <h2 style="margin: 0 0 1rem; color: #1A202C; font-size: 1.25rem;">
                                🔐 Código de Verificación
                            </h2>
                            @if($nombre)
                            <p style="margin: 0 0 1rem; color: #4A5568; font-size: .9375rem; line-height: 1.6;">
                                Hola, <strong>{{ $nombre }}</strong>:
                            </p>
                            @endif
                            <p style="margin: 0 0 1.5rem; color: #4A5568; font-size: .9375rem; line-height: 1.6;">
                                Has solicitado cargar tus datos registrados. Ingresa el siguiente código para confirmar tu identidad:
                            </p>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td align="center" style="padding: 1rem 0;">
                                        <div style="display: inline-block; background: linear-gradient(135deg, #1B3A5C, #2D4E75); color: #ffffff; font-size: 2.5rem; font-weight: 800; letter-spacing: .75rem; padding: 1.25rem 2.5rem; border-radius: 12px; font-family: 'Courier New', Courier, monospace;">
                                            {{ $codigo }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin: 1rem 0 0;">
                                <tr>
                                    <td style="background-color: #FEF3C7; border-radius: 8px; padding: .875rem 1rem; border: 1px solid #FDE68A; text-align: center;">
                                        <p style="margin: 0; color: #92400E; font-size: .8125rem; font-weight: 600;">
                                            ⏱ Este código expira en 3 minutos
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin: 1.5rem 0 0; color: #718096; font-size: .8125rem; line-height: 1.6;">
                                Si no solicitaste cargar tus datos, puedes ignorar este correo.
                            </p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #F7FAFC; padding: 1.25rem 2rem; border-top: 1px solid #E2E8F0; text-align: center;">
                            <p style="margin: 0; color: #A0AEC0; font-size: .75rem;">Oficina de Admisión — Universidad Nacional del Altiplano</p>
                            <p style="margin: .25rem 0 0; color: #A0AEC0; font-size: .6875rem;">Este es un correo automático, no responda a este mensaje.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
