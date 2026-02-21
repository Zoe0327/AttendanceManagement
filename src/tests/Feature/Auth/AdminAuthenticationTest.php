<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_admin_login_email_is_required()
    {
        $response = $this->post(route('admin.login'),[
            'email' => '',
            'password' => 'password',
        ]);

        $this->assertGuest('admin');

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    public function test_admin_login_password_is_required()
    {
        $response = $this->post(route('admin.login'), [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $this->assertGuest('admin');

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    public function test_admin_login_fails_with_invalid_credentials()
    {
        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(route('admin.login'), [
            'email' => 'admin@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertGuest('admin');

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }
}
