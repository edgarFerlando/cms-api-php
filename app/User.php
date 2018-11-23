<?php namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Zizaco\Entrust\Traits\EntrustUserTrait;

use Cmgmyr\Messenger\Traits\Messagable;
use Auth;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

	use Authenticatable, CanResetPassword, EntrustUserTrait;
//    use SoftDeletes;
    use Messagable;
    //protected $dates = ['deleted_at'];
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
        'name', 
        'email', 
        'password', 
        'activation_code', 
        'is_active',
        'created_by',
        'created_at', 
        'updated_by', 
        'updated_at',
        'firebase_token',
        'cutoff_date',
        'provider',
        'provider_user_id',
        'certificate_no',
        'description'
    ];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [
        'password', 
        'remember_token',
        'activation_code',
        'api_token'
    ];
/*
    public static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            $model->deleted_by = Auth::id(); 
            $model->save();
        });
    }*/
	public function userMetas() {

        return $this->hasMany('App\Models\UserMeta', 'user_id', 'id');
    }

    public function userMeta_branch() {

        return $this->hasOne('App\Models\UserMeta', 'user_id', 'id')->where('meta_key', 'branch');//->where('meta_value', '<>', '');
    }

    public function trips()
    {
    	return $this->hasMany('App\Product', 'created_by', 'id');
    }

    public function myRole()
    {
    	return $this->with('roles')->find(Auth::user()->id);
    }

    public function role_user(){
        return $this->hasMany('App\Models\RoleUser', 'user_id', 'id');
    }

    public function goalGrade()
    {
    	return $this->hasMany('App\Models\GoalGrade', 'user_id', 'id');
    }

    public function cfpClient()
    {
    	return $this->hasOne('App\Models\CfpClient', 'client_id', 'id');
    }

    public function cfp_clients()
    {
        return $this->hasMany('App\Models\CfpClient', 'cfp_id', 'user_id');
    }

    public function incomes() {
        return $this->hasMany('App\Models\Income', 'user_id', 'id');
    }

    public function expenses() {
        return $this->hasMany('App\Models\Expense', 'user_id', 'id');
    }

    public function debt_repayments() {
        return $this->hasMany('App\Models\DebtRepayment', 'user_id', 'id');
    }

    public function asset_repayments() {
        return $this->hasMany('App\Models\AssetRepayment', 'user_id', 'id');
    }

    public function insurances() {
        return $this->hasMany('App\Models\Insurance', 'user_id', 'id');
    }

    public function asset_repayments_paid() {
        return $this->hasMany('App\Models\AssetRepaymentPaid', 'user_id', 'id');
    }

    public function asset_repayments_paidoff() {
        return $this->hasMany('App\Models\AssetRepaymentPaidoff', 'user_id', 'id');
    }

    public function plan_a() {
        return $this->hasMany('App\Models\PlanA', 'user_id', 'user_id');
    }

    public function income_simulations() {
        return $this->hasMany('App\Models\IncomeSimulation', 'user_id', 'user_id');
        //->select([ 'income_simulations.id', 'income_simulations.version', 'income_simulations.bunga_investasi_pertahun', 'income_simulations.cicilan_perbulan', 'income_simulations.produk', 'income_simulations.total_investasi']);
    }

    public function plan_protections() {
        return $this->hasMany('App\Models\PlanProtection', 'user_id', 'user_id');
    }

    public function insurance_coverages() {
        return $this->hasMany('App\Models\InsuranceCoverage', 'user_id', 'user_id');
    }

    public function plan_b() {
        return $this->hasMany('App\Models\PlanB', 'user_id', 'user_id');
    }

    public function plan_analysis() {
        return $this->hasMany('App\Models\PlanAnalysis', 'user_id', 'user_id');
    }

    /*public function a_plans() {
        return $this->hasMany('App\Models\PlanAnalysisPlan', 'user_id', 'user_id')->where('plan_type', 'a');
    }

    public function b_plans() {
        return $this->hasMany('App\Models\PlanAnalysisPlan', 'user_id', 'user_id')->where('plan_type', 'b');
    }*/

    public function a_plans() {
        return $this->hasMany('App\Models\PlanA', 'user_id', 'user_id');
    }

    public function b_plans() {
        return $this->hasMany('App\Models\PlanB', 'user_id', 'user_id');
    }

    public function plan_balances() {
        return $this->hasMany('App\Models\PlanBalance', 'user_id', 'user_id');
    }

    public function note() {
        return $this->hasOne('App\Models\LogBookNote', 'user_id', 'user_id');
    }

    public function notes() {
        return $this->hasMany('App\Models\LogBookNote', 'user_id', 'user_id');
    }
}
