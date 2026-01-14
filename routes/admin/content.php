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
    // Articles Dashboard
    Route::get('articles/dashboard', [\App\Http\Controllers\Admin\ArticleDashboardController::class, 'index'])->name('articles.dashboard');

    // Articles Additional Routes
    Route::post('articles/bulk-delete', [\App\Http\Controllers\Admin\ArticleController::class, 'bulkDelete'])->name('articles.bulk-delete');
    Route::post('articles/{article}/toggle-published', [\App\Http\Controllers\Admin\ArticleController::class, 'togglePublished'])->name('articles.toggle-published');
    Route::post('articles/{article}/toggle-featured', [\App\Http\Controllers\Admin\ArticleController::class, 'toggleFeatured'])->name('articles.toggle-featured');
    Route::post('articles/{article}/toggle-pinned', [\App\Http\Controllers\Admin\ArticleController::class, 'togglePinned'])->name('articles.toggle-pinned');
    Route::post('articles/{article}/archive', [\App\Http\Controllers\Admin\ArticleController::class, 'archive'])->name('articles.archive');
    Route::post('articles/{article}/unarchive', [\App\Http\Controllers\Admin\ArticleController::class, 'unarchive'])->name('articles.unarchive');
    Route::post('articles/{article}/duplicate', [\App\Http\Controllers\Admin\ArticleController::class, 'duplicate'])->name('articles.duplicate');
    Route::post('articles/bulk-publish', [\App\Http\Controllers\Admin\ArticleController::class, 'bulkPublish'])->name('articles.bulk-publish');
    Route::post('articles/bulk-archive', [\App\Http\Controllers\Admin\ArticleController::class, 'bulkArchive'])->name('articles.bulk-archive');
    Route::post('articles/{article}/auto-save', [\App\Http\Controllers\Admin\ArticleController::class, 'autoSave'])->name('articles.auto-save');
    Route::get('articles/{article}/revisions', [\App\Http\Controllers\Admin\ArticleController::class, 'revisions'])->name('articles.revisions');
    Route::post('articles/{article}/restore-revision/{revision}', [\App\Http\Controllers\Admin\ArticleController::class, 'restoreRevision'])->name('articles.restore-revision');

    // Article Tags
    Route::get('articles/tags', [\App\Http\Controllers\Admin\ArticleTagController::class, 'index'])->name('articles.tags.index');
    Route::post('articles/tags', [\App\Http\Controllers\Admin\ArticleTagController::class, 'store'])->name('articles.tags.store');
    Route::put('articles/tags/{tag}', [\App\Http\Controllers\Admin\ArticleTagController::class, 'update'])->name('articles.tags.update');
    Route::delete('articles/tags/{tag}', [\App\Http\Controllers\Admin\ArticleTagController::class, 'destroy'])->name('articles.tags.destroy');
    Route::post('articles/tags/bulk-delete', [\App\Http\Controllers\Admin\ArticleTagController::class, 'bulkDelete'])->name('articles.tags.bulk-delete');
    Route::post('articles/tags/suggest', [\App\Http\Controllers\Admin\ArticleTagController::class, 'suggest'])->name('articles.tags.suggest');
    Route::get('articles/tags/search', [\App\Http\Controllers\Admin\ArticleTagController::class, 'search'])->name('articles.tags.search');

    Route::get('articles/comments', [\App\Http\Controllers\Admin\AdminArticleCommentController::class, 'index'])->name('articles.comments.index');
    Route::prefix('articles/comments')->name('articles.comments.')->group(function () {
        Route::post('/', [\App\Http\Controllers\Admin\AdminArticleCommentController::class, 'store'])->name('store');
        Route::post('/batch-delete', [\App\Http\Controllers\Admin\AdminArticleCommentController::class, 'batchDelete'])->name('batch-delete');
        Route::post('/batch-approve', [\App\Http\Controllers\Admin\AdminArticleCommentController::class, 'batchApprove'])->name('batch-approve');
        Route::post('/batch-reject', [\App\Http\Controllers\Admin\AdminArticleCommentController::class, 'batchReject'])->name('batch-reject');
        Route::post('/block-user', [\App\Http\Controllers\Admin\AdminArticleCommentController::class, 'blockUser'])->name('block-user');
        Route::post('/unblock-user/{userId}', [\App\Http\Controllers\Admin\AdminArticleCommentController::class, 'unblockUser'])->name('unblock-user');
        Route::post('/{comment}/reply', [\App\Http\Controllers\Admin\AdminArticleCommentController::class, 'reply'])->name('reply');
        Route::delete('/{comment}', [\App\Http\Controllers\Admin\AdminArticleCommentController::class, 'destroy'])->name('destroy');
        Route::post('/{comment}/toggle-status', [\App\Http\Controllers\Admin\AdminArticleCommentController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{comment}/toggle-pin', [\App\Http\Controllers\Admin\AdminArticleCommentController::class, 'togglePin'])->name('toggle-pin');
        Route::post('/{comment}/approve', [\App\Http\Controllers\Admin\AdminArticleCommentController::class, 'approve'])->name('approve');
        Route::post('/{comment}/reject', [\App\Http\Controllers\Admin\AdminArticleCommentController::class, 'reject'])->name('reject');
        Route::post('/{comment}/clear-report', [\App\Http\Controllers\Admin\AdminArticleCommentController::class, 'clearReport'])->name('clear-report');
    });
    Route::resource('articles', \App\Http\Controllers\Admin\ArticleController::class);

    // News Dashboard
    Route::get('news/dashboard', [\App\Http\Controllers\Admin\NewsDashboardController::class, 'index'])->name('news.dashboard');

    // News Additional Routes
    Route::post('news/bulk-delete', [\App\Http\Controllers\Admin\NewsController::class, 'bulkDelete'])->name('news.bulk-delete');
    Route::post('news/{news}/toggle-published', [\App\Http\Controllers\Admin\NewsController::class, 'togglePublished'])->name('news.toggle-published');
    Route::post('news/{news}/toggle-featured', [\App\Http\Controllers\Admin\NewsController::class, 'toggleFeatured'])->name('news.toggle-featured');
    Route::post('news/{news}/toggle-pinned', [\App\Http\Controllers\Admin\NewsController::class, 'togglePinned'])->name('news.toggle-pinned');
    Route::post('news/{news}/archive', [\App\Http\Controllers\Admin\NewsController::class, 'archive'])->name('news.archive');
    Route::post('news/{news}/unarchive', [\App\Http\Controllers\Admin\NewsController::class, 'unarchive'])->name('news.unarchive');
    Route::post('news/{news}/duplicate', [\App\Http\Controllers\Admin\NewsController::class, 'duplicate'])->name('news.duplicate');
    Route::post('news/bulk-publish', [\App\Http\Controllers\Admin\NewsController::class, 'bulkPublish'])->name('news.bulk-publish');
    Route::post('news/bulk-archive', [\App\Http\Controllers\Admin\NewsController::class, 'bulkArchive'])->name('news.bulk-archive');
    Route::post('news/{news}/auto-save', [\App\Http\Controllers\Admin\NewsController::class, 'autoSave'])->name('news.auto-save');
    Route::get('news/{news}/revisions', [\App\Http\Controllers\Admin\NewsController::class, 'revisions'])->name('news.revisions');
    Route::post('news/{news}/restore-revision/{revision}', [\App\Http\Controllers\Admin\NewsController::class, 'restoreRevision'])->name('news.restore-revision');

    // News Tags
    Route::get('news/tags', [\App\Http\Controllers\Admin\NewsTagController::class, 'index'])->name('news.tags.index');
    Route::post('news/tags', [\App\Http\Controllers\Admin\NewsTagController::class, 'store'])->name('news.tags.store');
    Route::put('news/tags/{tag}', [\App\Http\Controllers\Admin\NewsTagController::class, 'update'])->name('news.tags.update');
    Route::delete('news/tags/{tag}', [\App\Http\Controllers\Admin\NewsTagController::class, 'destroy'])->name('news.tags.destroy');
    Route::post('news/tags/bulk-delete', [\App\Http\Controllers\Admin\NewsTagController::class, 'bulkDelete'])->name('news.tags.bulk-delete');
    Route::get('news/tags/search', [\App\Http\Controllers\Admin\NewsTagController::class, 'search'])->name('news.tags.search');

    // News Comments (Enhanced Moderation - matching articles)
    Route::get('news/comments', [\App\Http\Controllers\Admin\AdminNewsCommentController::class, 'index'])->name('news.comments.index');
    Route::prefix('news/comments')->name('news.comments.')->group(function () {
        Route::post('/', [\App\Http\Controllers\Admin\AdminNewsCommentController::class, 'store'])->name('store');
        Route::post('/batch-delete', [\App\Http\Controllers\Admin\AdminNewsCommentController::class, 'batchDelete'])->name('batch-delete');
        Route::post('/batch-approve', [\App\Http\Controllers\Admin\AdminNewsCommentController::class, 'batchApprove'])->name('batch-approve');
        Route::post('/batch-reject', [\App\Http\Controllers\Admin\AdminNewsCommentController::class, 'batchReject'])->name('batch-reject');
        Route::post('/block-user', [\App\Http\Controllers\Admin\AdminNewsCommentController::class, 'blockUser'])->name('block-user');
        Route::post('/unblock-user/{userId}', [\App\Http\Controllers\Admin\AdminNewsCommentController::class, 'unblockUser'])->name('unblock-user');
        Route::post('/{comment}/reply', [\App\Http\Controllers\Admin\AdminNewsCommentController::class, 'reply'])->name('reply');
        Route::delete('/{comment}', [\App\Http\Controllers\Admin\AdminNewsCommentController::class, 'destroy'])->name('destroy');
        Route::post('/{comment}/toggle-status', [\App\Http\Controllers\Admin\AdminNewsCommentController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{comment}/toggle-pin', [\App\Http\Controllers\Admin\AdminNewsCommentController::class, 'togglePin'])->name('toggle-pin');
        Route::post('/{comment}/approve', [\App\Http\Controllers\Admin\AdminNewsCommentController::class, 'approve'])->name('approve');
        Route::post('/{comment}/reject', [\App\Http\Controllers\Admin\AdminNewsCommentController::class, 'reject'])->name('reject');
        Route::post('/{comment}/clear-report', [\App\Http\Controllers\Admin\AdminNewsCommentController::class, 'clearReport'])->name('clear-report');
    });
    Route::resource('news', \App\Http\Controllers\Admin\NewsController::class);

    // ============ EMAIL TEMPLATES ============
    Route::resource('email-templates', \App\Http\Controllers\Admin\EmailTemplateController::class);
    Route::get('/email-templates/{email_template}/preview', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'preview'])
        ->name('email-templates.preview');

    // ============ TESTIMONIAL ============
    Route::prefix('testimonial')->name('testimonial.')->group(function () {
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
