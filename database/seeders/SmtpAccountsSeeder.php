<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SmtpAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $cuentas = [
            [
                'name' => 'DG Admisión',
                'mailer' => 'smtp',
                'host' => 'smtp.gmail.com',
                'port' => 465,
                'username' => 'dgadmision@unap.edu.pe',
                'password' => 'ghsp ydhg ezya rbtx',
                'encryption' => 'ssl',
                'from_address' => 'dgadmision@unap.edu.pe',
                'from_name' => 'DIRECCIÓN DE ADMISIÓN UNA PUNO',
                'is_active' => true,
                'is_default' => true,
            ],
            [
                'name' => 'Admisión Informática',
                'mailer' => 'smtp',
                'host' => 'smtp.gmail.com',
                'port' => 465,
                'username' => 'admision.informatica@unap.edu.pe',
                'password' => 'yxrv jiji ervw wunc',
                'encryption' => 'ssl',
                'from_address' => 'dgadmision@unap.edu.pe',
                'from_name' => 'DIRECCIÓN DE ADMISIÓN UNA PUNO',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Admisión Informática 1',
                'mailer' => 'smtp',
                'host' => 'smtp.gmail.com',
                'port' => 465,
                'username' => 'admision.informatica1@unap.edu.pe',
                'password' => 'ephi ltcs espu rafx',
                'encryption' => 'ssl',
                'from_address' => 'dgadmision@unap.edu.pe',
                'from_name' => 'DIRECCIÓN DE ADMISIÓN UNA PUNO',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Admisión Informática 2',
                'mailer' => 'smtp',
                'host' => 'smtp.gmail.com',
                'port' => 465,
                'username' => 'admision.informatica2@unap.edu.pe',
                'password' => 'jldj fyoc ajxd ynpl',
                'encryption' => 'ssl',
                'from_address' => 'admision.informatica2@unap.edu.pe',
                'from_name' => 'DIRECCIÓN DE ADMISIÓN UNA PUNO',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Admisión Informática 3',
                'mailer' => 'smtp',
                'host' => 'smtp.gmail.com',
                'port' => 465,
                'username' => 'admision.informatica3@unap.edu.pe',
                'password' => 'aeob ywpk zbry bxtg',
                'encryption' => 'ssl',
                'from_address' => 'admision.informatica3@unap.edu.pe',
                'from_name' => 'DIRECCIÓN DE ADMISIÓN UNA PUNO',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Admisión Informática 4',
                'mailer' => 'smtp',
                'host' => 'smtp.gmail.com',
                'port' => 465,
                'username' => 'admision.informatica4@unap.edu.pe',
                'password' => 'gctv sewy hgmq qevm',
                'encryption' => 'ssl',
                'from_address' => 'admision.informatica4@unap.edu.pe',
                'from_name' => 'DIRECCIÓN DE ADMISIÓN UNA PUNO',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Admisión Informática 5',
                'mailer' => 'smtp',
                'host' => 'smtp.gmail.com',
                'port' => 465,
                'username' => 'admision.informatica5@unap.edu.pe',
                'password' => 'nmkf wecy vvsz vrvm',
                'encryption' => 'ssl',
                'from_address' => 'admision.informatica5@unap.edu.pe',
                'from_name' => 'DIRECCIÓN DE ADMISIÓN UNA PUNO',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Admisión OTI 6',
                'mailer' => 'smtp',
                'host' => 'smtp.gmail.com',
                'port' => 465,
                'username' => 'admision.oti6@unap.edu.pe',
                'password' => 'rpai nphi ngbu zvoi',
                'encryption' => 'ssl',
                'from_address' => 'admision.oti6@unap.edu.pe',
                'from_name' => 'DIRECCIÓN DE ADMISIÓN UNA PUNO',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Admisión OTI',
                'mailer' => 'smtp',
                'host' => 'smtp.gmail.com',
                'port' => 465,
                'username' => 'admision.oti@unap.edu.pe',
                'password' => 'uhry kipi mvlk egon',
                'encryption' => 'ssl',
                'from_address' => 'admision.oti@unap.edu.pe',
                'from_name' => 'DIRECCIÓN DE ADMISIÓN UNA PUNO',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Admisión OTI 7',
                'mailer' => 'smtp',
                'host' => 'smtp.gmail.com',
                'port' => 465,
                'username' => 'admision.oti7@unap.edu.pe',
                'password' => 'vuxc lcdp dxfi orpn',
                'encryption' => 'ssl',
                'from_address' => 'admision.oti7@unap.edu.pe',
                'from_name' => 'DIRECCIÓN DE ADMISIÓN UNA PUNO',
                'is_active' => true,
                'is_default' => false,
            ],
        ];

        foreach ($cuentas as $cuenta) {
            DB::table('smtp_accounts')->updateOrInsert(
                ['username' => $cuenta['username']],
               array_merge($cuenta, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
