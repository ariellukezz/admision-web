<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmtpAccount;
use App\Mail\TestSmtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class SmtpAccountController extends Controller
{
    public function lista()
    {
        $cuentas = SmtpAccount::orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();

        return response()->json([
            'estado' => true,
            'datos' => $cuentas,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'mailer'      => 'required|string|max:50',
            'host'        => 'required|string|max:255',
            'port'        => 'required|integer',
            'username'    => 'required|string|max:255',
            'password'    => 'required|string|max:255',
            'encryption'  => 'nullable|string|in:ssl,tls,null',
            'from_address'=> 'required|email|max:255',
            'from_name'   => 'required|string|max:255',
            'is_active'   => 'boolean',
            'is_default'  => 'boolean',
        ]);

        if ($request->is_default) {
            SmtpAccount::where('is_default', true)->update(['is_default' => false]);
        }

        $cuenta = SmtpAccount::create($request->all());

        return response()->json([
            'estado'  => true,
            'mensaje' => 'Cuenta SMTP creada correctamente',
            'datos'   => $cuenta,
        ]);
    }

    public function update(Request $request, $id)
    {
        $cuenta = SmtpAccount::findOrFail($id);

        $request->validate([
            'name'        => 'sometimes|string|max:255',
            'mailer'      => 'sometimes|string|max:50',
            'host'        => 'sometimes|string|max:255',
            'port'        => 'sometimes|integer',
            'username'    => 'sometimes|string|max:255',
            'password'    => 'sometimes|string|max:255',
            'encryption'  => 'nullable|string|in:ssl,tls,null',
            'from_address'=> 'sometimes|email|max:255',
            'from_name'   => 'sometimes|string|max:255',
            'is_active'   => 'boolean',
            'is_default'  => 'boolean',
        ]);

        if ($request->has('is_default') && $request->is_default) {
            SmtpAccount::where('is_default', true)->where('id', '!=', $id)->update(['is_default' => false]);
        }

        $data = $request->except(['password']);
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $cuenta->update($data);

        return response()->json([
            'estado'  => true,
            'mensaje' => 'Cuenta SMTP actualizada correctamente',
            'datos'   => $cuenta,
        ]);
    }

    public function destroy($id)
    {
        $cuenta = SmtpAccount::findOrFail($id);

        if ($cuenta->is_default) {
            return response()->json([
                'estado'  => false,
                'mensaje' => 'No se puede eliminar la cuenta predeterminada',
            ], 422);
        }

        $cuenta->delete();

        return response()->json([
            'estado'  => true,
            'mensaje' => 'Cuenta SMTP eliminada correctamente',
        ]);
    }

    public function toggle($id)
    {
        $cuenta = SmtpAccount::findOrFail($id);
        $cuenta->is_active = !$cuenta->is_active;
        $cuenta->save();

        return response()->json([
            'estado'  => true,
            'mensaje' => $cuenta->is_active ? 'Cuenta activada' : 'Cuenta desactivada',
            'datos'   => $cuenta,
        ]);
    }

    public function setDefault($id)
    {
        $cuenta = SmtpAccount::findOrFail($id);

        SmtpAccount::where('is_default', true)->update(['is_default' => false]);

        $cuenta->is_default = true;
        $cuenta->is_active = true;
        $cuenta->save();

        return response()->json([
            'estado'  => true,
            'mensaje' => 'Cuenta establecida como predeterminada',
            'datos'   => $cuenta,
        ]);
    }

    public function testEmail($id)
    {
        $cuenta = SmtpAccount::findOrFail($id);

        Config::set('mail.mailers.smtp_dynamic', [
            'transport'  => $cuenta->mailer,
            'host'       => $cuenta->host,
            'port'       => $cuenta->port,
            'encryption' => $cuenta->encryption,
            'username'   => $cuenta->username,
            'password'   => $cuenta->password,
        ]);
        Config::set('mail.from.address', $cuenta->from_address);
        Config::set('mail.from.name', $cuenta->from_name);

        try {
            Mail::mailer('smtp_dynamic')->to($cuenta->from_address)->send(new TestSmtp($cuenta->name));

            $cuenta->error_message = null;
            $cuenta->error_at = null;
            $cuenta->save();

            return response()->json([
                'estado'  => true,
                'mensaje' => 'Correo de prueba enviado correctamente a ' . $cuenta->from_address,
            ]);
        } catch (\Exception $e) {
            $cuenta->is_active = false;
            $cuenta->error_message = $e->getMessage();
            $cuenta->error_at = now();
            $cuenta->save();

            return response()->json([
                'estado'  => false,
                'mensaje' => 'Error al enviar: ' . $e->getMessage(),
            ], 422);
        }
    }
}
