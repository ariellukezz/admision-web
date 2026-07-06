<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\SettingController;
use App\Models\Setting;
use Tests\TestCase;

class PreinscripcionVerificationSettingTest extends TestCase
{
    public function test_get_preinscripcion_email_verification_returns_disabled_state_when_setting_is_off(): void
    {
        Setting::set('preinscripcion_email_verification', '0');

        $controller = new SettingController();
        $response = $controller->getPreinscripcionEmailVerification();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['estado' => false], json_decode($response->getContent(), true));
    }
}
