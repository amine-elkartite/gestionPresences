<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_administrator_sees_the_professional_navigation_shell(): void
    {
        $response = $this->withSession(['role' => 'admin'])->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Temps • Présence • Sérénité');
        $response->assertSee('Cartes NFC');
        $response->assertSee('Terminaux NFC');
        $response->assertSee('Rechercher dans votre espace');
    }
}
