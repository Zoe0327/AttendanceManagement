<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;
use App\Models\User;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_verification_email_is_sent_after_registration()
    {
        Notification::fake();
        
        $response = $this->post(route('register'), [
            'name' => 'テスト 太郎',
            'email' => 'testtaro@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasNoErrors();

        $user = User::first();
        $this->assertNotNull($user);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_user_is_redirected_to_verification_site_when_clicking_verification_link_button()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        /** @var \App\Models\User $user */
        $this->actingAs($user);

        $response = $this->get(route('verification.notice'));

        $response->assertStatus(200);

        // ボタンのテキストが表示されているか確認
        $response->assertSee('認証はこちらから');

        // 正しいリンクが含まれているか確認
        $response->assertSee('https://mailtrap.io/home', false);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->get($verificationUrl);
        $response->assertRedirect(route('user.attendance.index', ['verified' => 1]));
    }

    public function test_user_is_redirected_to_attendance_page_after_email_verification()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        /** @var \App\Models\User $user */
        $this->actingAs($user);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->get($verificationUrl);
        $response->assertRedirect(route('user.attendance.index', ['verified' => 1]));

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

}
