<?php
Route::get('admin/logout', 'Backend\Auth\AuthController@getLogout');
Route::group(
    [
        'middleware' => [ 'localizationRedirect', 'localeSessionRedirect']
    ], 
    function(){ 
        Route::get('admin', [ 'middleware' => ['is.logged.as.admin'], 'uses' => 'Backend\Auth\AuthController@getLogin' ]);  

    }
);


Route::resource('admin/report', 'Backend\ReportController');
Route::get('admin/report/month/{year}/{month}', 'Backend\ReportController@getByMonth');
Route::get('admin/report/{start}/{end}', 'Backend\ReportController@getByDate');


foreach (LaravelLocalization::getSupportedLanguagesKeys() as $lang) {
    LaravelLocalization::setLocale($lang);
    Route::group([
        'prefix' => $lang,  
        'middleware' => ['setLocale', 'localizationRedirect', 'localeSessionRedirect']
    ], function () use ( $lang ){



        Route::get('admin', 'Backend\Auth\AuthController@getLogin');
        Route::post('admin', 'Backend\Auth\AuthController@postLogin');

        Route::group(['prefix' => 'admin', 'middleware' => 'auth.admin'], function() {

            Route::get('autocomplete/clients', ['uses' => 'Backend\UserController@clients']);      
            Route::get('autocomplete/cfpclient-clients', ['uses' => 'Backend\CfpClientController@clients']);
            Route::get('autocomplete/cfps', ['uses' => 'Backend\UserController@cfps']);
            Route::get('autocomplete/cfpclient-cfps', ['uses' => 'Backend\CfpClientController@cfps']);
            Route::get('cfp/schedule/available-time-slot', ['uses' => 'Backend\CfpScheduleController@availableTimeSlot']);

            Route::get('profile', array('as'   => 'admin.profile', 'uses' => 'Backend\Auth\AuthController@myProfile'));
            Route::post('profile/update', array('as'   => 'admin.profile.update', 'uses' => 'Backend\Auth\AuthController@myProfileStore'));

            // filemanager
            Route::get('filemanager/show', ['uses' => 'Backend\FileManagerController@index']);

            //Admin Dashboard
            Route::get('dashboard', [ 'as' => 'admin.dashboard', 'uses' => 'Backend\DashboardController@index' ]);

            Route::get('article/filter', [ 'as' => 'admin.article.filter', 'uses' => 'Backend\ArticleController@index']);
            Route::post('article/filter', [ 'as' => 'admin.article.filter', 'uses' => 'Backend\ArticleController@index']);
            Route::resource('article', 'Backend\ArticleController');
            Route::get('article/{id}/delete', array('as'   => 'admin.article.delete',
                                                    'uses' => 'Backend\ArticleController@confirmDestroy'))->where('id', '\d+');
            Route::post('article/{id}/toggle-publish', array('as'   => 'admin.article.toggle-publish',
                                                             'uses' => 'Backend\ArticleController@togglePublish'))->where('id', '[0-9]+');

            //investment information
            Route::get('investment-information/filter', [ 'as' => 'admin.investment-information.filter', 'uses' => 'Backend\InvestmentInformationController@index']);
            Route::post('investment-information/filter', [ 'as' => 'admin.investment-information.filter', 'uses' => 'Backend\InvestmentInformationController@index']);
            Route::resource('investment-information', 'Backend\InvestmentInformationController');
            Route::get('investment-information/{id}/delete', array('as'   => 'admin.investment-information.delete',
                                                    'uses' => 'Backend\InvestmentInformationController@confirmDestroy'))->where('id', '\d+');

            //taxonomy
            Route::post('taxonomy/store', [
                    'as'   => 'admin.taxonomy.store',
                    'uses' => 'Backend\TaxonomyController@store'
                ]);

            Route::post('taxonomy/{post_type}/save', [
                    'as' => 'admin.taxonomy.save', 
                    'uses' => 'Backend\TaxonomyController@save'
                ]);

            Route::get('taxonomy/{post_type}/create', [
                    'as'   => 'admin.taxonomy.create',
                    'uses' => 'Backend\TaxonomyController@create'
                ]);

            Route::get('taxonomy/{post_type}/{id}/show', [
                    'as'   => 'admin.taxonomy.show',
                    'uses' => 'Backend\TaxonomyController@show'
                ])->where('id', '[0-9]+');
            
            Route::get('taxonomy/{post_type}/{id}/edit', [
                    'as'   => 'admin.taxonomy.edit',
                    'uses' => 'Backend\TaxonomyController@edit'
                ])->where('id', '[0-9]+');

            Route::patch('taxonomy/{id}', [
                    'as'   => 'admin.taxonomy.update',
                    'uses' => 'Backend\TaxonomyController@update'
                ])->where('id', '[0-9]+');

            Route::get('taxonomy/{post_type}/{id}/delete', [
                    'as'   => 'admin.taxonomy.delete',
                    'uses' => 'Backend\TaxonomyController@confirmDestroy'
                ])->where('id', '[0-9]+');

            Route::delete('taxonomy/{post_type}/{id}', [
                    'as'   => 'admin.taxonomy.destroy',
                    'uses' => 'Backend\TaxonomyController@destroy'
                ])->where('id', '[0-9]+');

            Route::get('taxonomy/{post_type}', [
                    'as'   => 'admin.taxonomy.index',
                    'uses' => 'Backend\TaxonomyController@index'
                ]);

            //user role
            Route::resource('user/role', 'Backend\RoleController');
            Route::get('user/role/{id}/delete', array('as'   => 'admin.user.role.delete',
                                                 'uses' => 'Backend\RoleController@confirmDestroy'))->where('id', '[0-9]+');

            //user permission
            Route::resource('user/permission', 'Backend\PermissionController');
            Route::get('user/permission/{id}/delete', array('as'   => 'admin.user.permission.delete',
                                                 'uses' => 'Backend\PermissionController@confirmDestroy'))->where('id', '[0-9]+');

            //user goals
            Route::get('user/{id}/goals', array('as'   => 'admin.user.goals',
                                                 'uses' => 'Backend\UserController@goalsByUser'))->where('id', '[0-9]+');

            // user
            Route::get('user/filter', array('as' => 'admin.user.filter',
                                                'uses' => 'Backend\UserController@index'));
            Route::post('user/filter', array('as' => 'admin.user.filter',
                                                'uses' => 'Backend\UserController@index'));
            Route::resource('user', 'Backend\UserController');
            Route::get('user/{id}/delete', array('as'   => 'admin.user.delete',
                                                 'uses' => 'Backend\UserController@confirmDestroy'))->where('id', '[0-9]+');
            Route::get('user/{id}/edit/password', array('as'   => 'admin.user.edit.password',
                                                 'uses' => 'Backend\UserController@editPassword'))->where('id', '[0-9]+');
            Route::post('user/{id}/edit/password/update', array('as'   => 'admin.user.edit.password.update',
                                                 'uses' => 'Backend\UserController@updatePassword'))->where('id', '[0-9]+');
            Route::get('user/{id}/edit/tourguide', array('as'   => 'admin.user.edit.tourguide',
                                                 'uses' => 'Backend\UserController@editTourguide'))->where('id', '[0-9]+');
            Route::get('user/{id}/generate-code', array('as'   => 'admin.user.generate-code',
                                                 'uses' => 'Backend\UserController@generateCode'))->where('id', '[0-9]+');
            Route::get('user/{id}/history-financial-checkup', array('as'   => 'admin.user.history-financial-checkup',
                                                 'uses' => 'Backend\UserController@historyFinancialCheckup'));
            Route::get('user/{id}/cashflow-analysis/{version}', array('as'   => 'admin.user.cashflow-analysis.show',
                                                 'uses' => 'Backend\UserController@showCashflowAnalysis'));
            Route::get('user/{id}/portfolio-analysis/{version}', array('as'   => 'admin.user.portfolio-analysis.show',
                                                 'uses' => 'Backend\UserController@showPortfolioAnalysis'));
            Route::get('user/{id}/plan-analysis/{version}', array('as'   => 'admin.user.plan-analysis.show',
                                                 'uses' => 'Backend\UserController@showPlanAnalysis'));
            
            // setting general
            Route::get('settings/general', [ 'as' => 'admin.settings.general', 'uses' => 'Backend\SettingController@general' ]);
            Route::post('settings/general', [ 'as' => 'admin.settings.general.store', 'uses' => 'Backend\SettingController@generalStore' ]);

            // setting reading
            Route::get('settings/reading', [ 'as' => 'admin.settings.reading', 'uses' => 'Backend\SettingController@reading' ]);
            Route::post('settings/reading', [ 'as' => 'admin.settings.reading.store', 'uses' => 'Backend\SettingController@readingStore' ]);

            // setting notification
            Route::get('settings/notification', [ 'as' => 'admin.settings.notification', 'uses' => 'Backend\SettingController@notification' ]);
            Route::post('settings/notification', [ 'as' => 'admin.settings.notification.store', 'uses' => 'Backend\SettingController@notificationStore' ]);    

            // setting finance
            Route::get('settings/finance', [ 'as' => 'admin.settings.finance', 'uses' => 'Backend\SettingController@finance' ]);
            Route::post('settings/finance', [ 'as' => 'admin.settings.finance.store', 'uses' => 'Backend\SettingController@financeStore' ]);

            // setting investment
            Route::get('settings/finance/investment', [ 'as' => 'admin.settings.investment', 'uses' => 'Backend\SettingController@investment' ]);
            Route::post('settings/finance/investment', [ 'as' => 'admin.settings.investment.store', 'uses' => 'Backend\SettingController@investmentStore' ]);

            // setting insurance
            Route::get('settings/finance/insurance', [ 'as' => 'admin.settings.insurance', 'uses' => 'Backend\SettingController@insurance' ]);
            Route::post('settings/finance/insurance', [ 'as' => 'admin.settings.insurance.store', 'uses' => 'Backend\SettingController@insuranceStore' ]);

            // setting inflation
            Route::get('settings/finance/inflation', [ 'as' => 'admin.settings.inflation', 'uses' => 'Backend\SettingController@inflation' ]);
            Route::post('settings/finance/inflation', [ 'as' => 'admin.settings.inflation.store', 'uses' => 'Backend\SettingController@inflationStore' ]);

            // setting cfp
            Route::get('settings/cfp', [ 'as' => 'admin.settings.cfp', 'uses' => 'Backend\SettingController@cfp' ]);
            Route::post('settings/cfp', [ 'as' => 'admin.settings.cfp.store', 'uses' => 'Backend\SettingController@cfpStore' ]);

            // setting commerce
            Route::get('settings/commerce', [ 'as' => 'admin.settings.commerce', 'uses' => 'Backend\SettingController@commerce' ]);
            Route::post('settings/commerce', [ 'as' => 'admin.settings.commerce.store', 'uses' => 'Backend\SettingController@commerceStore' ]);

            // setting subscription
            Route::get('settings/subscription', [ 'as' => 'admin.settings.subscription', 'uses' => 'Backend\SettingController@subscription' ]);
            Route::post('settings/subscription', [ 'as' => 'admin.settings.subscription.store', 'uses' => 'Backend\SettingController@subscriptionStore' ]);

            // setting wallet
            Route::get('settings/wallet', [ 'as' => 'admin.settings.wallet', 'uses' => 'Backend\SettingController@wallet' ]);
            Route::post('settings/wallet', [ 'as' => 'admin.settings.wallet.store', 'uses' => 'Backend\SettingController@walletStore' ]);

            // setting weekend days
            Route::get('settings/weekend-days', [ 'as' => 'admin.settings.weekend-days', 'uses' => 'Backend\SettingController@weekendDays' ]);
            Route::post('settings/weekend-days', [ 'as' => 'admin.settings.weekend-days.store', 'uses' => 'Backend\SettingController@weekendDaysStore' ]);

            Route::get('settings/weekend-days/inject/{from}/{to}', [ 'as' => 'admin.settings.weekend-days.inject', 'uses' => 'Backend\SettingController@weekendDays_injectWeekend' ]);

            //email template
            Route::resource('settings/email-template', 'Backend\EmailTemplateController');
            Route::get('settings/email-template/{id}/delete', array('as'   => 'admin.settings.email-template.delete',
                                                    'uses' => 'Backend\EmailTemplateController@confirmDestroy'))->where('id', '\d+');
            Route::post('settings/email-template/{id}/toggle-publish', array('as'   => 'admin.settings.email-template.toggle-publish',
                                                             'uses' => 'Backend\EmailTemplateController@togglePublish'))->where('id', '[0-9]+');

            //email mapping
            Route::get('settings/email-mapping', [ 'as' => 'admin.settings.email-mapping', 'uses' => 'Backend\EmailTemplateMappingController@create' ]);
            Route::post('settings/email-mapping', [ 'as' => 'admin.settings.email-mapping.store', 'uses' => 'Backend\EmailTemplateMappingController@store' ]);

            // setting interest rates
            Route::resource('settings/finance/interest-rate', 'Backend\InterestRateController');
            Route::get('settings/finance/interest-rate/{id}/delete', array('as'   => 'admin.settings.finance.interest-rate.delete',
                                                    'uses' => 'Backend\InterestRateController@confirmDestroy'))->where('id', '\d+');

            // setting actual interest rates
            Route::resource('settings/finance/actual-interest-rate', 'Backend\ActualInterestRateController');
            Route::get('settings/finance/actual-interest-rate/{id}/delete', array('as'   => 'admin.settings.finance.actual-interest-rate.delete',
                                                    'uses' => 'Backend\ActualInterestRateController@confirmDestroy'))->where('id', '\d+');

            // setting plan anaylsis - triangle
            Route::resource('settings/plan-analysis/triangle', 'Backend\TriangleController');
            Route::get('settings/plan-analysis/triangle/{id}/delete', array('as'   => 'admin.settings.plan-analysis.triangle.delete',
                                                    'uses' => 'Backend\TriangleController@confirmDestroy'));
            Route::get('asset-repayment-categories', array('as'   => 'admin.asset-repayment-categories',
                                                    'uses' => 'Backend\TaxonomyController@getListAssetRepaymentCategories'));//sementara route nya pakai ini

            //triangle layer                                        
            Route::resource('settings/plan-analysis/triangle-layer', 'Backend\TriangleLayerController');
            Route::get('settings/plan-analysis/triangle-layer/{id}/delete', array('as'   => 'admin.settings.plan-analysis.triangle-layer.delete',
                                                    'uses' => 'Backend\TriangleLayerController@confirmDestroy'));

            // Category Code
            //Route::resource('category/code', 'Backend\CategoryCodeController');
            //Route::get('category/code/{id}/delete', array('as'   => 'admin.category.code.delete', 'uses' => 'Backend\CategoryCodeController@confirmDestroy'))->where('id', '\d+');

            // Portofolio
            Route::resource('portofolio', 'Backend\PortofolioController');
            Route::get('portofolio/{id}/delete', array('as'   => 'admin.portofolio.delete',
                                                    'uses' => 'Backend\PortofolioController@confirmDestroy'))->where('id', '\d+');
            // Portofolio detail
            Route::resource('detail/portofolio', 'Backend\PortofolioDetailController');
            Route::get('detail/portofolio/{id}/delete', array('as'   => 'admin.detail.portofolio.delete',
                                                    'uses' => 'Backend\PortofolioDetailController@confirmDestroy'))->where('id', '\d+');

            // Company
            //Route::resource('company', 'Backend\CompanyController');
            //Route::get('company/{id}/delete', array('as'   => 'admin.company.delete', 'uses' => 'Backend\CompanyController@confirmDestroy'))->where('id', '\d+');

            // Cfp Client
            Route::get('cfp/client/filter', array('as' => 'admin.cfp.client.filter',
                                                'uses' => 'Backend\CfpClientController@index'));
            Route::post('cfp/client/filter', array('as' => 'admin.cfp.client.filter',
                                                'uses' => 'Backend\CfpClientController@index'));
            Route::resource('cfp/client', 'Backend\CfpClientController');
            Route::get('cfp/client/{id}/delete', array('as'   => 'admin.cfp.client.delete',
                                                    'uses' => 'Backend\CfpClientController@confirmDestroy'))->where('id', '\d+');

            // Cfp Schedule
            Route::get('cfp/schedule/filter', array('as' => 'admin.cfp.schedule.filter',
                                                'uses' => 'Backend\CfpScheduleController@index'));
            Route::post('cfp/schedule/filter', array('as' => 'admin.cfp.schedule.filter',
                                                'uses' => 'Backend\CfpScheduleController@index'));
            Route::resource('cfp/schedule', 'Backend\CfpScheduleController');
            Route::get('cfp/schedule/{id}/delete', array('as'   => 'admin.cfp.schedule.delete',
                                                    'uses' => 'Backend\CfpScheduleController@confirmDestroy'))->where('id', '\d+');

            // CFP Schedule Cut Off
            Route::get('cfp/schedule_dayoff', array('as' => 'admin.cfp.schedule.dayoff',
            'uses' => 'Backend\CfpScheduleController@cfpScheduleDayOff'));
            Route::get('cfp/schedule_dayoff/create', array('as' => 'admin.cfp.schedule.dayoff.create',
            'uses' => 'Backend\CfpScheduleController@cfpScheduleDayOffCreate'));
            Route::post('cfp/schedule_dayoff/create', array('as' => 'admin.cfp.schedule.dayoff.store',
            'uses' => 'Backend\CfpScheduleController@cfpScheduleDayOffStore'));
            Route::get('cfp/schedule_dayoff/{id}/edit', [
                'as'   => 'admin.cfp.schedule.dayoff.edit',
                'uses' => 'Backend\CfpScheduleController@cfpScheduleDayOffEdit'
            ])->where('id', '[0-9]+');
            Route::get('cfp/schedule_dayoff/{id}/delete', array('as'   => 'admin.cfp.schedule.dayoff.delete',
                                                    'uses' => 'Backend\CfpScheduleController@cfpScheduleDayOffConfirmDelete'))->where('id', '\d+');
            Route::delete('cfp/schedule_dayoff/{id}', ['as'   => 'admin.cfp.schedule.dayoff.destroy', 'uses' => 'Backend\CfpScheduleController@cfpScheduleDayOffDelete'])->where('id', '[0-9]+');
            Route::patch('cfp/schedule_dayoff_update/{id}', ['as'   => 'admin.cfp.schedule.dayoff.update', 'uses' => 'Backend\CfpScheduleController@cfpScheduleDayOffUpdate'])->where('id', '[0-9]+');
            Route::get('cfp/schedule_dayoff_show/{id}', ['as'   => 'admin.cfp.schedule.dayoff.show', 'uses' => 'Backend\CfpScheduleController@cfpScheduleDayOffShow'])->where('id', '[0-9]+');

            //simulasi
            Route::get('simulasi', [
                'as'   => 'admin.simulasi',
                'uses' => 'Backend\ToolController@simulasi'
            ]);

            Route::get('simulasi-php', [
                'as'   => 'admin.simulasi-php',
                'uses' => 'Backend\ToolController@simulasiPhp'
            ]);

            //Bank Master List
            Route::resource('bank', 'Backend\BankController');
            Route::get('bank/{id}/confirm_delete', array('as'   => 'admin.bank.confirm.delete',
                                                    'uses' => 'Backend\BankController@confirmDestroy'))->where('id', '\d+');

            // Grade
            Route::resource('grade', 'Backend\GradeController');
            Route::get('grade/{id}/delete', array('as'   => 'admin.grade.delete',
                                                    'uses' => 'Backend\GradeController@confirmDestroy'))->where('id', '\d+');

            // Grade
            Route::resource('reference', 'Backend\ReferenceController');
            Route::get('reference/{id}/delete', array('as'   => 'admin.reference.delete',
                                                    'uses' => 'Backend\ReferenceController@confirmDestroy'))->where('id', '\d+');

            // Goal
            Route::resource('goal', 'Backend\GoalController');
            Route::get('goal/{id}/delete', array('as'   => 'admin.goal.delete',
                                                    'uses' => 'Backend\GoalController@confirmDestroy'))->where('id', '\d+');

            Route::resource('payments', 'Backend\PaymentController');
        });
    });
}