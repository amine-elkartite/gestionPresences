<?php

namespace Tests\Feature;

use Tests\TestCase;

class PinLoginPageTest extends TestCase
{
    public function test_the_pin_login_page_has_the_new_presence_management_layout(): void
    {
        $response = $this->get(route('pin.show'));

        $response->assertOk();
        $response->assertSee('Une gestion moderne', false);
        $response->assertSee('présence', false);
        $response->assertSee('Espace administration', false);
        $response->assertSee('name="pin_code"', false);
        $response->assertSee('name="action"', false);
        $response->assertSee('action="'.route('pin.store').'"', false);
    }
}
