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
Route::get('/exhibitions/{id}/floorplan', [\App\Http\Controllers\Frontend\FloorplanController::class, 'show'])->name('floorplan.show.public');

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
    
    // Booth Management
    Route::get('/exhibitions/{exhibitionId}/booths', [\App\Http\Controllers\Admin\BoothController::class, 'index'])->name('booths.index');
    Route::get('/exhibitions/{exhibitionId}/booths/create', [\App\Http\Controllers\Admin\BoothController::class, 'create'])->name('booths.create');
    Route::post('/exhibitions/{exhibitionId}/booths', [\App\Http\Controllers\Admin\BoothController::class, 'store'])->name('booths.store');
    Route::get('/exhibitions/{exhibitionId}/booths/{id}', [\App\Http\Controllers\Admin\BoothController::class, 'show'])->name('booths.show');
    Route::get('/exhibitions/{exhibitionId}/booths/{id}/edit', [\App\Http\Controllers\Admin\BoothController::class, 'edit'])->name('booths.edit');
    Route::put('/exhibitions/{exhibitionId}/booths/{id}', [\App\Http\Controllers\Admin\BoothController::class, 'update'])->name('booths.update');
    Route::delete('/exhibitions/{exhibitionId}/booths/{id}', [\App\Http\Controllers\Admin\BoothController::class, 'destroy'])->name('booths.destroy');
    
    // Booking Management
    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{id}', [AdminBookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{id}/process-cancellation', [AdminBookingController::class, 'processCancellation'])->name('bookings.process-cancellation');
    
    // Financial Management
    Route::get('/financial', [FinancialController::class, 'index'])->name('financial.index');
    
    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    
    // Floorplan Management
    Route::get('/exhibitions/{id}/floorplan', [\App\Http\Controllers\Admin\FloorplanController::class, 'show'])->name('floorplan.show');
    Route::post('/exhibitions/{exhibitionId}/booths/{boothId}/position', [\App\Http\Controllers\Admin\FloorplanController::class, 'updateBoothPosition'])->name('floorplan.update-position');
    Route::post('/exhibitions/{exhibitionId}/booths/merge', [\App\Http\Controllers\Admin\FloorplanController::class, 'mergeBooths'])->name('floorplan.merge');
    Route::post('/exhibitions/{exhibitionId}/booths/{boothId}/split', [\App\Http\Controllers\Admin\FloorplanController::class, 'splitBooth'])->name('floorplan.split');
    
    // Booth Requests (Approvals)
    Route::get('/booth-requests', [\App\Http\Controllers\Admin\BoothRequestController::class, 'index'])->name('booth-requests.index');
    Route::post('/booth-requests/{id}/approve', [\App\Http\Controllers\Admin\BoothRequestController::class, 'approve'])->name('booth-requests.approve');
    Route::post('/booth-requests/{id}/reject', [\App\Http\Controllers\Admin\BoothRequestController::class, 'reject'])->name('booth-requests.reject');
});

// Frontend Exhibitor Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Booking
    Route::get('/exhibitions/{exhibitionId}/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
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
    
    // Floorplan (Exhibitor)
    Route::get('/exhibitions/{id}/floorplan', [\App\Http\Controllers\Frontend\FloorplanController::class, 'show'])->name('floorplan.show');
    Route::post('/exhibitions/{exhibitionId}/booths/merge-request', [\App\Http\Controllers\Frontend\FloorplanController::class, 'requestMerge'])->name('floorplan.merge-request');
    Route::post('/exhibitions/{exhibitionId}/booths/{boothId}/split-request', [\App\Http\Controllers\Frontend\FloorplanController::class, 'requestSplit'])->name('floorplan.split-request');
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
