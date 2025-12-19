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
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Frontend\NotificationController as FrontendNotificationController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/', [FrontendExhibitionController::class, 'index'])->name('home');
Route::get('/exhibitions', [FrontendExhibitionController::class, 'list'])->name('exhibitions.list');
Route::get('/exhibitions/{id}', [FrontendExhibitionController::class, 'show'])->name('exhibitions.show');
Route::get('/exhibitions/{id}/floorplan', [\App\Http\Controllers\Frontend\FloorplanController::class, 'show'])->name('floorplan.show.public');

// API Routes for Country/State
Route::get('/api/states', [\App\Http\Controllers\Auth\RegisteredUserController::class, 'getStates'])->name('api.states');

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
    
    // Floor Management
    Route::post('/exhibitions/{id}/floors', [ExhibitionController::class, 'storeFloors'])->name('exhibitions.floors.store');
    Route::get('/exhibitions/{id}/floors', [ExhibitionController::class, 'getFloors'])->name('exhibitions.floors.get');
    
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
    Route::get('/bookings/cancellations', [AdminBookingController::class, 'cancellations'])->name('bookings.cancellations');
    Route::get('/bookings/{id}', [AdminBookingController::class, 'show'])->name('bookings.show');
    Route::get('/bookings/{id}/edit', [AdminBookingController::class, 'edit'])->name('bookings.edit');
    Route::put('/bookings/{id}', [AdminBookingController::class, 'update'])->name('bookings.update');
    Route::get('/bookings/{id}/manage-cancellation', [AdminBookingController::class, 'manageCancellation'])->name('bookings.manage-cancellation');
    Route::post('/bookings/{id}/process-cancellation', [AdminBookingController::class, 'processCancellation'])->name('bookings.process-cancellation');
    Route::post('/bookings/{id}/approve-cancellation', [AdminBookingController::class, 'approveCancellation'])->name('bookings.approve-cancellation');
    Route::post('/bookings/{id}/reject-cancellation', [AdminBookingController::class, 'rejectCancellation'])->name('bookings.reject-cancellation');
    Route::get('/exhibitions/{exhibitionId}/bookings', [AdminBookingController::class, 'bookedByExhibition'])->name('exhibitions.bookings');
    Route::delete('/bookings/{id}', [AdminBookingController::class, 'destroy'])->name('bookings.destroy');
    Route::post('/documents/{documentId}/approve', [AdminBookingController::class, 'approveDocument'])->name('bookings.documents.approve');
    Route::post('/documents/{documentId}/reject', [AdminBookingController::class, 'rejectDocument'])->name('bookings.documents.reject');
    
    // Financial Management
    Route::get('/financial', [FinancialController::class, 'index'])->name('financial.index');
    
    // Payment Management
    Route::get('/payments', [\App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/create', [\App\Http\Controllers\Admin\PaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments', [\App\Http\Controllers\Admin\PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{id}', [\App\Http\Controllers\Admin\PaymentController::class, 'show'])->name('payments.show');
    Route::post('/payments/{id}/approve', [\App\Http\Controllers\Admin\PaymentController::class, 'approve'])->name('payments.approve');
    Route::post('/payments/{id}/reject', [\App\Http\Controllers\Admin\PaymentController::class, 'reject'])->name('payments.reject');
    
    // Category Management
    Route::resource('categories', CategoryController::class)->except(['show']);
    
    // Role Management
    Route::get('/roles', [\App\Http\Controllers\Admin\RoleController::class, 'index'])->name('roles.index');
    Route::post('/roles', [\App\Http\Controllers\Admin\RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{id}/edit-permissions', [\App\Http\Controllers\Admin\RoleController::class, 'editPermissions'])->name('roles.edit-permissions');
    Route::put('/roles/{id}/permissions', [\App\Http\Controllers\Admin\RoleController::class, 'updatePermissions'])->name('roles.update-permissions');
    
    // Exhibition Management (Alternative View)
    Route::get('/exhibitions-management', [ExhibitionController::class, 'management'])->name('exhibitions.management');
    
    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    
    // Settings
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/payment-gateway', [\App\Http\Controllers\Admin\SettingsController::class, 'savePaymentGateway'])->name('settings.save-payment-gateway');
    Route::post('/settings/email-sms', [\App\Http\Controllers\Admin\SettingsController::class, 'saveEmailSms'])->name('settings.save-email-sms');
    Route::post('/settings/otp-dlt', [\App\Http\Controllers\Admin\SettingsController::class, 'saveOtpDlt'])->name('settings.save-otp-dlt');
    Route::post('/settings/default-pricing', [\App\Http\Controllers\Admin\SettingsController::class, 'saveDefaultPricing'])->name('settings.save-default-pricing');
    Route::post('/settings/cancellation-charges', [\App\Http\Controllers\Admin\SettingsController::class, 'saveCancellationCharges'])->name('settings.save-cancellation-charges');
    
    // Document Management
    Route::get('/documents', [\App\Http\Controllers\Admin\DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/{id}', [\App\Http\Controllers\Admin\DocumentController::class, 'show'])->name('documents.show');
    Route::post('/documents/{id}/approve', [\App\Http\Controllers\Admin\DocumentController::class, 'approve'])->name('documents.approve');
    Route::post('/documents/{id}/reject', [\App\Http\Controllers\Admin\DocumentController::class, 'reject'])->name('documents.reject');
    Route::post('/documents/bulk-approve', [\App\Http\Controllers\Admin\DocumentController::class, 'bulkApprove'])->name('documents.bulk-approve');
    
    // Document Categories Management (CRUD)
    Route::resource('document-categories', \App\Http\Controllers\Admin\DocumentCategoryController::class);
    
    // Floorplan Management
        Route::get('/exhibitions/{id}/floorplan', [\App\Http\Controllers\Admin\FloorplanController::class, 'show'])->name('floorplan.show');
        Route::get('/exhibitions/{id}/floorplan/config', [\App\Http\Controllers\Admin\FloorplanController::class, 'loadConfig'])->name('floorplan.config.load');
        Route::get('/exhibitions/{id}/floorplan/config/{floorId}', [\App\Http\Controllers\Admin\FloorplanController::class, 'loadConfig'])->name('floorplan.config.load.floor');
        Route::post('/exhibitions/{id}/floorplan/config', [\App\Http\Controllers\Admin\FloorplanController::class, 'saveConfig'])->name('floorplan.config.save');
        Route::post('/exhibitions/{exhibitionId}/booths/{boothId}/position', [\App\Http\Controllers\Admin\FloorplanController::class, 'updateBoothPosition'])->name('floorplan.update-position');
        Route::post('/exhibitions/{exhibitionId}/booths/merge', [\App\Http\Controllers\Admin\FloorplanController::class, 'mergeBooths'])->name('floorplan.merge');
        Route::post('/exhibitions/{exhibitionId}/booths/{boothId}/split', [\App\Http\Controllers\Admin\FloorplanController::class, 'splitBooth'])->name('floorplan.split');
        
        // Booth management
        Route::get('/exhibitions/{exhibitionId}/booths/{id}', [\App\Http\Controllers\Admin\BoothController::class, 'show'])->name('booths.show');
        Route::post('/exhibitions/{exhibitionId}/booths', [\App\Http\Controllers\Admin\BoothController::class, 'store'])->name('booths.store');
    
    // Booth Requests (Approvals)
    Route::get('/booth-requests', [\App\Http\Controllers\Admin\BoothRequestController::class, 'index'])->name('booth-requests.index');
    Route::get('/booth-requests/{id}', [\App\Http\Controllers\Admin\BoothRequestController::class, 'show'])->name('booth-requests.show');
    Route::post('/booth-requests/{id}/approve', [\App\Http\Controllers\Admin\BoothRequestController::class, 'approve'])->name('booth-requests.approve');
    Route::post('/booth-requests/{id}/reject', [\App\Http\Controllers\Admin\BoothRequestController::class, 'reject'])->name('booth-requests.reject');
    
    // Discount Management (Wireframe 30)
    Route::resource('discounts', \App\Http\Controllers\Admin\DiscountController::class);
    
    // Checklist Management (Wireframe 31)
    Route::get('/checklists', [\App\Http\Controllers\Admin\ChecklistController::class, 'index'])->name('checklists.index');
    Route::post('/checklists', [\App\Http\Controllers\Admin\ChecklistController::class, 'store'])->name('checklists.store');
    Route::put('/checklists/{id}', [\App\Http\Controllers\Admin\ChecklistController::class, 'update'])->name('checklists.update');
    Route::delete('/checklists/{id}', [\App\Http\Controllers\Admin\ChecklistController::class, 'destroy'])->name('checklists.destroy');
    
    // Service Configuration (Wireframe 32)
    Route::get('/services/config', [\App\Http\Controllers\Admin\ServiceConfigController::class, 'index'])->name('services.config');
    Route::post('/services/config', [\App\Http\Controllers\Admin\ServiceConfigController::class, 'store'])->name('services.config.store');
    Route::get('/services/config/{id}', [\App\Http\Controllers\Admin\ServiceConfigController::class, 'show'])->name('services.config.show');
    Route::put('/services/config/{id}', [\App\Http\Controllers\Admin\ServiceConfigController::class, 'update'])->name('services.config.update');
    Route::delete('/services/config/{id}', [\App\Http\Controllers\Admin\ServiceConfigController::class, 'destroy'])->name('services.config.destroy');
    Route::post('/services/config/bulk-action', [\App\Http\Controllers\Admin\ServiceConfigController::class, 'bulkAction'])->name('services.config.bulk-action');
    
    // Analytics (Wireframe 33)
    Route::get('/analytics', [\App\Http\Controllers\Admin\AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/export', [\App\Http\Controllers\Admin\AnalyticsController::class, 'export'])->name('analytics.export');
    
    // Exhibitor Management (Wireframes 34-35)
    Route::get('/exhibitors', [\App\Http\Controllers\Admin\ExhibitorManagementController::class, 'index'])->name('exhibitors.index');
    Route::get('/exhibitors/{id}', [\App\Http\Controllers\Admin\ExhibitorManagementController::class, 'show'])->name('exhibitors.show');
    Route::put('/exhibitors/{id}/contact', [\App\Http\Controllers\Admin\ExhibitorManagementController::class, 'updateContact'])->name('exhibitors.update-contact');
    Route::put('/exhibitors/{id}/booth', [\App\Http\Controllers\Admin\ExhibitorManagementController::class, 'updateBooth'])->name('exhibitors.update-booth');
    Route::post('/exhibitors/{id}/messages', [\App\Http\Controllers\Admin\ExhibitorManagementController::class, 'sendMessage'])->name('exhibitors.messages.send');
    Route::post('/exhibitors/{id}/messages/close', [\App\Http\Controllers\Admin\ExhibitorManagementController::class, 'closeChat'])->name('exhibitors.messages.close');
    
    // Admin Communication Center
    Route::get('/communications', [\App\Http\Controllers\Admin\CommunicationController::class, 'index'])->name('communications.index');
    Route::get('/communications/{id}', [\App\Http\Controllers\Admin\CommunicationController::class, 'show'])->name('communications.show');
    Route::get('/communications/new', [\App\Http\Controllers\Admin\CommunicationController::class, 'create'])->name('communications.create');
    Route::get('/communications/exhibitors/list', [\App\Http\Controllers\Admin\CommunicationController::class, 'getExhibitorsList'])->name('communications.exhibitors-list');
    Route::get('/communications/new-chat/{exhibitorId}', [\App\Http\Controllers\Admin\CommunicationController::class, 'newChat'])->name('communications.new-chat');
    Route::post('/communications', [\App\Http\Controllers\Admin\CommunicationController::class, 'store'])->name('communications.store');
    Route::post('/communications/mark-as-read', [\App\Http\Controllers\Admin\CommunicationController::class, 'markAsRead'])->name('communications.mark-as-read');
    Route::post('/communications/delete', [\App\Http\Controllers\Admin\CommunicationController::class, 'delete'])->name('communications.delete');
    Route::post('/communications/archive', [\App\Http\Controllers\Admin\CommunicationController::class, 'archive'])->name('communications.archive');
    Route::post('/communications/unarchive', [\App\Http\Controllers\Admin\CommunicationController::class, 'unarchive'])->name('communications.unarchive');
    
    // Email Management (Wireframe 36)
    Route::get('/emails', [\App\Http\Controllers\Admin\EmailManagementController::class, 'index'])->name('emails.index');
    Route::get('/emails/{id}/edit', [\App\Http\Controllers\Admin\EmailManagementController::class, 'edit'])->name('emails.edit');
    Route::put('/emails/{id}', [\App\Http\Controllers\Admin\EmailManagementController::class, 'update'])->name('emails.update');
    Route::post('/emails/{id}/toggle', [\App\Http\Controllers\Admin\EmailManagementController::class, 'toggleStatus'])->name('emails.toggle');
    
    // Notifications
    Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [AdminNotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [AdminNotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
});

// Frontend Exhibitor Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Booking
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/exhibitions/{exhibitionId}/book', [BookingController::class, 'book'])->name('bookings.book');
    Route::get('/exhibitions/{exhibitionId}/booking/details', [BookingController::class, 'details'])->name('bookings.details');
    Route::get('/exhibitions/{exhibitionId}/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{id}', [BookingController::class, 'show'])->name('bookings.show');
    Route::get('/bookings/{id}/edit', [BookingController::class, 'edit'])->name('bookings.edit');
    Route::put('/bookings/{id}', [BookingController::class, 'update'])->name('bookings.update');
    Route::get('/bookings/{id}/cancel', [BookingController::class, 'showCancel'])->name('bookings.cancel.show');
    Route::post('/bookings/{id}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/bookings/{id}/replace', [BookingController::class, 'replace'])->name('bookings.replace');
    
    // Payment
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{bookingId}', [PaymentController::class, 'create'])->name('payments.create');
    Route::get('/payments/pay/{paymentId}', [PaymentController::class, 'pay'])->name('payments.pay');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{paymentId}/confirmation', [PaymentController::class, 'confirmation'])->name('payments.confirmation');
    Route::post('/payments/{paymentId}/upload-proof', [PaymentController::class, 'uploadProof'])->name('payments.upload-proof');
    Route::get('/payments/{paymentId}/download', [PaymentController::class, 'download'])->name('payments.download');
    
    // Documents
    Route::resource('documents', DocumentController::class);
    Route::get('/bookings/{bookingId}/required-documents', [DocumentController::class, 'requiredDocuments'])->name('bookings.required-documents');
    
    // Document Categories (Read-only for exhibitors - only active categories)
    Route::get('/document-categories', [\App\Http\Controllers\Frontend\DocumentCategoryController::class, 'index'])->name('document-categories.index');
    
    // Badges
    Route::resource('badges', BadgeController::class);
    Route::get('/badges/{id}/download', [BadgeController::class, 'download'])->name('badges.download');
    
    // Messages
    Route::resource('messages', MessageController::class);
    Route::get('/messages/new/chat', [MessageController::class, 'newChat'])->name('messages.new-chat');
    Route::post('/messages/{id}/archive', [MessageController::class, 'archive'])->name('messages.archive-single');
    Route::post('/messages/archive', [MessageController::class, 'archiveBulk'])->name('messages.archive');
    Route::post('/messages/mark-as-read', [MessageController::class, 'markAsRead'])->name('messages.mark-as-read');
    Route::post('/messages/delete', [MessageController::class, 'delete'])->name('messages.delete');
    Route::post('/messages/unarchive', [MessageController::class, 'unarchive'])->name('messages.unarchive');
    
    // Wallet
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    
    // Additional Services
    Route::get('/services', [\App\Http\Controllers\Frontend\ServiceController::class, 'index'])->name('services.index');
    Route::post('/services/add-to-cart', [\App\Http\Controllers\Frontend\ServiceController::class, 'addToCart'])->name('services.add-to-cart');
    Route::post('/services/update-cart', [\App\Http\Controllers\Frontend\ServiceController::class, 'updateCart'])->name('services.update-cart');
    Route::post('/services/remove-from-cart', [\App\Http\Controllers\Frontend\ServiceController::class, 'removeFromCart'])->name('services.remove-from-cart');
    Route::post('/services/checkout', [\App\Http\Controllers\Frontend\ServiceController::class, 'checkout'])->name('services.checkout');
    
    // Sponsorships
    Route::get('/sponsorships', [\App\Http\Controllers\Frontend\SponsorshipController::class, 'index'])->name('sponsorships.index');
    Route::post('/sponsorships/{id}/select', [\App\Http\Controllers\Frontend\SponsorshipController::class, 'select'])->name('sponsorships.select');
    
    // Floorplan (Exhibitor)
    Route::get('/exhibitions/{id}/floorplan', [\App\Http\Controllers\Frontend\FloorplanController::class, 'show'])->name('floorplan.show');
    Route::post('/exhibitions/{exhibitionId}/booths/merge-request', [\App\Http\Controllers\Frontend\FloorplanController::class, 'requestMerge'])->name('floorplan.merge-request');
    Route::post('/exhibitions/{exhibitionId}/booths/{boothId}/split-request', [\App\Http\Controllers\Frontend\FloorplanController::class, 'requestSplit'])->name('floorplan.split-request');
    
    // Notifications
    Route::get('/notifications', [FrontendNotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [FrontendNotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [FrontendNotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
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
