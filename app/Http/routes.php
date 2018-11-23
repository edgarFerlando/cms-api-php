<?php
//home sementara ditiadakan karena belum ada frontend
Route::get('/', ['as' => 'dashboard', 'uses' => 'HomeController@index']);


/*
|--------------------------------------------------------------------------
| Login facebook, Payment, Top 3 dan Anggrana bulanan
|--------------------------------------------------------------------------
*/

Route::post('api/v1/client/registerfacebook', 'API\ClientController@createOrGetUser');

Route::post('api/v1/user/set_activate', 
    [ 
        'as' => 'user.set_activate', 
        'uses' => 'API\ClientController@setActive' 
    ]
);

Route::resource('api/v1/payment', 'API\PaymentController');
Route::get('api/v1/payment/index', 'API\PaymentController@index');
Route::post('api/v1/payment/store_bca_va/{id}', 'API\PaymentController@storeBcaVa');
Route::post('api/v1/payment/verify_bca_va', 'API\PaymentController@verifyBcaVa');

Route::get('api/v1/payment/store_creditcard/{id}', 'API\PaymentController@storeCreditcard');
Route::post('api/v1/payment/verify_creditcard', 'API\PaymentController@verifyCreditcard');
Route::get('payment/callback_creditcard/{id}', 'API\PaymentController@callbackCreditcard');

Route::post('api/v1/bank-statements/percentage-rencana-bulanan', 'API\BankStatementController@percentageRencanaBulanan');
// --------------------------------------------------------------------------


Route::get('api/v1/time', 'API\BankStatementV2Controller@getTime');


Route::post('api/v1/user/get-token', 'API\ClientController@authenticate');
Route::get('client/reset-password/{token}', ['as' => 'reset-password', 'uses' => 'Auth\PasswordController@getReset']);
Route::post('client/reset-password', [ 'as' => 'do-reset-password', 'uses' => 'Auth\PasswordController@postReset' ]);
// Route::get('activation-page', [ 'as' => 'activation-page', 'uses' => 'API\ClientController@activation_page' ]);
Route::get('email/financial-health-checkup/{email_web_path}', ['as' => 'email-financial-health-checkup', 'uses' => 'API\FinanceController@financialHealthCheckupView']);
Route::get('email/plan-analysis/{email_web_path}', ['as' => 'email-plan-analysis', 'uses' => 'API\PlanController@planAnalysisView']);

//login api
Route::post('api/v1/client/login', [ 'as' => 'api.v1.client.login', 'uses' => 'API\ClientController@doLogin'] );
Route::post('api/v1/cfp/login', [ 'as' => 'api.v1.cfp.login', 'uses' => 'API\CfpController@doLogin' ]);

//register
Route::post('api/v1/client/register', 'API\ClientController@doRegister');

Route::post('api/v1/client/forgot-password', [ 'uses' => 'API\ClientController@forgotPassword'] );

//national day
Route::get('api/v1/national-day-indonesia', ['uses' => 'API\CfpScheduleController@nationalDayIndonesia']);

Route::post('api/v1/support', ['uses' => 'API\SupportController@support']);

Route::group(array('prefix' => 'api/v1', 'middleware' => ['jwt.auth', 'log']), function ($request) {
            //Route::group(array('prefix' => 'api/v1'), function($request) {
                //client
    Route::post('client/masukan', ['as' => 'api.v1.client.masukan', 'uses' => 'API\SupportController@masukan']);
    Route::post('client/schedules', ['as' => 'api.v1.client.schedules', 'uses' => 'API\CfpScheduleController@clientSchedules']);
    Route::post('client/portfolio', 'API\ClientController@portfolio');
    Route::get('client/cfpList', 'API\ClientController@getCfpList');
    Route::post('client/change-cfp', 'API\ClientController@changeCfp');

    //CFP detail
    Route::post('cfp/detail', [ 'as' => 'api.v1.cfp.detail', 'uses' => 'API\CfpController@cfpDetail' ]);


    //cutoff client
    Route::post('client/cutoffdate', ['as' => 'api.v1.client.cutoffdate', 'uses' => 'API\BankStatementController@cutoffDateClient']);

    Route::post('client/cutoffdate/update', ['as' => 'api.v1.client.cutoffdate', 'uses' => 'API\BankStatementController@cutoffDateClientUpdate']);

    Route::post('my-account/update', 'API\ClientController@myAccountUpdate');
            
                //progress bar
    Route::post('progress-bar', ['as' => 'api.v1.progress-bar', 'uses' => 'API\ClientController@progressBar']); 
            
                //schedule
    Route::post('cfp/schedule/store', ['as' => 'api.v1.cfp.schedule.store', 'uses' => 'API\CfpScheduleController@store']);
    Route::post('cfp/schedule/update', ['as' => 'api.v1.cfp.schedule.update', 'uses' => 'API\CfpScheduleController@update']);
    Route::post('cfp/schedule/destroy', ['as' => 'api.v1.cfp.schedule.destroy', 'uses' => 'API\CfpScheduleController@destroy']);
    Route::post('cfp/schedule/available-time-slot', ['as' => 'api.v1.cfp.schedule.available-time-slot', 'uses' => 'API\CfpScheduleController@availableTimeSlot']);
    Route::post('cfp/schedules', ['as' => 'api.v1.cfp.schedules', 'uses' => 'API\CfpScheduleController@schedules']);
            
                //cfp
    Route::post('cfp/clients', ['as' => 'api.v1.cfp.clients.destroy', 'uses' => 'API\CfpController@cfpClients']);
            
                //wallet
    Route::post('wallet/transaction/store', ['as' => 'api.v1.wallet.transaction.store', 'uses' => 'API\WalletTransactionController@store']);
    Route::post('wallet/transaction/update', ['as' => 'api.v1.wallet.transaction.update', 'uses' => 'API\WalletTransactionController@update']);
    Route::post('wallet/transaction/destroy', ['as' => 'api.v1.wallet.transaction.destroy', 'uses' => 'API\WalletTransactionController@destroy']);
    Route::post('wallet/transaction/balance', ['as' => 'api.v1.wallet.transaction.balance', 'uses' => 'API\WalletTransactionController@balance']);
    Route::post('wallet/transactions', ['as' => 'api.v1.wallet.transactions', 'uses' => 'API\WalletTransactionController@transactions']);
            
                //smart-wallet
    Route::post('smart-wallet/bank-account/store', ['as' => 'api.v1.smart-wallet.bank-account.store', 'uses' => 'API\BankAccountController@store']);
    Route::post('smart-wallet/bank-account/update', ['as' => 'api.v1.smart-wallet.bank-account.store', 'uses' => 'API\BankAccountController@update']);
    Route::post('smart-wallet/bank-account/destroy', ['as' => 'api.v1.smart-wallet.bank-account.destroy', 'uses' => 'API\BankAccountController@destroy']);
    Route::post('smart-wallet/bank-accounts', ['as' => 'api.v1.smart-wallet.bank-accounts', 'uses' => 'API\BankAccountController@index']);
    
    Route::post('smart-wallet/bank-accounts/detail', ['as' => 'api.v1.smart-wallet.bank-accounts-detail', 'uses' => 'API\BankStatementController@bankAccountsDetail']);

    //top-three
    Route::post('bank-statements/top-three-highest', 'API\BankStatementController@topThreeHighest');
    Route::post('bank-statements/top-three-active', 'API\BankStatementController@topThreeActive');
    Route::post('bank-statements/top-three-highest-v2', 'API\BankStatementV2Controller@topThreeHighestV2');
    Route::post('bank-statements/top-three-active-v2', 'API\BankStatementV2Controller@topThreeActiveV2');

    //anggaran bulanan V2
    Route::post('bank-statements/anggaran-bulanan-v2', 'API\BankStatementV2Controller@anggaranBulananV2');



    //split category
    Route::post('bank-statements/split-category/store', 'API\BankStatementController@splitExpenseCategoryStore');
    Route::post('bank-statements/split-category/update', 'API\BankStatementController@splitExpenseCategoryUpdate');
    Route::post('bank-statements/split-category/reset', 'API\BankStatementController@splitExpenseCategoryReset');
    Route::post('smart-wallet/expenses/set-category-v2', ['as' => 'api.v1.smart-wallet.expenses.set-category', 'uses' => 'API\BankStatementController@expensesSetCategoryV2']);
    
                //bisa dipakai untuk 3.1 dan 3.5
    Route::post('smart-wallet/bank-statements', ['as' => 'api.v1.smart-wallet.bank-statements', 'uses' => 'API\BankStatementController@bankStatements']);    
                //Route::post('smart-wallet/expenses/uncategorized', ['as' => 'api.v1.smart-wallet.expenses.uncategorized', 'uses' => 'API\BankStatementController@expensesUncategorized']);
    Route::post('smart-wallet/expenses/set-category', ['as' => 'api.v1.smart-wallet.expenses.set-category', 'uses' => 'API\BankStatementController@expensesSetCategory']);
    Route::post('smart-wallet/monitor/monthly', ['as' => 'api.v1.smart-wallet.monitor.monthly', 'uses' => 'API\BankStatementController@monitorMonthly']);
    Route::post('smart-wallet/expenses/monthly', ['as' => 'api.v1.smart-wallet.expenses.monthly', 'uses' => 'API\BankStatementController@expensesMonthly']);
    Route::post('smart-wallet/expenses/monthly-v2', ['as' => 'api.v1.smart-wallet.expenses.monthly', 'uses' => 'API\BankStatementController@expensesMonthlyV2']);
    Route::post('smart-wallet/detail-pengeluaran', ['as' => 'api.v1.smart-wallet.detail.pengeluaran', 'uses' => 'API\BankStatementV2Controller@detailPengeluaran']);

    //pendapatan list
    Route::post('smart-wallet/pendapatan/monthly', ['as' => 'api.v1.smart-wallet.pendapatan.monthly', 'uses' => 'API\BankStatementController@pendapatanList']);
            
                //saldo rekening,pemasukan dan pengeluaran
    Route::post('smart-wallet/transaction-account', ['as' => 'api.v1.smart-wallet.transaction-account', 'uses' => 'API\BankStatementController@transactionAccount']);
    Route::post('smart-wallet/transaction-account-v2', ['as' => 'api.v1.smart-wallet.transaction-account', 'uses' => 'API\BankStatementController@transactionAccountV2']);

    //bank-list
    Route::get('bank-list', ['as' => 'api.v1.bank', 'uses' => 'API\BankStatementController@bankList']);
            
                //plan simulation
    Route::post('plan-simulation', ['as' => 'api.v1.plan-simulation', 'uses' => 'API\PlanController@planSimulation']);
            
                //finance
    Route::post('dept-repayment-categories', ['as' => 'api.v1.dept-repayment-categories', 'uses' => 'API\FinanceController@getDebtRepaymentCategories']);
    Route::post('asset-repayment-categories', ['as' => 'api.v1.asset-repayment-categories', 'uses' => 'API\FinanceController@getAssetRepaymentCategories']);
    Route::post('insurance-names', ['as' => 'api.v1.insurance-names', 'uses' => 'API\FinanceController@getInsuranceNames']);
    Route::post('insurance-types', ['as' => 'api.v1.insurance-types', 'uses' => 'API\FinanceController@getInsuranceTypes']); 
    Route::get('incomes', ['as' => 'api.v1.incomes', 'uses' => 'API\FinanceController@getIncomes']);
                
                //taxonomy
    Route::post('wallet-categories', ['as' => 'api.v1.wallet-categories', 'uses' => 'API\FinanceController@getWalletCategories']);
    Route::post('branches', ['as' => 'api.v1.branches', 'uses' => 'API\UserController@getBranches']);
            





    // Cashflow analysis

    /**
     * Financila Checkup CFP ...
     */

    Route::post('financial-checkup/cashflow-analysis/store', 
        [
            'as' => 'api.v1.financial-checkup.cashflow-analysis.store', 
            'uses' => 'API\FinanceController@storeCashflowAnalysis'
        ]
    );
    
    Route::post('financial-checkup/cashflow-analysis/show', 
        [
            'as' => 'api.v1.financial-checkup.cashflow-analysis.show', 
            'uses' => 'API\FinanceController@showCashflowAnalysis'
        ]
    );



    /**
     | ----------------------------------------------------------------------
     | Self financial checkup ...
     | Financila Checkup Client ...
     |
     | Gugun Dwi Permana
     | Agustus 2018
     */

    // Insert untuk semua pendapatan dan pengeluaran sekaligus
    Route::post('financial-checkup/self-cashflow-analysis/store', 
        [
            'as' => 'api.v1.financial-checkup.self-cashflow-analysis.store', 
            'uses' => 'API\FinanceController@storeSelfCashflowAnalysis'
        ]
    );
        // Insert hanya untuk pendapatan
        Route::post('financial-checkup/self/store-income', 
        [
                'as' => 'api.v1.financial-checkup.self.store-income', 
                'uses' => 'API\FinanceController@storeSelfIncome'
            ]
        );

        /**
         * Insert untuk masing-masing penreluaran ...
         */

        Route::post('financial-checkup/self/store-expense', 
        [
                'as' => 'api.v1.financial-checkup.self.store-expense', 
                'uses' => 'API\FinanceController@storeSelfExpense'
            ]
        );

            Route::post('financial-checkup/self/store-expense-auto', 
            [
                    'as' => 'api.v1.financial-checkup.self.store-expense-auto', 
                    'uses' => 'API\FinanceController@storeSelfExpenseAuto'
                ]
            );

        Route::post('financial-checkup/self/store-debt', 
        [
                'as' => 'api.v1.financial-checkup.self.store-debt', 
                'uses' => 'API\FinanceController@storeSelfDebt'
            ]
        );

        Route::post('financial-checkup/self/store-asset',
        [
                'as' => 'api.v1.financial-checkup.self.store-asset',
                'uses' => 'API\FinanceController@storeSelfAsset'
            ]
        );

        Route::post('financial-checkup/self/store-insurances',
        [
                'as' => 'api.v1.financial-checkup.self.store-insurances',
                'uses' => 'API\FinanceController@storeSelfInsurances'
            ]
        );

        // Untuk melakukan Rekap
        Route::post('financial-checkup/self/rekap', 
        [
                'as' => 'api.v1.financial-checkup.self.rekap', 
                'uses' => 'API\FinanceController@selfRekap'
            ]
        );

    // Menampilkan detail pengeluaran dan pendapatan dalam version yang aktif
    Route::post('financial-checkup/self-cashflow-analysis/show', 
        [
            'as' => 'api.v1.financial-checkup.self-cashflow-analysis.show', 
            'uses' => 'API\FinanceController@showSelfCashflowAnalysis'
        ]
    );

    // Menampilkan detail pengeluaran dan pendapatan dalam version yang belum aktif (OnGoing)
    Route::post('financial-checkup/self-cashflow-analysis/show-ongoing', 
        [
            'as' => 'api.v1.financial-checkup.self-cashflow-analysis.show-ongoing', 
            'uses' => 'API\FinanceController@showSelfFincheckOnGoing'
        ]
    );

    /**
     * Category Pendapatan dan Pengeluaran ...
     */
    
    Route::post('expense-categories', 
        [
            'as' => 'api.v1.expense-categories', 
            'uses' => 'API\FinanceController@getExpenseCategories'
        ]
    );

        Route::post('expense-default-categories', 
            [
                'as' => 'api.v1.expense-default-categories', 
                'uses' => 'API\FinanceController@getExpenseDefaultCategories'
            ]
        );


    Route::post('debt-categories', 
        [
            'as' => 'api.v1.debt-categories', 
            'uses' => 'API\FinanceController@getDebtCategories'
        ]
    );

    Route::post('asset-categories', 
        [
            'as' => 'api.v1.asset-categories', 
            'uses' => 'API\FinanceController@getAssetCategories'
        ]
    );

    Route::post('insurance-categories', 
        [
            'as' => 'api.v1.insurance-categories', 
            'uses' => 'API\FinanceController@getInsuranceCategories'
        ]
    );

    /** END ---------------------------------------------------------------------- */






            
    //portfolio analysis
    Route::post('financial-checkup/portfolio-analysis/store', ['as' => 'api.v1.financial-checkup.portfolio-analysis.store', 'uses' => 'API\FinanceController@storePortfolioAnalysis']);
    Route::post('financial-checkup/portfolio-analysis/show', ['as' => 'api.v1.financial-checkup.portfolio-analysis.show', 'uses' => 'API\FinanceController@showPortfolioAnalysis']);
            
    //plan a
    Route::post('plan/a/store', ['as' => 'api.v1.plan.a.store', 'uses' => 'API\PlanController@storePlanA']);
    Route::post('plan/a/show', ['as' => 'api.v1.plan.a.show', 'uses' => 'API\PlanController@showPlanA']);
    Route::post('plan/a/destroy', ['as' => 'api.v1.plan.a.destroy', 'uses' => 'API\PlanController@destroyPlanA']);
            
    //plan b
    Route::post('plan/b/store', ['as' => 'api.v1.plan.b.store', 'uses' => 'API\PlanController@storePlanB']);
    Route::post('plan/b/show', ['as' => 'api.v1.plan.b.show', 'uses' => 'API\PlanController@showPlanB']);
    Route::post('plan/b/destroy', ['as' => 'api.v1.plan.b.destroy', 'uses' => 'API\PlanController@destroyPlanB']);
            
    //plan analysis
    Route::post('plan-analysis/store', ['as' => 'api.v1.plan-analysis.store', 'uses' => 'API\PlanController@storePlanAnalysis']);
    Route::post('plan-analysis/show', ['as' => 'api.v1.plan-analysis.show', 'uses' => 'API\PlanController@showPlanAnalysis']);
            
    //log book
    Route::post('log-book/store', ['as' => 'api.v1.log-book.store', 'uses' => 'API\FinanceController@storeLogBookNote']);
    Route::post('log-book/show', ['as' => 'api.v1.log-book.show', 'uses' => 'API\FinanceController@showLogBookNote']);
            
    //approval
    Route::post('need-approval', ['as' => 'api.v1.need-approval', 'uses' => 'API\FinanceController@needApproval']);
    Route::post('approve/finance', ['as' => 'api.v1.approve.finance', 'uses' => 'API\FinanceController@approveFinance']);
    Route::post('approve/plan', ['as' => 'api.v1.approve.plan', 'uses' => 'API\FinanceController@approvePlan']);
    Route::post('approve/action-plan', ['as' => 'api.v1.approve.action-plan', 'uses' => 'API\FinanceController@approveActionPlan']);
    Route::post('has-approved-cashflow-analysis', ['as' => 'api.v1.has-approved-cashflow-analysis', 'uses' => 'API\FinanceController@hasApprovedCashflowAnalysis']);
    Route::post('check-approval', ['as' => 'api.v1.check-approval', 'uses' => 'API\FinanceController@checkApproval']);
            
    //Asset Repayment is on asset repayment paid ?
    Route::post('asset-repayment/show', ['as' => 'api.v1.asset-repayment.show', 'uses' => 'API\FinanceController@showAssetRepayment']);
            
    //reminder
    Route::post('reminder/store', ['as' => 'api.v1.reminder.store', 'uses' => 'API\ReminderController@store']);
    Route::post('reminder/update', ['as' => 'api.v1.reminder.update', 'uses' => 'API\ReminderController@update']);
    Route::post('reminder/destroy', ['as' => 'api.v1.reminder.destroy', 'uses' => 'API\ReminderController@destroy']);
    Route::post('reminders', ['as' => 'api.v1.reminder.all', 'uses' => 'API\ReminderController@allReminders']);
            
    //check free consultation
    Route::post('check-free-consultation', ['as' => 'api.v1.check-free-consultation', 'uses' => 'API\FinanceController@checkFreeConsultation']);
            
    //balance circles
    Route::post('balance-circles', ['as' => 'api.v1.balance-cirle', 'uses' => 'API\FinanceController@balanceCircles']);
            
    //end of month 
    Route::post('eom/store', ['as' => 'api.v1.eom.store', 'uses' => 'API\EomBalanceController@store']);
            
    //triangle layer
    Route::post('triangle-layers', ['as' => 'api.v1.triangle-layer', 'uses' => 'API\PlanController@triangleLayers']);
            
    //action plan
    Route::post('action-plans', ['as' => 'api.v1.action-plans', 'uses' => 'API\PlanController@getActionPlans']); 
            
    //article
    Route::post('articles', ['as' => 'api.v1.articles', 'uses' => 'API\ArticleController@index']);
    Route::post('article/show', ['as' => 'api.v1.article.show', 'uses' => 'API\ArticleController@show']); 
            
    //investment information
    Route::post('investment-informations', ['as' => 'api.v1.investment-information', 'uses' => 'API\FinanceController@investmentInformation']);
    Route::post('investment-information-confirm', ['as' => 'api.v1.investment-information-confirm', 'uses' => 'API\FinanceController@investmentInformationConfirm']);
            
    //financial health checkup
    Route::post('financial-health-structure', ['as' => 'api.v1.financial-health-structure', 'uses' => 'API\FinanceController@financialHealthStructure']);
    Route::post('financial-health/store', ['as' => 'api.v1.financial-health.store', 'uses' => 'API\FinanceController@financialHealthStore']);
            
    //check cycle
    Route::post('is-full-cycle', ['as' => 'api.v1.is-full-cycle', 'uses' => 'API\FinanceController@isFullCycle']);
            
    //rating for CFP
    Route::post('rating/store', ['as' => 'api.v1.rating.store', 'uses' => 'API\RatingController@store']);
    Route::post('rating/show', ['as' => 'api.v1.rating.show', 'uses' => 'API\RatingController@show']);
            
    //convert cash
    Route::post('convert-cash', ['as' => 'api.v1.convert.cash', 'uses' => 'API\FinanceController@convertCash']);

    Route::post('notification/push', ['as' => 'api.v1.notification.push', 'uses' => 'API\NotificationController@push']);

    Route::post('cfp/schedule_day_off', ['as' => 'api.v1.cfp.schedule.dayoff', 'uses' => 'API\CfpScheduleController@cfpScheduleDayOff']);
});

Route::get('user/activate/{activation_code}', [ 'as' => 'user.doactivate', 'uses' => 'API\ClientController@doActivate' ]);
Route::get('user/activate/failed', [ 'as' => 'user.activate.failed', 'uses' => 'API\ClientController@activateFailed' ]);

foreach (LaravelLocalization::getSupportedLanguagesKeys() as $lang) {
    LaravelLocalization::setLocale($lang);
    Route::group([
        'prefix' => $lang, 
        'middleware' => ['setLocale', 'localie', 'localizationRedirect', 'localeSessionRedirect']
    ], 
    function() use ( $lang ){



    });
}
/*
|--------------------------------------------------------------------------
| Backend Routes
|--------------------------------------------------------------------------
*/
require __DIR__.'/admin_routes.php';
// foreach (LaravelLocalization::getSupportedLanguagesKeys() as $lang) {
//     LaravelLocalization::setLocale($lang);
//     Route::group([
//         'prefix' => $lang, 
//         'middleware' => [ 'setLocale', 'localize', 'localizationRedirect', 'localeSessionRedirect']
//     ], 
//     function() use ($lang){  
//         Route::get(LaravelLocalization::transRoute('routes.page_slug'), array('as' => $lang.'.page.show', 'uses' => 'PageController@show'));
//     });
// } 
