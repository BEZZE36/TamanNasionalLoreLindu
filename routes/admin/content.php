<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\FeedbackController as AdminFeedbackController;
use App\Http\Controllers\Admin\NewsletterController as AdminNewsletterController;

/*
|--------------------------------------------------------------------------
| Admin Routes - Content Management
|--------------------------------------------------------------------------
| Articles, News, Announcements, Feedback, Site Info, Newsletter
*/

Route::middleware(['auth:admin', 'menu.access'])->group(function () {
    // ============ NEWSLETTER SUBSCRIBERS ============
    Route::prefix('newsletter')->name('newsletter.')->group(function () {
        Route::get('/', [AdminNewsletterController::class, 'index'])->name('index');
        Route::patch('/{subscriber}/toggle', [AdminNewsletterController::class, 'toggleStatus'])->name('toggle');
        Route::delete('/{subscriber}', [AdminNewsletterController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-delete', [AdminNewsletterController::class, 'bulkDelete'])->name('bulk-delete');
        Route::get('/export', [AdminNewsletterController::class, 'export'])->name('export');

        // ============ NEWSLETTER CAMPAIGNS ============
        Route::prefix('campaigns')->name('campaigns.')->group(function () {
            Route::get('/', [AdminNewsletterController::class, 'campaigns'])->name('index');
            Route::get('/create', [AdminNewsletterController::class, 'createCampaign'])->name('create');
            Route::post('/', [AdminNewsletterController::class, 'storeCampaign'])->name('store');
            Route::get('/{campaign}/edit', [AdminNewsletterController::class, 'editCampaign'])->name('edit');
            Route::put('/{campaign}', [AdminNewsletterController::class, 'updateCampaign'])->name('update');
            Route::delete('/{campaign}', [AdminNewsletterController::class, 'destroyCampaign'])->name('destroy');
            Route::get('/{campaign}/preview', [AdminNewsletterController::class, 'previewCampaign'])->name('preview');
            Route::post('/{campaign}/send', [AdminNewsletterController::class, 'sendCampaign'])->name('send');
            Route::post('/{campaign}/test-email', [AdminNewsletterController::class, 'sendTestEmail'])->name('test-email');
            Route::post('/{campaign}/cancel', [AdminNewsletterController::class, 'cancelCampaign'])->name('cancel');
        });
    });


    // ============ ANNOUNCEMENTS ============
    Route::prefix('announcements')->name('announcements.')->group(function () {
        Route::get('/statistics', [AdminAnnouncementController::class, 'statistics'])->name('statistics');
        Route::get('/export', [AdminAnnouncementController::class, 'export'])->name('export');
        Route::post('/bulk-delete', [AdminAnnouncementController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/{announcement}/toggle', [AdminAnnouncementController::class, 'toggleStatus'])->name('toggle');
        Route::post('/{announcement}/duplicate', [AdminAnnouncementController::class, 'duplicate'])->name('duplicate');
        Route::get('/{announcement}/preview', [AdminAnnouncementController::class, 'preview'])->name('preview');
    });

    // Announcement Tracking (public access)
    Route::post('announcements/{announcement}/track-view', [AdminAnnouncementController::class, 'trackView'])
        ->name('announcements.track-view')->withoutMiddleware(['auth:admin']);
    Route::post('announcements/{announcement}/track-click', [AdminAnnouncementController::class, 'trackClick'])
        ->name('announcements.track-click')->withoutMiddleware(['auth:admin']);
    Route::post('announcements/{announcement}/track-dismiss', [AdminAnnouncementController::class, 'trackDismiss'])
        ->name('announcements.track-dismiss')->withoutMiddleware(['auth:admin']);

    Route::resource('announcements', AdminAnnouncementController::class);

    // ============ ARTICLES & NEWS ============
    Route::resource('articles', \App\Http\Controllers\Admin\ArticleController::class);
    Route::resource('news', \App\Http\Controllers\Admin\NewsController::class);

    // ============ EMAIL TEMPLATES ============
    Route::resource('email-templates', \App\Http\Controllers\Admin\EmailTemplateController::class);
    Route::get('/email-templates/{email_template}/preview', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'preview'])
        ->name('email-templates.preview');

    // ============ FEEDBACK ============
    Route::prefix('feedback')->name('feedback.')->group(function () {
        Route::get('/', [AdminFeedbackController::class, 'index'])->name('index');
        Route::get('/create', [AdminFeedbackController::class, 'create'])->name('create');
        Route::post('/', [AdminFeedbackController::class, 'store'])->name('store');
        Route::get('/{feedback}/edit', [AdminFeedbackController::class, 'edit'])->name('edit');
        Route::put('/{feedback}', [AdminFeedbackController::class, 'update'])->name('update');
        Route::get('/{feedback}', [AdminFeedbackController::class, 'show'])->name('show');
        Route::post('/{feedback}/reply', [AdminFeedbackController::class, 'reply'])->name('reply');
        Route::patch('/{feedback}/status', [AdminFeedbackController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{feedback}', [AdminFeedbackController::class, 'destroy'])->name('destroy');
        Route::delete('/reply/{reply}', [AdminFeedbackController::class, 'deleteReply'])->name('reply.destroy');
        Route::put('/reply/{reply}', [AdminFeedbackController::class, 'updateReply'])->name('reply.update');
    });

    // ============ SITE INFO ============
    Route::prefix('site-info')->name('site-info.')->group(function () {
        Route::get('/privacy', [\App\Http\Controllers\Admin\SiteInfoController::class, 'privacy'])->name('privacy');
        Route::put('/privacy', [\App\Http\Controllers\Admin\SiteInfoController::class, 'updatePrivacy'])->name('privacy.update');
        Route::get('/terms', [\App\Http\Controllers\Admin\SiteInfoController::class, 'terms'])->name('terms');
        Route::put('/terms', [\App\Http\Controllers\Admin\SiteInfoController::class, 'updateTerms'])->name('terms.update');
        Route::get('/about', [\App\Http\Controllers\Admin\SiteInfoController::class, 'about'])->name('about');
        Route::put('/about', [\App\Http\Controllers\Admin\SiteInfoController::class, 'updateAbout'])->name('about.update');
        Route::get('/contact', [\App\Http\Controllers\Admin\SiteInfoController::class, 'contact'])->name('contact');
        Route::put('/contact', [\App\Http\Controllers\Admin\SiteInfoController::class, 'updateContact'])->name('contact.update');
        Route::get('/faq', [\App\Http\Controllers\Admin\SiteInfoController::class, 'faq'])->name('faq');
        Route::put('/faq', [\App\Http\Controllers\Admin\SiteInfoController::class, 'updateFaq'])->name('faq.update');
        Route::get('/social', [\App\Http\Controllers\Admin\SiteInfoController::class, 'social'])->name('social');
        Route::put('/social', [\App\Http\Controllers\Admin\SiteInfoController::class, 'updateSocial'])->name('social.update');
        Route::get('/detail-web', [\App\Http\Controllers\Admin\SiteInfoController::class, 'detailWeb'])->name('detail-web');
        Route::put('/detail-web', [\App\Http\Controllers\Admin\SiteInfoController::class, 'updateDetailWeb'])->name('detail-web.update');
        Route::get('/geocode/reverse', [\App\Http\Controllers\Admin\SiteInfoController::class, 'reverseGeocode'])->name('geocode.reverse');
        Route::get('/general', [\App\Http\Controllers\Admin\SiteInfoController::class, 'general'])->name('general');
        Route::put('/general', [\App\Http\Controllers\Admin\SiteInfoController::class, 'updateGeneral'])->name('general.update');
        Route::get('/setting/create', [\App\Http\Controllers\Admin\SiteInfoController::class, 'createSetting'])->name('setting.create');
        Route::post('/setting', [\App\Http\Controllers\Admin\SiteInfoController::class, 'storeSetting'])->name('setting.store');
        Route::delete('/setting/{setting}', [\App\Http\Controllers\Admin\SiteInfoController::class, 'destroySetting'])->name('setting.destroy');
        Route::post('/setting/clear-cache', [\App\Http\Controllers\Admin\SiteInfoController::class, 'clearCache'])->name('setting.clear-cache');
    });
});
