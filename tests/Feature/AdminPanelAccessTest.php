<?php

namespace Tests\Feature;

use App\Filament\Pages\Catalog\CategoriesProducts;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_admin_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_regular_users_cannot_access_the_admin_panel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_admin_users_can_access_the_admin_panel(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true])->save();

        $this->actingAs($user)->get('/admin')->assertRedirect(CategoriesProducts::getUrl());
    }

    public function test_registration_cannot_grant_admin_access(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'is_admin' => true,
        ]);

        $this->assertFalse((bool) User::query()->where('email', 'test@example.com')->value('is_admin'));
    }
}
