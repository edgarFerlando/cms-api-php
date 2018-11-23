<?php namespace App\Repositories\Wallet;

use App\Models\Wallet;

use Auth;
use Config;
use Response;
use Illuminate\Support\Str;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface as CrudableInterface;
use App\Repositories\RepositoryInterface as RepositoryInterface;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\AbstractValidator as Validator;
//use LaravelLocalization;
use Notification;
use App\User;

use Validator as Valid;
use Carbon\Carbon;

//use App\Taxonomy;
//use App\Repositories\Taxonomy\TaxonomyRepository;


class WalletRepository extends RepositoryAbstract implements WalletInterface, CrudableInterface {

    protected $perPage;
    protected $wallet;
    /**
     * Rules
     *
     * @var array
     */
    protected static $rules;
    protected static $attributeNames;

    /**
     * @param ProductAttribute $productAttribute
     */
    public function __construct(Wallet $Wallet) {

        $config = Config::get('holiday');
        $this->perPage = $config['per_page'];
        $this->wallet = $Wallet;
        //$this->taxonomy = new TaxonomyRepository(new Taxonomy);

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        
        $_rules['transaction_type'] = 'required';
        $_rules['amount'] = 'required';//|exists:users,id';
        $_rules['wallet_category'] = 'required';//|exists:users,id';
        $_rules['transaction_date'] = 'required';

        //$setAttributeNames['amount'] = trans('app.amount');
        //$setAttributeNames['wallet_category'] = trans('app.wallet_category');
        /*
        foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
            $_rules['name.'.$locale] = 'required|max:255';

            $setAttributeNames['name.' . $locale] = trans('app.name').' [ ' . $properties['native'].' ]';
        }
        */

        return [
            'rules' => $_rules,
            'attributeNames' => $setAttributeNames
        ]; 

    }

    public function all() {
        return $this->wallet->orderBy('created_by', 'DESC')->get();
    }

    public function paginate($page = 1, $limit = 10, $all = false) {

        $result = new \StdClass;
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();

        $query = $this->wallet->select('wallet.*', 'uc.name as created_by_name', 'uu.name as updated_by_name');
        $query->orderBy('created_by', 'DESC');
        $query->with(['category', 'transaction_type', 'category_type']);
        //dd($query);
        if(!$all) {
            $query->where('is_published', 1);
        }

        //$query->join('users as c', 'c.id', '=', 'cfp_clients.client_id', 'left');
        //$query->join('users as cfp', 'cfp.id', '=', 'cfp_clients.cfp_id', 'left');
        $query->join('users as uc', 'uc.id', '=', 'wallet.created_by', 'left');
        $query->join('users as uu', 'uu.id', '=', 'wallet.updated_by', 'left');

        //\DB::enableQueryLog();

        $transactions = $query->skip($limit * ($page - 1))->take($limit)->get();

        //dd(\DB::getQueryLog());
        //dd($categoryCodes);

        $result->totalItems = $this->totalTransactions($all);
        $result->items = $transactions->all();
        /*dd($result);
        foreach ($result->items as $key => $item) {
            $result->items[$key]->Wallets = $item;

            /*$customer_user = User::find($item->client_id);
            $cfp_user = User::find($item->cfp_id);
            $userCreate = User::find($item->created_by);

            $result->items[$key]->clientName = $customer_user->name;
            $result->items[$key]->cfpName = $cfp_user->name;
            $result->items[$key]->userCreate = $userCreate->name;

            if(!is_null($item->updated_by))
            {
                $userUpdate = User::find($item->updated_by);
                $result->items[$key]->userUpdate = $userUpdate->name;
            }*/
      /*  };*/
        
        //dd($result);

        return $result;
    }

    public function find($id) {
        return $this->wallet->find($id);
    }

    public function findCfpByClientEmail($client_email) {
        return $this->wallet->whereHas('cfp', function($q) use ($client_email){
            $q->whereRaw('LOWER(email) = ?', [ strtolower($client_email)]);
        })->get();
    }

    public function create($attributes) {

        if($this->isValid($attributes)) {
            /*
            'transaction_type_id', //simple atau detail, ini depth ke 0
            'amount', 
            'category_id', // taxonomy id, seharusnya depth ke 2
            'category_type', // income atau expense , ini depth ke 1
            'notes',
            'transaction_date',
            'created_by',
            'created_at', 
            'updated_by', 
            'updated_at', 
            'record_flag'*/
            //$category_info = $this->taxonomy->findWithParentDetail($attributes['wallet_category']);
            //dd($category_info);
            //$category_type = is_null($category_info)?'':$category_info->parentDetail->title;
            $user_id = isset($attributes['user_id'])?$attributes['user_id']:Auth::user()->id;//$attributes['user_id'] untuk API
            $t_attributes = [
                'transaction_type_id' => $attributes['transaction_type'],
                'amount' => $attributes['amount'],
                'category_id' => $attributes['wallet_category'],
                'category_type_id' => $attributes['category_type'],
                'notes' => $attributes['notes'],
                'transaction_date' => $attributes['transaction_date'],
                'created_by' => $user_id, 
                'created_at' => Carbon::now(), 
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(),
                'record_flag' => 'N'
            ];
            //dd($t_attributes);

            /*$t_attributes['transaction_type_id'] = $attributes['transaction_type'];
            $t_attributes['amount'] = $attributes['amount'];
            $t_attributes['wallet_category'] = $attributes['wallet_category'];
            $t_attributes['notes'] = $attributes['notes'];
            $t_attributes['transaction_date'] = $attributes['transaction_date'];*/
            $taxonomy = $this->wallet->create($t_attributes);
            return $taxonomy;
        }
        throw new ValidationException('Wallet validation failed', $this->getErrors());
    }

    public function createJUNK($attributes) { //dd($attributes);
        $rules['client_id'] = 'unique:cfp_clients,client_id';
        $rules['client_id'] = '';
        $attributeNames['client_id'] = trans('app.customer_name');

        $validator = Valid::make($attributes, $rules);
        $validator->setAttributeNames($attributeNames);

        if ($validator->fails())
        { 
            //dd($validator->errors());
            throw new ValidationException('Cfp client attribute validation failed', $validator->errors());
        }

        if($this->isValid($attributes)) {
            /*$t_attributes = array();
            $t_attributes['client_id'] = $attributes['client_id'];
            $t_attributes['cfp_id'] = $attributes['cfp_id'];
            $t_attributes['notes'] = $attributes['notes'];
            $t_attributes['created_by'] = Auth::user()->id;
            $t_attributes['created_on'] = date("Y-m-d H:i:s");
            $t_attributes['record_flag'] = 'N';*/
            /*
            $t_attributes['testimonial'] = Str::slug($attributes['name'][getLang()], '_');
            foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes[$locale] = [
                    'name' => $attributes['name'][$locale]
                ];
            }*/
            $user_id = Auth::user()->id;
            $client_raw = explode('__', $attributes['client_id']);
            $cfp_raw = explode('__', $attributes['cfp_id']);
            $t_attributes = [
                'client_id' => $client_raw[0],
                'cfp_id' => $cfp_raw[0],
                'notes' => $attributes['notes'],
                'created_by' => $user_id, 
                'created_at' => Carbon::now(), 
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(),
                'record_flag' => 'N'
            ];
            
            
            //dd($t_attributes);
            $this->wallet->fill($t_attributes)->save();

            return true;
        }
        throw new ValidationException('Cfp client attribute validation failed', $this->getErrors());
    }

    public function update($id, $attributes) {

        if($this->isValid($attributes)) {
            $user_id = isset($attributes['user_id'])?$attributes['user_id']:Auth::user()->id;//$attributes['user_id'] untuk API
            $this->transaction = $this->find($id);
            $t_attributes = [
                'transaction_type_id' => $attributes['transaction_type'],
                'amount' => $attributes['amount'],
                'category_id' => $attributes['wallet_category'],
                'category_type_id' => $attributes['category_type'],
                'notes' => $attributes['notes'],
                'transaction_date' => $attributes['transaction_date'],
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(),
                'record_flag' => 'U'
            ];
            $transaction = $this->transaction->fill($t_attributes)->save();
            return $this->find($id)->toArray();
        }
        throw new ValidationException('Wallet validation failed', $this->getErrors());
    }

    public function updateJUNK($id, $attributes) {
        if($this->isValid($attributes)) {
            $t_attributes = array();
            $this->wallet = $this->find($id);


            $user_id = Auth::user()->id;
            $client_raw = explode('__', $attributes['client_id']);
            $cfp_raw = explode('__', $attributes['cfp_id']);
            $t_attributes = [
                'client_id' => $client_raw[0],
                'cfp_id' => $cfp_raw[0],
                'notes' => $attributes['notes'],
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(),
                'record_flag' => 'U'
            ];


            //dd($attributes);
            /*$t_attributes['customer_id'] = $attributes['customer_id'];
            $t_attributes['cfp_id'] = $attributes['cfp_id'];
            $t_attributes['notes'] = $attributes['notes'];
            $t_attributes['updated_by'] = Auth::user()->id;
            $t_attributes['updated_on'] = date("Y-m-d H:i:s");
            $t_attributes['record_flag'] = 'U';*/
            /*
            $t_attributes['testimonial'] = Str::slug($attributes['name'][getLang()], '_');
            foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes[$locale] = [
                    'name' => $attributes['name'][$locale]
                ];
            }
            */
            $this->wallet->fill($t_attributes)->save();
            return true;
        }
        throw new ValidationException('Cfp client attribute validation failed', $this->getErrors());
    }

    public function delete($id) {
        $Wallet = $this->wallet->findOrFail($id);
        $Wallet->delete();
    }

    protected function totalTransactions($all = false) {
        return $this->wallet->count();
    }
}
