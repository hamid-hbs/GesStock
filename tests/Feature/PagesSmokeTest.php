<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PagesSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_toutes_les_pages_admin_repondent_200(): void
    {
        Role::create(['name' => 'admin']);
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $pages = [
            'dashboard', 'types.index', 'produits.index', 'membres.index',
            'clients.index', 'productions.index', 'ventes.index', 'mouvements.index',
        ];

        foreach ($pages as $route) {
            $response = $this->actingAs($admin)->get(route($route));
            $this->assertEquals(200, $response->status(), "Route {$route} : ".$response->status());
        }
    }

    public function test_invite_redirige_vers_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }
}
