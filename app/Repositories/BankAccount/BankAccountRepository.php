<?php namespace App\Repositories\BankAccount;

use App\BankAccount;

// use Config;
// use Response;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface as CrudableInterface;
use App\Repositories\RepositoryInterface as RepositoryInterface;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\AbstractValidator as Validator;
// use LaravelLocalization;
// use Notification;
use Input;
use Hash;
use Auth;

// use App\Models\UserMeta;
// use App\Models\Role;
// use App\Taxonomy;
// use App\Models\CfpClient;
// use App\Models\UserCodeCounter;
// use App\Models\CfpCodeCounter;
//use App\Repositories\CfpClient\CfpClientRepository;
use DB;
use Carbon\Carbon;
use Route;
use JWTAuth;


class BankAccountRepository extends RepositoryAbstract implements BankAccountInterface, CrudableInterface {

    /**
     * @var
     */
    protected $perPage;
    /**
     * @var \Page
     */
    protected $bank_account;
    /**
     * Rules
     *
     * @var array
     */
    protected static $rules;

    protected static $attributeNames;
    
    // bank accounts status
    public $onProcess = 'on_process';
    public $connected = 'connected';
    public $failed = 'failed';

    /**
     * @param BankAccount $bank_account
     */
    public function __construct(BankAccount $bank_account) {
        $this->bank_account = $bank_account;
    }

    public function rules_on_create($attributes){        
        return [
            'rules' => [
                'user_id' => 'required',
                'bank_code' => 'required',
                'ibank_uid' => 'required|internet_banking_id_unique_for_user:'.$attributes['user_id'],
                'ibank_pin' => 'required'
            ],
            'attributeNames' => [
                'user_id' => 'Client Id',
                'bank_code' => 'Bank Code',
                'ibank_uid' => 'User ID',
                'ibank_pin' => 'Pin'
            ]
        ];       
    }

    public function rules_on_update($attributes){
        return [
            'rules' => [
                'user_id' => 'required',
                'bank_code' => 'required',
                'ibank_uid' => 'required',
                'ibank_pin' => 'required'
            ],
            'attributeNames' => [
                'user_id' => 'Client Id',
                'bank_code' => 'Bank Code',
                'ibank_uid' => 'User ID',
                'ibank_pin' => 'Pin'
            ]
        ];       
    }

    /**
     * @return mixed
     */
    public function all() {
        return $this->bank_account->orderBy('created_at', 'DESC')->get();
    }

    /**
     * @return mixed
     */
    public function lists($name = 'title', $id = 'id') {
        return $this->bank_account->all()->lists($name, $id);
        //return $this->bank_account->all()->lists($name, $id);
    }

    /**
     * Get paginated menu groups
     *
     * @param int $page Number of pages per page
     * @param int $limit Results per page
     * @param boolean $all Show published or all
     * @return StdClass Object with $items and $totalItems for pagination
     */
    public function paginate($page = 1, $limit = 10, $filter = array()){
        $result = new \StdClass;
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();

        //$query = $this->article->with('tags')->orderBy('created_at', 'DESC');
        $query = $this->bank_account->select('bank_accounts.*', 'banks.featured_image', 'banks.image_color')->orderBy('created_at', 'DESC');

        if(is_array($filter)){
            foreach($filter as $ff => $term){
                if(trim($term) != ''){
                    switch ($ff) {
                        case 'user_id':
                            $query->where('user_id', $term);
                        break;
                        case 'bank_code':
                            if($term  !== '')//tidak kosong, tapi harus 0 atau 1 saja
                                $query->where('bank_code', $term);
                        break;
                    }
                }
            }
        }

        $query->join('banks', 'bank_accounts.bank_code', '=', 'banks.slug');

        $accounts = $query->skip($limit * ($page - 1))->take($limit)->get();

        $result->totalItems = $this->totalBankAccounts($filter);
        $result->items = $accounts->all();
        return $result;
    }

    /**
     * Get total bank_account count
     * @param bool $all
     * @return mixed
     */
    protected function totalBankAccounts($filter = array()) {
        $query = $this->bank_account->select('bank_accounts.id');
        if(is_array($filter)){ 
            foreach($filter as $ff => $term){
                if(trim($term) != ''){
                    switch ($ff) {                        
                        case 'user_id':
                            $query->where('user_id', $term);
                        break;
                        case 'bank_code':
                            $query->where('bank_code', $term);
                        break;
                    }
                }
            }
        }
       
        return $query->count();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id) {
        return $this->bank_account->find($id);

    }

    public function findJoin($id) {
        return $this->bank_account
        ->select('bank_accounts.*', 'banks.title', 'banks.slug', 'banks.featured_image','banks.image_color')
        ->join('banks', 'bank_accounts.bank_code', '=', 'banks.slug')
        ->find($id);
    }

    /**
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function create($attributes) {
        $rules_n_attributeNames = $this->rules_on_create($attributes);
        self::$rules = $rules_n_attributeNames['rules'];        
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];

        if($this->isValid($attributes)) {
            $bank_account = new BankAccount;
            $bank_account->user_id = $attributes['user_id'];
            $bank_account->bank_code = $attributes['bank_code'];
            // $bank_account->account_name = $attributes['account_name'];
            // $bank_account->account_no = $attributes['account_no'];
            $bank_account->ibank_uid = $attributes['ibank_uid'];
            $bank_account->ibank_pin = $attributes['ibank_pin'];
            $bank_account->status = $this->onProcess;

            if($attributes['bank_code'] != 'bca' && $attributes['bank_code'] != 'mandiri' && $attributes['bank_code'] != 'mandiri-internet'){                
                // throw new ValidationException('Bank Code not found.', $this->getErrors());
                return false;
            }
            $bank_account->bank_code = $attributes['bank_code'];
            $bank_account->save();
            return true;
        }
        throw new ValidationException('User validation failed', $this->getErrors());
    }

    public function update($id, $attributes) {
        $rules_n_attributeNames = $this->rules_on_update($attributes);
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];

        if($this->isValid($attributes)) {
            $this->bank_account->where('id', $id)->update([
                'bank_code' => $attributes['bank_code'],
                'account_no' => isset($attributes['account_no']) ? $attributes['account_no'] : "",
                'account_name' => isset($attributes['account_name']) ? $attributes['account_name'] : "",
                'ibank_uid' => $attributes['ibank_uid'],
                'ibank_pin' => $attributes['ibank_pin'],
            ]);
            return true;
        }
        throw new ValidationException('User validation failed', $this->getErrors());
    }

    public function delete($id) {}

}
