<?php

use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\AddOnController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlansController;
use App\Http\Controllers\SubscriptionsController;
use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\CreditNotesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HostedPageController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\DeleteRecordsController;
use App\Http\Controllers\ClicksController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PartnersAffiliatesController;
use App\Http\Controllers\PartnerUsersController;
use App\Http\Controllers\PlanFeaturesController;
use App\Http\Controllers\ProviderDataController;
use App\Http\Controllers\SignupController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\ClicksEmailAlertController;
use App\Http\Controllers\PartnerClicksController;
use App\Http\Controllers\ApiController;

use App\Mail\Email;
use App\Mail\ClickUsage;
use App\Models\Plans;
use League\Csv\Query\Row;

Route::get('/download/{filepath}', [ProviderDataController::class, 'downloadS3Files'])->where('filepath', '.*')->middleware(['isLoggedIn','isAdmin']);
//Login and Password Management
Route::middleware('alreadyLoggedIn')->group(function () {

    Route::get('/login', [UserAuthController::class, 'login']);

    Route::post('/login-user', [UserAuthController::class, 'loginUser']);

    Route::get('/admin/login', [UserAuthController::class, 'adminLogin']);

    Route::post('/login-admin', [UserAuthController::class, 'loginAdmin']);

    Route::get('/signup', [UserAuthController::class, 'signup']);

    Route::post('/signup-partner', [SignupController::class, 'signupPartner']);

});

Route::get('/logout', [UserAuthController::class, 'logout']);

Route::get('/reset-password', [UserAuthController::class, 'resetView']);
Route::post('/forgot-password', [UserAuthController::class, 'forgotPassword']);

Route::post('/reset-password', [UserAuthController::class, 'resetPassword']);

Route::get('/change-password/{token}', [UserAuthController::class, 'changePassword']);

Route::post('/verify-otp', [UserAuthController::class, 'verifyOtp'])->name('verify.otp');

Route::get('/resend-otp', [UserAuthController::class, 'resendOtp'])->name('resend.otp');

Route::get('/verify-otp', [UserAuthController::class, 'verifyOtpForm'])->name('verify.otp.form');

Route::get('/verify-otp-signup', [SignupController::class, 'verifySignupOtpForm'])->name('verify.otp.signup.form');

Route::post('/verify-otp-signup', [SignupController::class, 'verifySignupOtp'])->name('verify.otp.signup');

Route::get('/resend-otp-signup', [SignupController::class, 'resendSignupOtp'])->name('resend.otp.signup');

Route::post('/upload-csv', [SignupController::class, 'uploadAndProcessCSV']);
Route::post('/save-provider-data-signup', [SignupController::class, 'saveProviderData']);
Route::post('/register', [SignupController::class, 'registerLead']);

Route::get('/register-success', [SignupController::class, 'registerSuccess']);

Route::get('/resend-otp', [UserAuthController::class, 'resendOtp'])->name('resend.otp');

Route::get('/reset-mail', [UserAuthController::class, 'resetMail']);

Route::get('/admin/verify-otp', [UserAuthController::class, 'verifyAdminOtpForm'])->name('admin.verify.otp.form');

Route::post('/admin/verify-otp', [UserAuthController::class, 'verifyAdminOtp'])->name('admin.verify.otp');

Route::get('/admin/resend-otp', [UserAuthController::class, 'resendAdminOtp'])->name('admin.resend.otp');

Route::get('/admin/reset-password', [UserAuthController::class, 'adminResetView']);
Route::post('/admin/forgot-password', [UserAuthController::class, 'adminForgotPassword']);

Route::post('/admin/reset-password', [UserAuthController::class, 'adminResetPassword']);

Route::get('/admin/reset-mail', [UserAuthController::class, 'adminResetMail']);

Route::get('/admin/change-password/{token}', [UserAuthController::class, 'adminChangePassword']);

// Partner Routes
Route::middleware(['isLoggedIn', 'isPartner'])->group(function () {

    Route::get('/profile', [ProfileController::class, 'getProfile']);

    Route::post('/invite-user', [ProfileController::class, 'inviteUser']);

    Route::post('/update-password', [ProfileController::class, 'updatePassword']);

    Route::post('/update-address', [ProfileController::class, 'updateAddress']);

    Route::get('/', [PlansController::class, 'getPlan'])->name('partner.plans');
    Route::get('/get-addon', [AddOnController::class, 'getAddon']);
    Route::get('/select-addon/{code}', [AddOnController::class, 'selectAddon']);

    Route::get('/change-plan/{id}', [PlansController::class, 'changePlan']);
    Route::get('/subscribe-plan/{id}', [PlansController::class, 'subscribePlan']);
    Route::get('/addon-plan/{id}', [PlansController::class, 'addonPlan']);
    Route::get('/multi-addon', [PlansController::class, 'multiAddonPlan']);
    Route::get('/subscribe-custom-plan/{id}/{hostedpageId}/{partnerId}', [PlansController::class, 'subscribeCustomPlan']);
    Route::get('/no-selected-plans', [PartnerController::class, 'noSelectedPlans']);


    Route::get('/subscription', [SubscriptionsController::class, 'getSubscription']);
    Route::get('/subscribe/{id}', [SubscriptionsController::class, 'addSubscription']);
    Route::get('/subscribe-update/{id}', [SubscriptionsController::class, 'updateSubscription']);
    Route::post('/upgrade', [SubscriptionsController::class, 'upgradeSubscription']);
    Route::get('/update-payment-method/{id}', [SubscriptionsController::class, 'updatePaymentMethod']);
    Route::get('/delete-payment-method/{id}', [SubscriptionsController::class, 'deletePaymentMethod']);
    Route::post('/metered-billing', [SubscriptionsController::class, 'updateMeteredBilling'])->name('metered.billing.update');
    Route::get('/create-addon/{id}', [AddOnController::class, 'createAddon']);

    Route::get('/invoices', [InvoicesController::class, 'getInvoice'])->name('partner.invoices');
    Route::post('/record-payment', [InvoicesController::class, 'recordPayment']);

    Route::get('/creditnotes', [CreditNotesController::class, 'getCreditNotes'])->name('partner.creditnotes');
    Route::get('/view-creditnote/{id}', [CreditNotesController::class, 'viewCreditNote']);
    Route::post('/refund-credit', [CreditNotesController::class, 'refund']);


    Route::get('/support', [SupportController::class, 'getSupport'])->name('partner.support');
    Route::post('/email', [SupportController::class, 'downgrade']);
    Route::get('/cancel-email', [SupportController::class, 'cancellation']);
    Route::post('/enterprise-support', [SupportController::class, 'enterpriseSupport']);
    Route::post('/custom-support', [SupportController::class, 'customSupport']);


    Route::get('/provider-info', [ProviderDataController::class, 'getProviderAvailabilityData'])->name('partner.provider-availability-data');
    Route::get('/company-info', [ProviderDataController::class, 'getProviderCompanyInfo'])->name('partner.provider-company-info');
    Route::post('/upload-csv', [ProviderDataController::class, 'uploadAndProcessCSV'])->name('upload.csv');
    Route::post('/save-provider-data', [ProviderDataController::class, 'saveProviderData']);
    Route::post('/update-provider-data', [ProviderDataController::class, 'updateProviderData']);
    Route::post('/send-details', [ProviderDataController::class, 'sendDetailToAdmin']);


    Route::post('/click-limit-notification', [ClicksController::class, 'clicksLimitReminder']);

    Route::get('/clicks-report', [ClicksController::class, 'showClicksReport'])->name('partner.reports');

    Route::get('/reports-export', [ClicksController::class, 'exportClicksReport'])->name('partner.reports.export');
});

// Admin Routes
Route::middleware(['isAdminLoggedIn', 'isAdmin'])->group(function () {

    Route::post('/add-plans', [PlansController::class, 'addPlans']);
    Route::get('/sync-plans', [PlansController::class, 'updatePlans']);
    Route::get('/admin', [AdminController::class, 'getPlans'])->name('admin.plans');
    Route::get('/admin/cpc-plans', [AdminController::class, 'getCPCPlans'])->name('admin.cpc-plans');
    Route::post('/add-addon', [PlansController::class, 'addAddon']);
    Route::get('/admin/subscription', [AdminController::class, 'getSubscriptions'])->name('admin.subscription');

    Route::get('/admin/partner', [AdminController::class, 'getPartner'])->name('admin.partners');
    Route::get('/admin/invite-partner', [AdminController::class, 'getInvitePartner']);
    Route::post('/invite-again', [PartnerController::class, 'invitePartnerAgain'])->name('invite-again');
    Route::post('/invite-partner', [PartnerController::class, 'invitePartner']);
    Route::get('/admin/view-partner/{id}/subscriptions', [PartnerController::class, 'viewPartnerSubscriptions'])->name('view.partner.subscriptions');
    Route::get('/admin/view-partner/{id}/invoices', [PartnerController::class, 'viewPartnerInvoices'])->name('view.partner.invoices');
    Route::get('/admin/view-partner/{id}/creditnotes', [PartnerController::class, 'viewPartnerCreditNotes'])->name('view.partner.creditnotes');
    Route::get('/admin/view-partner/{id}/provider-data', [PartnerController::class, 'viewPartnerProviderData'])->name('view.partner.providerdata');
    Route::get('/admin/view-partner/{id}/clicks-data', [PartnerController::class, 'viewPartnerClicksData'])->name('view.partner.clicksdata');
    Route::get('/admin/view-partner/{id}/export-clicks', [PartnerController::class, 'exportClicksReport'])->name('view.partner.reports.export');
    Route::get('/admin/view-partner/{id}/', [PartnerController::class, 'viewPartnerOverview'])->name('view.partner.overview');
    Route::get('/admin/view-partner/{id}/refunds', [PartnerController::class, 'viewPartnerRefunds'])->name('view.partner.refunds');;
    Route::post('/upload-provider-data', [PartnerController::class, 'uploadProviderData']);
    Route::post('/upload-provider-availability-data', [PartnerController::class, 'uploadProviderAvailabilityData']);
    Route::get('/admin/view-partner/{id}/selected-plans', [PartnerController::class, 'viewPartnerSelectedPlans']);
    Route::post('/add-affiliate-id', [PartnerController::class, 'addAffiliate']);
    Route::post('/add-custom-invoice', [PartnerController::class, 'addCustomInvoice']);
    Route::post('/refund-a-payment', [PartnerController::class, 'refundPayment']);




    Route::post('/remove-affiliate', [PartnerController::class, 'removeAffiliate']);
    Route::post('/update-partner', [PartnerController::class, 'updatePartner']);
    Route::post('/update-subscription', [PartnerController::class, 'updateSubscription']);
    Route::post('/add-creditnote', [PartnerController::class, 'addCreditNote']);
    Route::get('/admin-view-creditnote/{id}', [CreditNotesController::class, 'viewCreditNote']);
    Route::get('/admin/view-partner/disable-partner/{id}', [PartnerController::class, 'disablePartner']);
    Route::get('/admin/view-partner/reactivate-partner/{id}', [PartnerController::class, 'reactivatePartner']);
    Route::get('/admin/view-partner/add-payment-method/{id}', [PartnerController::class, 'associatePaymentMethod']);
    Route::get('/approve-partner/{id}', [PartnerController::class, 'approvePartner']);
    Route::post('/add-selected-plans', [PartnerController::class, 'addSelectedPlans']);

    //User
    Route::post('/admin/invite-user/{id}', [PartnerUsersController::class, 'inviteUser']);
    Route::post('/update-user', [PartnerUsersController::class, 'updatePartnerUser']);
    Route::get('/admin/view-partner/disable-user/{id}', [PartnerUsersController::class, 'disablePartnerUser']);
    Route::get('/admin/view-partner/reactivate-user/{id}', [PartnerUsersController::class, 'reactivatePartnerUser']);
    Route::get('/admin/view-partner/mark-primary/{id}', [PartnerUsersController::class, 'markAsPrimary']);


    //Leads
    Route::get('/admin/leads', [LeadController::class, 'getLeads'])->name('admin.leads');
    Route::get('/admin/approve-lead/{id}', [LeadController::class, 'approveLead']);
    Route::get('/admin/reject-lead/{id}', [LeadController::class, 'rejectLead']);
    Route::get('/admin/view-lead/{id}/', [LeadController::class, 'viewLeadOverview'])->name('view.lead.overview');
    Route::get('/admin/view-lead/{id}/provider-data', [LeadController::class, 'viewLeadProviderData'])->name('view.lead.providerdata');


    //Affiliates
    Route::get('/admin/affiliates', [AffiliateController::class, 'getAffiliates'])->name('admin.affiliates');
    Route::post('/add-affiliate', [AffiliateController::class, 'addAffiliate']);
    Route::post('/edit-affiliate', [AffiliateController::class, 'editAffiliate']);
    Route::get('/delete-affiliate/{id}', [AffiliateController::class, 'deleteAffiliate']);

    //PartnerAffiliates
    Route::get('/admin/partners-affiliates-edit', [PartnersAffiliatesController::class, 'edit'])->name('partnersAffiliates.edit');
    Route::post('/admin/partners-affiliates-edit', [PartnersAffiliatesController::class, 'update'])->name('partnersAffiliates.update');


    // Admin Support
    Route::get('/admin/support', [AdminController::class, 'getSupport'])->name('admin.supports');
    Route::post('/revoke-support', [SupportController::class, 'revokeSupport']);
    Route::post('/support', [AdminController::class, 'updateSupport']);

    // Admin Invoices
    Route::get('/admin/invoice', [AdminController::class, 'getInvoices'])->name('admin.invoice');
    Route::get('/admin/invoice/unpaid', [AdminController::class, 'getUnpaidInvoices'])->name('admin.unpaid-invoice');

    Route::post('/record-payment', [InvoicesController::class, 'recordPayment']);

    //Admin Plans
    Route::post('/update-plan-price', [PlanFeaturesController::class, 'updatePlanPrice']);
    Route::post('/update-addon-price', [PlanFeaturesController::class, 'updateAddonPrice']);

    // Admin Terms
    Route::get('/admin/terms', [AdminController::class, 'getTerms'])->name('admin.terms');

    //Clicks Email 
    Route::get('/admin/clicks-email', [AdminController::class, 'getClicksEmailLog'])->name('admin.clicks-email-log');

    // Admin Profile
    Route::get('/admin/profile', [AdminController::class, 'getProfile'])->name('admin.profile');
    Route::post('/admin/update-password', [AdminController::class, 'updatePassword']);

    // Plan Features
    Route::get('/admin/plan-features/{plan_code}', [PlanFeaturesController::class, 'showUpdateForm'])->name('admin.planfeatures.show');
    Route::post('/admin/plan-features/update', [PlanFeaturesController::class, 'update'])->name('admin.planfeatures.update');
    Route::post('/admin/plan-features/create', [PlanFeaturesController::class, 'create'])->name('admin.planfeatures.create');

    // Partner Users
    Route::get('/partner-users', [PartnerUsersController::class, 'index'])->name('admin.partnerusers.index');
    Route::post('/partner-users', [PartnerUsersController::class, 'store'])->name('admin.partnerusers.store');
    Route::post('/partner-users/{partnerUser}', [PartnerUsersController::class, 'update'])->name('admin.partnerusers.update');
    Route::delete('/partner-users/{partnerUser}', [PartnerUsersController::class, 'destroy'])->name('admin.partnerusers.destroy');

    // Clicks
    Route::get('/clicks', [ClicksController::class, 'index'])->name('admin.clicks.index');

    Route::get('/admin/partner-clicks', [PartnerClicksController::class, 'showPartnerClicks'])->name('admin.all-partner-clicks');

    Route::get('/admin/run-scheduled-task', [ClicksEmailAlertController::class, 'runScheduledTask']);


    Route::post('/affiliate/remove', [AffiliateController::class, 'removeAffiliate'])->name('affiliate.remove');



    // Webhook
    Route::get('/admin/webhook', [WebhookController::class, 'getWebhook'])->name('admin.webhook');

    Route::post('/webhook', [WebhookController::class, 'updateWebhook']);

    // Deletion (Staging Only)
    if (env('APP_ENV') === 'staging') {

        Route::get('/delete-records', [DeleteRecordsController::class, 'deleteRecords']);
    }
});

Route::middleware(['isLoggedIn'])->group(function () {

    Route::get('/thankyou-create', [HostedPageController::class, 'thankyouCreate']);
    Route::get('/thankyou-update', [HostedPageController::class, 'thankyouUpdate']);
    Route::get('/thankyou-downgrade', [HostedPageController::class, 'thankyouDowngrade']);
    Route::get('/add-payment-method', [HostedPageController::class, 'addPaymentMethod']);
    Route::get('/cancel-subscription/{id}', [SubscriptionsController::class, 'cancelSubscription']);
    Route::post('/create-subscription', [SubscriptionsController::class, 'createSubscription']);
    Route::post('/upgrade', [SubscriptionsController::class, 'upgradeSubscription']);
});

// Super Admin Routes
Route::middleware(['isAdminLoggedIn', 'isSuperAdmin'])->group(function () {

    Route::get('/admin/admins', [SuperAdminController::class, 'getAdmins'])->name('admin.admins');
    Route::get('/admin/api-clients', [ApiController::class, 'index'])->name('api-client.index');
    Route::post('/oauth/create-client', [ApiController::class, 'store'])->name('api-client.store');
    Route::delete('/oauth/revoke-client/{id}', [ApiController::class, 'revoke'])->name('api-client.revoke');

    Route::post('/invite-admin', [SuperAdminController::class, 'inviteAdmin']);

    Route::post('/update-admin', [SuperAdminController::class, 'updateAdmin']);

    Route::get('/delete-admin/{id}', [SuperAdminController::class, 'deleteAdmin']);
});

Route::get('/incorrect-partner-user', [UserAuthController::class, 'incorrectPartnerUser'])->name('incorrect.partner.user');

Route::get('/incorrect-admin-user', [UserAuthController::class, 'incorrectAdminUser'])->name('incorrect.admin.user');

Route::get('/incorrect-superadmin-user', [UserAuthController::class, 'incorrectSuperAdminUser'])->name('incorrect.superadmin.user');

Route::get('/terms-conditions', [InvoicesController::class, 'termsConditions']);

Route::post('/download-presigned-url', [ProviderDataController::class, 'downloadPresignedUrl'])->name('download.presigned.url');




//WEBHOOK
Route::post('/webhook/subscription', [WebhookController::class, 'handleSubscription']);

Route::post('/webhook/invoice', [WebhookController::class, 'handleInvoice']);

Route::post('/webhook/credit-note', [WebhookController::class, 'handleCreditNote']);

Route::post('/webhook/payment-method', [WebhookController::class, 'handlePaymentMethod']);

Route::post('/webhook/payment-thankyou', [WebhookController::class, 'handlePaymentThankyou']);

Route::post('/webhook/refund', [WebhookController::class, 'handleRefund']);
