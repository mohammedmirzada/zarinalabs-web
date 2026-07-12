<?php

namespace Tests\Feature;

use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ResetPassword as ResetPasswordPage;
use App\Livewire\Profile;
use App\Models\User;
use App\Notifications\ResetPassword;
use App\Notifications\VerifyEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function fillRegistration(): \Livewire\Features\SupportTesting\Testable
    {
        return Livewire::test(Register::class)
            ->set('name', 'Ali Hassan')
            ->set('gender', 'male')
            ->set('date_of_birth', '1998-05-04')
            ->set('email', 'ali@example.com')
            ->set('phone', '+9647500000000')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->set('city', 'erbil')
            ->set('education_level', 'bachelor')
            ->set('it_interest', 'cyber_security');
    }

    public function test_register_creates_user_logs_in_and_sends_verification_email(): void
    {
        Notification::fake();

        $this->fillRegistration()->call('register')->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'ali@example.com')->first();

        $this->assertNotNull($user, 'user was not created');
        $this->assertFalse($user->is_admin, 'new user must not be admin');
        $this->assertNull($user->email_verified_at, 'new user must be unverified');
        $this->assertTrue(Auth::check(), 'user should be logged in after registering');
        $this->assertTrue(Hash::check('Password123!', $user->password));

        Notification::assertSentTo($user, VerifyEmail::class);
        $this->assertInstanceOf(ShouldQueue::class, new VerifyEmail, 'verification email must be queued');
    }

    public function test_register_rejects_option_values_outside_config(): void
    {
        $this->fillRegistration()->set('city', 'paris')->call('register')->assertHasErrors('city');
        $this->fillRegistration()->set('it_interest', 'astrology')->call('register')->assertHasErrors('it_interest');
    }

    public function test_login_succeeds_and_throttles_after_five_failures(): void
    {
        $user = User::factory()->create(['email' => 'kaya@example.com', 'password' => 'secret-password']);

        Livewire::test(Login::class)
            ->set('email', 'kaya@example.com')->set('password', 'secret-password')
            ->call('login')->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);

        Auth::logout();

        for ($i = 0; $i < 5; $i++) {
            Livewire::test(Login::class)
                ->set('email', 'kaya@example.com')->set('password', 'wrong')
                ->call('login')->assertHasErrors('email');
        }

        // Correct password now, but the throttle must still refuse.
        Livewire::test(Login::class)
            ->set('email', 'kaya@example.com')->set('password', 'secret-password')
            ->call('login')->assertHasErrors('email');
        $this->assertGuest();
    }

    public function test_signed_link_verifies_the_email(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);

        $this->actingAs($user)->get($url)->assertRedirect(route('home'));
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_unverified_user_cannot_open_profile_but_can_browse_courses(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get('/profile')->assertRedirect(route('verification.notice'));
        $this->actingAs($user)->get('/courses')->assertOk();
        $this->get('/courses')->assertOk();  // guests too
    }

    public function test_profile_updates_allowed_fields_only(): void
    {
        $user = User::factory()->create(['city' => 'erbil', 'name' => 'Original Name']);

        Livewire::actingAs($user)->test(Profile::class)
            ->set('phone', '+9647701111111')
            ->set('city', 'duhok')
            ->set('education_level', 'master')
            ->set('it_interest', 'data_science')
            ->call('updateProfile')->assertHasNoErrors();

        $user->refresh();
        $this->assertSame('duhok', $user->city);
        $this->assertSame('master', $user->education_level);
        $this->assertSame('Original Name', $user->name, 'name must stay read-only');

        Livewire::actingAs($user)->test(Profile::class)->set('city', 'atlantis')
            ->call('updateProfile')->assertHasErrors('city');
    }

    public function test_password_change_requires_the_current_password(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);

        Livewire::actingAs($user)->test(Profile::class)
            ->set('current_password', 'not-it')
            ->set('password', 'New-password1')
            ->set('password_confirmation', 'New-password1')
            ->call('updatePassword')->assertHasErrors('current_password');

        Livewire::actingAs($user)->test(Profile::class)
            ->set('current_password', 'old-password')
            ->set('password', 'New-password1')
            ->set('password_confirmation', 'New-password1')
            ->call('updatePassword')->assertHasNoErrors();

        $this->assertTrue(Hash::check('New-password1', $user->fresh()->password));
    }

    public function test_password_reset_end_to_end(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        Livewire::test(ForgotPassword::class)->set('email', $user->email)
            ->call('sendResetLink')->assertHasNoErrors();

        $this->assertInstanceOf(ShouldQueue::class, new ResetPassword('token'), 'reset email must be queued');

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            Livewire::test(ResetPasswordPage::class, ['token' => $notification->token])
                ->set('email', $user->email)
                ->set('password', 'Brand-new1')
                ->set('password_confirmation', 'Brand-new1')
                ->call('resetPassword')->assertHasNoErrors()->assertRedirect(route('login'));

            return true;
        });

        $this->assertTrue(Hash::check('Brand-new1', $user->fresh()->password));
    }

    /**
     * The HTML and plain-text mail views are separate files, so it is easy to
     * rebrand one and forget the other. Check the real message, both parts.
     */
    public function test_both_mime_parts_of_the_email_are_branded(): void
    {
        $user = User::factory()->unverified()->create();

        $user->sendEmailVerificationNotification();

        $messages = Mail::getSymfonyTransport()->messages();
        $this->assertCount(1, $messages);

        $email = $messages[0]->getOriginalMessage();

        foreach (['html' => $email->getHtmlBody(), 'text' => $email->getTextBody()] as $part => $body) {
            $this->assertStringContainsString('ZARINALABS', $body, "{$part} part lost the wordmark");
            $this->assertStringContainsString('Lions Fort', $body, "{$part} part lost the Lions Fort footer");
            $this->assertStringContainsString('lionsfortco.com', $body, "{$part} part lost the Lions Fort link");
            $this->assertStringNotContainsString('All rights reserved', $body, "{$part} part kept Laravel's footer");
            $this->assertStringNotContainsString('laravel.com', $body, "{$part} part kept a Laravel asset");
        }

        $this->assertStringContainsString('#720a0f', strtolower($email->getHtmlBody()));
        $this->assertSame('Verify your ZARINALABS email', $email->getSubject());
    }
}
