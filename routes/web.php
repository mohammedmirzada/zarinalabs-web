<?php

use App\Livewire\Admin\CheckIn;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Auth\VerifyEmail;
use App\Livewire\Courses;
use App\Livewire\Home;
use App\Livewire\MyRegistrations;
use App\Livewire\Profile;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::livewire('/', Home::class)->name('home');

Route::livewire('/courses', Courses\Index::class)->name('courses.index');
Route::livewire('/courses/{slug}', Courses\Show::class)->name('courses.show');

Route::middleware('guest')->group(function () {
    Route::livewire('/register', Register::class)->name('register');
    Route::livewire('/login', Login::class)->name('login');
    Route::livewire('/forgot-password', ForgotPassword::class)->name('password.request');
    Route::livewire('/reset-password/{token}', ResetPassword::class)->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Route::livewire('/verify-email', VerifyEmail::class)->name('verification.notice');

    Route::get('/verify-email/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('home')->with('status', 'Your email address is verified.');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    })->name('logout');

    Route::middleware('verified')->group(function () {
        Route::livewire('/profile', Profile::class)->name('profile');
        Route::livewire('/my-registrations', MyRegistrations::class)->name('my-registrations');
    });
});

// Target of the QR code, opened by the admin's phone camera.
// Sits outside /admin so it cannot collide with the Filament panel's routes.
Route::livewire('/check-in/{registration}', CheckIn::class)
    ->middleware(['signed', 'auth', 'admin'])
    ->name('admin.check-in');
