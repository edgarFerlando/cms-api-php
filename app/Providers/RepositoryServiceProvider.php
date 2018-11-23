<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Article;
use App\Taxonomy;
use App\Menu;
use App\MenuGroup;
use App\Slider;
use App\Setting;
use App\User;
use App\Models\Permission;
use App\Models\Role;
use App\Models\EmailTemplate;
use App\Models\InterestRate;


use App\Repositories\Article\ArticleRepository;
use App\Repositories\Taxonomy\TaxonomyRepository;
use App\Repositories\Menu\MenuRepository;
use App\Repositories\MenuGroup\MenuGroupRepository;
use App\Repositories\Setting\SettingRepository;
use App\Repositories\User\UserRepository;
use App\Repositories\Permission\PermissionRepository;
use App\Repositories\Role\RoleRepository;
use App\Repositories\EmailTemplate\EmailTemplateRepository;
use App\Repositories\InterestRate\InterestRateRepository;

use App\Models\Affiliate;
use App\Repositories\Affiliate\AffiliateRepository;

use App\Models\Referral;
use App\Repositories\Referral\ReferralRepository;

use App\Models\Portofolio;
use App\Repositories\Portofolio\PortofolioRepository;

use App\Models\PortofolioDetail;
use App\Repositories\PortofolioDetail\PortofolioDetailRepository;

use App\Models\CfpClient;
use App\Repositories\CfpClient\CfpClientRepository;

use App\Models\CfpSchedule;
use App\Repositories\CfpSchedule\CfpScheduleRepository;

use App\Models\Grade;
use App\Repositories\Grade\GradeRepository;

use App\Models\Goal;
use App\Repositories\Goal\GoalRepository;

use App\Models\CfpScheduleType;
use App\Repositories\CfpScheduleType\CfpScheduleTypeRepository;

use App\Models\Wallet;
use App\Repositories\Wallet\WalletRepository;

use App\Models\WalletTransaction;
use App\Repositories\WalletTransaction\WalletTransactionRepository;

use App\Models\EomBalanceTransaction;
use App\Repositories\EomBalanceTransaction\EomBalanceTransactionRepository;

use App\Models\Triangle;
use App\Repositories\Triangle\TriangleRepository;

use App\Models\TriangleLayer;
use App\Repositories\TriangleLayer\TriangleLayerRepository;

use App\Models\ActionPlanCategory;
use App\Repositories\ActionPlanCategory\ActionPlanCategoryRepository;

use App\Models\ActionPlan;
use App\Repositories\ActionPlan\ActionPlanRepository;

use App\Models\InvestmentInformationClient;
use App\Repositories\InvestmentInformationClient\InvestmentInformationClientRepository;

use App\Models\FinancialHealth;
use App\Repositories\FinancialHealthClient\FinancialHealthRepository;

use App\Models\ActualInterestRate;
use App\Repositories\ActualInterestRate\ActualInterestRateRepository;

use App\Models\Cycle;
use App\Repositories\Cycle\CycleRepository;

use App\Models\CfpRating;
use App\Repositories\CfpRating\CfpRatingRepository;

use App\Models\BankAccount;
use App\Repositories\BankAccount\BankAccountRepository;

use App\Models\BankStatement;
use App\Repositories\BankStatement\BankStatementRepository;

class RepositoryServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		//
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{
		$app = $this->app;

        // article
		$app->bind('App\Repositories\Article\ArticleInterface', function ($app) {

			$article = new ArticleRepository(
				new Article
				);

			return $article;
		});

		// taxonomy
		$app->bind('App\Repositories\Taxonomy\TaxonomyInterface', function ($app) {

			$taxonomy = new TaxonomyRepository(
				new Taxonomy
				);
			return $taxonomy;
		});

        // menu
		$app->bind('App\Repositories\Menu\MenuInterface', function ($app) {

			$menu = new MenuRepository(
				new Menu
				);

			return $menu;
		});

		//menu group
		$app->bind('App\Repositories\MenuGroup\MenuGroupInterface', function ($app) {

			$menuGroup = new MenuGroupRepository(
				new MenuGroup
				);

			return $menuGroup;
		});

        // setting
		$app->bind('App\Repositories\Setting\SettingInterface', function ($app) {

			$setting = new SettingRepository(
				new Setting
				);

			return $setting;
		});

		// user
		$app->bind('App\Repositories\User\UserInterface', function ($app) {

			$user = new UserRepository(
				new User
				);

			return $user;
		});

		// user permission
		$app->bind('App\Repositories\Permission\PermissionInterface', function ($app) {

			$userPermission = new PermissionRepository(
				new Permission
				);

			return $userPermission;
		});

		// user role
		$app->bind('App\Repositories\Role\RoleInterface', function ($app) {

			$userRole = new RoleRepository(
				new Role
				);

			return $userRole;
		});

		// email template
		$app->bind('App\Repositories\EmailTemplate\EmailTemplateInterface', function ($app) {

			$emailTemplate = new EmailTemplateRepository(
				new EmailTemplate
				);

			return $emailTemplate;
		});

		// interest rate
		$app->bind('App\Repositories\InterestRate\InterestRateInterface', function ($app) {

			$interestRate = new InterestRateRepository(
				new InterestRate
				);

			return $interestRate;
		});

		// actual interest rate
		$app->bind('App\Repositories\ActualInterestRate\ActualInterestRateInterface', function ($app) {

			$actualInterestRate = new ActualInterestRateRepository(
				new ActualInterestRate
				);

			return $actualInterestRate;
		});

		// Affiliate
		$app->bind('App\Repositories\Affiliate\AffiliateInterface', function ($app) {

			$affiliate = new AffiliateRepository(
					new Affiliate
				);

			return $affiliate;
		});

		// Referral
		$app->bind('App\Repositories\Referral\ReferralInterface', function ($app) {

			$referral = new ReferralRepository(
					new Referral
				);

			return $referral;
		});

		//Portofolio
		$app->bind('App\Repositories\Portofolio\PortofolioInterface', function ($app) {

			$portofolio = new PortofolioRepository(
				new Portofolio
				);

			return $portofolio;
		});

		//Portofolio Detail
		$app->bind('App\Repositories\PortofolioDetail\PortofolioDetailInterface', function ($app) {

			$portofolioDetail = new PortofolioDetailRepository(
				new PortofolioDetail
				);

			return $portofolioDetail;
		});

		// CfpClient
		$app->bind('App\Repositories\CfpClient\CfpClientInterface', function ($app) {

			$CfpClient = new CfpClientRepository(
				new CfpClient
				);

			return $CfpClient;
		});

		// CfpSchedule
		$app->bind('App\Repositories\CfpSchedule\CfpScheduleInterface', function ($app) {

			$scheduleCfp = new CfpScheduleRepository(
				new CfpSchedule
				);

			return $scheduleCfp;
		});

		// reference
		$app->bind('App\Repositories\Reference\ReferenceInterface', function ($app) {

			$reference = new ReferenceRepository(
				new Reference
				);

			return $reference;
		});

		// grade
		$app->bind('App\Repositories\Grade\GradeInterface', function ($app) {

			$grade = new GradeRepository(
				new Grade
				);

			return $grade;
		});

		// goal
		$app->bind('App\Repositories\Goal\GoalInterface', function ($app) {

			$goal = new GoalRepository(
				new Goal
				);

			return $goal;
		});

		// CFP Schedule Type
		$app->bind('App\Repositories\CfpScheduleType\CfpScheduleTypeInterface', function ($app) {

			$CfpScheduleType = new CfpScheduleTypeRepository(
				new CfpScheduleType
				);

			return $CfpScheduleType;
		});

		// Wallet
		$app->bind('App\Repositories\Wallet\WalletInterface', function ($app) {

			$Wallet = new WalletRepository(
				new Wallet
				);

			return $Wallet;
		});

		// Wallet Transaction
		$app->bind('App\Repositories\WalletTransaction\WalletTransactionInterface', function ($app) {

			$WalletTransaction = new WalletTransactionRepository(
				new WalletTransaction
				);

			return $WalletTransaction;
		});

		// Eom Balance Transaction
		$app->bind('App\Repositories\EomBalanceTransaction\EomBalanceTransactionInterface', function ($app) {

			$EomBalanceTransaction = new EomBalanceTransactionRepository(
				new EomBalanceTransaction
				);

			return $EomBalanceTransaction;
		});

		// Triangle
		$app->bind('App\Repositories\Triangle\TriangleInterface', function ($app) {

			$Triangle = new TriangleRepository(
				new Triangle
				);

			return $Triangle;
		});

		// Triangle Layer
		$app->bind('App\Repositories\TriangleLayer\TriangleLayerInterface', function ($app) {

			$TriangleLayer = new TriangleLayerRepository(
				new TriangleLayer
				);

			return $TriangleLayer;
		});

		// Action plan category
		$app->bind('App\Repositories\ActionPlanCategory\ActionPlanCategoryInterface', function ($app) {

			$ActionPlanCategory = new ActionPlanCategoryRepository(
				new ActionPlanCategory
				);

			return $ActionPlanCategory;
		});

		// Action plan
		$app->bind('App\Repositories\ActionPlan\ActionPlanInterface', function ($app) {

			$ActionPlan = new ActionPlanRepository(
				new ActionPlan
				);

			return $ActionPlan;
		});

		// investment information client
		$app->bind('App\Repositories\InvestmentInformationClient\InvestmentInformationClientInterface', function ($app) {

			$InvestmentInformationClient = new InvestmentInformationClientRepository(
				new InvestmentInformationClient
				);

			return $InvestmentInformationClient;
		});

		// financial health
		$app->bind('App\Repositories\FinancialHealth\FinancialHealthInterface', function ($app) {

			$FinancialHealth = new FinancialHealthRepository(
				new FinancialHealth
				);

			return $FinancialHealth;
		});

		// cycle
		$app->bind('App\Repositories\Cycle\CycleInterface', function ($app) {

			$cycle = new CycleRepository(
				new Cycle
			);

			return $cycle;
		});

		// cfp ratings
		$app->bind('App\Repositories\CfpRating\CfpRatingInterface', function ($app) {

			$cfpRating = new CfpRatingRepository(
				new CfpRating
			);

			return $cfpRating;
		});


		//bank accounts
		$app->bind('App\Repositories\BankAccount\BankAccountInterface', function ($app) {

			$bankAccount = new BankAccountRepository(
				new BankAccount
			);

			return $bankAccount;
		});

		// bank statement
		$app->bind('App\Repositories\BankStatement\BankStatementInterface', function ($app) {

			$bankStatement = new BankStatementRepository(
				new BankStatement
			);

			return $bankStatement;
		});
	}

}
