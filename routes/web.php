<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ExhibitionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\FinancialController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Frontend\ExhibitionController as FrontendExhibitionController;
use App\Http\Controllers\Frontend\BookingController;
use App\Http\Controllers\Frontend\Auth\OtpController;
use App\Http\Controllers\Frontend\DashboardController;
use App\Http\Controllers\Frontend\PaymentController;
use App\Http\Controllers\Frontend\DocumentController;
use App\Http\Controllers\Frontend\BadgeController;
use App\Http\Controllers\Frontend\MessageController;
use App\Http\Controllers\Frontend\WalletController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/', [FrontendExhibitionController::class, 'index'])->name('home');
Route::get('/exhibitions', [FrontendExhibitionController::class, 'list'])->name('exhibitions.list');
Route::get('/exhibitions/{id}', [FrontendExhibitionController::class, 'show'])->name('exhibitions.show');

// Admin Routes
Route::middleware(['auth', 'role:Admin|Sub Admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Exhibition Management
    Route::resource('exhibitions', ExhibitionController::class);
    Route::get('/exhibitions/{id}/step2', [ExhibitionController::class, 'step2'])->name('exhibitions.step2');
    Route::post('/exhibitions/{id}/step2', [ExhibitionController::class, 'storeStep2'])->name('exhibitions.step2.store');
    Route::get('/exhibitions/{id}/step3', [ExhibitionController::class, 'step3'])->name('exhibitions.step3');
    Route::post('/exhibitions/{id}/step3', [ExhibitionController::class, 'storeStep3'])->name('exhibitions.step3.store');
    Route::get('/exhibitions/{id}/step4', [ExhibitionController::class, 'step4'])->name('exhibitions.step4');
    Route::post('/exhibitions/{id}/step4', [ExhibitionController::class, 'storeStep4'])->name('exhibitions.step4.store');
    
    // User Management
    Route::resource('users', UserController::class);
    
    // Booking Management
    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{id}', [AdminBookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{id}/process-cancellation', [AdminBookingController::class, 'processCancellation'])->name('bookings.process-cancellation');
    
    // Financial Management
    Route::get('/financial', [FinancialController::class, 'index'])->name('financial.index');
    
    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
});

// Frontend Exhibitor Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Booking
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{id}', [BookingController::class, 'show'])->name('bookings.show');
    Route::put('/bookings/{id}', [BookingController::class, 'update'])->name('bookings.update');
    Route::post('/bookings/{id}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/bookings/{id}/replace', [BookingController::class, 'replace'])->name('bookings.replace');
    
    // Payment
    Route::get('/payments/{bookingId}', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    
    // Documents
    Route::resource('documents', DocumentController::class);
    
    // Badges
    Route::resource('badges', BadgeController::class);
    Route::get('/badges/{id}/download', [BadgeController::class, 'download'])->name('badges.download');
    
    // Messages
    Route::resource('messages', MessageController::class);
    Route::post('/messages/{id}/archive', [MessageController::class, 'archive'])->name('messages.archive');
    
    // Wallet
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
});

// OTP Authentication
Route::get('/login-otp', [OtpController::class, 'showLoginForm'])->name('login.otp');
Route::post('/send-otp', [OtpController::class, 'sendOtp'])->name('otp.send');
Route::post('/verify-otp', [OtpController::class, 'verifyOtp'])->name('otp.verify');

// Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
