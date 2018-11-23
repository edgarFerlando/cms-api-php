<?php namespace App\Repositories\BankStatement;

use App\Models\BankStatement;
use Config;
use Response;
//use App\Tag;
use App\Category;
use Str;
//use Event;
//use Image;
//use File;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface as CrudableInterface;
use App\Repositories\RepositoryInterface as RepositoryInterface;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\AbstractValidator as Validator;
use LaravelLocalization;
use Notification;
use Auth;
use Carbon\Carbon;

use App\Repositories\BankStatementRepositories;
use Illuminate\Support\Facades\DB;


class BankStatementRepository extends RepositoryAbstract implements BankStatementInterface, CrudableInterface {

    //protected $perPage;
    protected $bankStatement;
    /**
     * Rules
     *
     * @var array
     */
    protected static $rules;
    protected static $attributeNames;

    /**
     * @param BankStatement $bankStatement
     */
    public function __construct(BankStatement $bankStatement) {
        $this->bankStatement = $bankStatement;

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        $_rules['bank_statement_id'] = 'required';
        $_rules['user_expense_id'] = 'required';
        return [
            'rules' => $_rules,
            'attributeNames' => $setAttributeNames
        ];
    }

    /**
     * @return mixed
     */
    public function all() {
        return $this->bankStatement->orderBy('created_at', 'DESC')->where('is_published', 1)->get();
    }

    /**
     * @param $limit
     * @return mixed
     */
    public function getLastBankStatement($limit) {

        return $this->bankStatement->orderBy('created_at', 'desc')->take($limit)->offset(0)->get();
    }

    /**
     * @return mixed
     */
    public function lists() {

        //return $this->bankStatement->get()->lists('title', 'id');
        return $this->bankStatement->all()->lists('title', 'id');
    }

    /**
     * Get paginated bankStatements
     *
     * @param int $page Number of bankStatements per page
     * @param int $limit Results per page
     * @param boolean $all Show published or all
     * @return StdClass Object with $items and $totalItems for pagination
     */
    public function paginate($page = 1, $limit = 10, $filter = array()) {

        $result = new \StdClass;
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();
        $query = $this->bankStatement->select('bank_statements.*', 'uc.name as created_by_name', 'uu.name as updated_by_name', 'banks.title', 'banks.slug', 'ba.ibank_uid', 'banks.color', 'banks.featured_image')
        ->orderBy('created_at', 'DESC');
        $query->join('users as uc', 'uc.id', '=', 'bank_statements.created_by', 'left');
        $query->join('users as uu', 'uu.id', '=', 'bank_statements.updated_by', 'left');
        $query->join('banks', 'bank_statements.bank_code_name', '=', 'banks.slug');
        $query->join('bank_accounts as ba', 'bank_statements.bank_account_id', '=', 'ba.id');

        if(is_array($filter)){
            //khusus filter is_uncategorized harus diset diluar switch
            if(isset($filter['is_uncategorized']) && $filter['is_uncategorized'] === true){
                //hanya menampilkan yang tidak berkategori / tidak memiliki user_expense_id
                $query->whereNull('user_expense_id');
            }elseif(!isset($filter['is_uncategorized']) && isset($filter['transaction_monthly'])){
                //hanya menampilkan data mutasi bank per bulan
                // $query->whereRaw("to_char(transaction_date, 'YYYY-MM-DD') >= trim('".$filter['transaction_monthly']."')");
                $query->where('transaction_date', '>=', $filter['transaction_monthly']);
                $query->where('transaction_date', '<=', $filter['transaction_monthly_next']);
            }else{
                //hanya menampilkan yang memiliki user_expense_id
                $query->whereNotNull('user_expense_id');
            }

            foreach($filter as $ff => $term){
                if(trim($term) != ''){
                    switch ($ff) {
                        case 'transaction_type':
                            $query->where('transaction_type', $term);
                        break;
                        case 'transaction_date_period':
                            // $start_date_raw = Carbon::createFromFormat('Y-m-d' , $term.'-01')->startOfMonth();
                            // $start_date = $start_date_raw->format('Y-m-d H:i:s'); 
                            // $end_date = $start_date_raw->endOfMonth()->format('Y-m-d H:i:s');
                            $query->where('transaction_date', '>=', $filter['transaction_date_period']) //$start_date)
                            ->where('transaction_date', '<=', $filter['transaction_date_period_next']); //$end_date);
                        break;
                        case 'transaction_mutation_daily':
                            $query->whereRaw("to_char(transaction_date, 'YYYY-MM-DD') = trim('".$term."')");
                        break;
                        case 'user_id':
                            $query->where('bank_statements.user_id', '=', $term);
                        break;
                        case 'transaction_daily':
                            $query->whereRaw("to_char(transaction_date, 'YYYY-MM-DD') = trim('".$term."')");
                        break;
                    }
                }
            }
        }
        //\DB::enableQueryLog();
        //$query->get();
        //dd(\DB::getQueryLog());


        /**
         | -----------------------
         | 08 Agustus 2018
         | -----------------------
         | Soft delete ini harus sama dengan function *totalBankStatements()*
         | supaya jumlah count(*) dengan jumlah data sama
        */

        // $query->whereRaw('bank_statements.deleted_at = ?',array(NULL));

        //coding Gugun
        //$query->where('bank_statements.record_flag', '!=','D');

        
        //coding Andriansyah
        //$query->whereRaw("bank_statements.record_flag is null or bank_statements.record_flag != 'D'");

        $bankStatements = $query->skip($limit * ($page - 1))->take($limit)->get();

        $result->totalItems = $this->totalBankStatements($filter);
        $result->items = $bankStatements->all();
        return $result;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id) {
        return $this->bankStatement->find($id);
    }

    public function findAllBy($filter = array()){
        $query = $this->bankStatement->orderBy('transaction_date', 'DESC');
        if(is_array($filter)){
            foreach($filter as $ff => $term){
                if(is_array($term) || trim($term) != ''){
                    switch ($ff) {
                        case 'period':
                            $start_date_raw = Carbon::createFromFormat('Y-m-d' , $term.'-01')->startOfMonth();
                            $start_date = $start_date_raw->format('Y-m-d H:i:s'); 
                            $end_date = $start_date_raw->endOfMonth()->format('Y-m-d H:i:s');
                            $query->where('transaction_date', '>=', $start_date)
                            ->where('transaction_date', '<=', $end_date);
                        break;
                        case 'user_expense_ids':
                            $query->whereIn('user_expense_id', $term);
                        break;
                        case 'client_id':
                            $query->where('user_id', $term);
                        break;
                        case 'transaction_type':
                            $query->where('transaction_type', $term);
                        break;
                        case 'bank-account-id':
                            $query->where('bank_account_id', $term);
                        break;
                        case 'period-last':
                            $query->where('transaction_date', '>=', $term);
                        break;
                        case 'period-next':
                            $query->where('transaction_date', '<=', $term);
                        break;
                    }
                }
            }
        }

        // \DB::enableQueryLog();
        // $query->get();
        // dd(\DB::getQueryLog());
    
        return $query->get();
    }

    public function findAllByJoin($filter = array())
    {
        $query = $this->bankStatement->orderBy('transaction_date', 'DESC');
        $query->join('banks', 'bank_statements.bank_code_name', '=', 'banks.slug');
        $query->join('bank_accounts as ba', 'bank_statements.bank_account_id', '=', 'ba.id');
        $query->select('bank_statements.*', 'banks.title', 'banks.slug', 'ba.ibank_uid');
        if(is_array($filter)){
            foreach($filter as $ff => $term){
                if(is_array($term) || trim($term) != ''){
                    switch ($ff) {
                        case 'period':
                            $start_date_raw = Carbon::createFromFormat('Y-m-d' , $term.'-01')->startOfMonth();
                            $start_date = $start_date_raw->format('Y-m-d H:i:s'); 
                            $end_date = $start_date_raw->endOfMonth()->format('Y-m-d H:i:s');
                            $query->where('transaction_date', '>=', $start_date)
                            ->where('transaction_date', '<=', $end_date);
                        break;
                        case 'user_expense_ids':
                            $query->whereIn('user_expense_id', $term);
                        break;
                        case 'client_id':
                            $query->where('bank_statements.user_id', $term);
                        break;
                        case 'transaction_type':
                            $query->where('transaction_type', $term);
                        break;
                        case 'bank-account-id':
                            $query->where('bank_account_id', $term);
                        break;
                        case 'period-last':
                            $query->where('transaction_date', '>=', $term);
                        break;
                        case 'period-next':
                            $query->where('transaction_date', '<=', $term);
                        break;
                    }
                }
            }
        }

        // \DB::enableQueryLog();
        // $query->get();
        // dd(\DB::getQueryLog());
    
        return $query->get();
    }

    public function findAllByJoinSplit($filter = array())
    {
        $query = $this->bankStatement->orderBy('transaction_date', 'DESC');
        $query->join('banks', 'bank_statements.bank_code_name', '=', 'banks.slug');
        $query->join('bank_accounts as ba', 'bank_statements.bank_account_id', '=', 'ba.id');
        $query->leftJoin('bank_statements_split_expense_categories as bss', 'bank_statements.id', '=', 'bss.bank_statement_id');
        $query->select('bank_statements.*', 'banks.title', 'banks.slug', 'ba.ibank_uid', 'bss.id as split_id', 'bss.user_expense_id as split_expense_id', 'bss.amount', 'bss.record_flag as flag_split');
        if(is_array($filter)){
            foreach($filter as $ff => $term){
                if(is_array($term) || trim($term) != ''){
                    switch ($ff) {
                        case 'period':
                            $start_date_raw = Carbon::createFromFormat('Y-m-d' , $term.'-01')->startOfMonth();
                            $start_date = $start_date_raw->format('Y-m-d H:i:s'); 
                            $end_date = $start_date_raw->endOfMonth()->format('Y-m-d H:i:s');
                            $query->where('transaction_date', '>=', $start_date)
                            ->where('transaction_date', '<=', $end_date);
                        break;
                        case 'user_expense_ids':
                            $query->whereIn('bank_statements.user_expense_id', $term);
                        break;
                        case 'client_id':
                            $query->where('bank_statements.user_id', $term);
                        break;
                        case 'transaction_type':
                            $query->where('transaction_type', $term);
                        break;
                        case 'bank-account-id':
                            $query->where('bank_account_id', $term);
                        break;
                        case 'period-last':
                            $query->where('transaction_date', '>=', $term);
                        break;
                        case 'period-next':
                            $query->where('transaction_date', '<=', $term);
                        break;
                    }
                }
            }
        }

        // \DB::enableQueryLog();
        // $query->get();
        // dd(\DB::getQueryLog());
    
        return $query->get();
    }

    public function sumTransAmountAllBy($filter = array()){
        $query = $this->bankStatement;
        if(is_array($filter)){
            foreach($filter as $ff => $term){
                if(is_array($term) || trim($term) != ''){
                    switch ($ff) {
                        case 'period':
                            $start_date_raw = Carbon::createFromFormat('Y-m-d' , $term.'-01')->startOfMonth();
                            $start_date = $start_date_raw->format('Y-m-d H:i:s'); 
                            $end_date = $start_date_raw->endOfMonth()->format('Y-m-d H:i:s');
                            $query->where('transaction_date', '>=', $start_date)
                            ->where('transaction_date', '<=', $end_date);
                        break;
                        case 'user_expense_ids':
                            $query->whereIn('user_expense_id', $term);
                        break;
                        case 'client_id':
                            $query->where('user_id', $term);
                        break;
                        case 'transaction_type':
                            $query->where('transaction_type', $term);
                        break;
                    }
                }
            }
        }

        // \DB::enableQueryLog();
        // $query->get();
        // dd(\DB::getQueryLog());
    
        return $query->sum('transaction_amount');
    }

    /**
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function create($attributes) {
        $t_attributes = array();
        $user_id = Auth::user()->id;
        $t_attributes['category_id'] = $attributes['category'];
        $t_attributes['is_published'] = isset($attributes['is_published']) ? true : false;
        $t_attributes['featured_image'] = isset($attributes['featured_image'])?getImagePath($attributes['featured_image']):'';

        if($this->isValid($attributes)) {
            $t_attributes += [
                'title' => $attributes['title'],
                'slug' => $attributes['slug'],
                'body' => $attributes['body'],
                'created_by' => $user_id,
                'updated_by' => $user_id,
                'record_flag' => 'N'
            ];
            $this->bankStatement->fill($t_attributes)->save();

            return true;
        }
        throw new ValidationException('BankStatement validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function update($id, $attributes) {
        $t_attributes = array();
        $attributes['bank_statement_id'] = $id;
        if($this->isValid($attributes)) {
            $bankStatement = $this->find($id); 
            if(is_null($bankStatement)){
                throw new ValidationException('Bank Statement validation failed', ['bank_statement' => 'not_found']);
            }else{
                $user_id = $bankStatement->user_id;
                $t_attributes += [
                    'user_expense_id' => $attributes['user_expense_id'],
                    'updated_by' => $user_id,
                    'updated_at' => Carbon::now(),
                    'record_flag' => 'U'
                ];
                $bankStatement->fill($t_attributes)->save();
                return $bankStatement;
            }
        }
        throw new ValidationException('Bank Statement validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @return mixed|void
     */
    public function delete($id) {

        $bankStatement = $this->bankStatement->findOrFail($id);
        //$bankStatement->tags()->detach();
        $bankStatement->delete();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function togglePublish($id) {

        $bankStatement = $this->bankStatement->find($id);

        $bankStatement->is_published = ($bankStatement->is_published) ? false : true;
        $bankStatement->save();

        return Response::json(array('result' => 'success', 'changed' => ($bankStatement->is_published) ? 1 : 0));
    }

    /**
     * @param $id
     * @return string
     */
    function getUrl($id) {

        $bankStatement = $this->bankStatement->findOrFail($id);
        return url('bankStatement/' . $id . '/' . $bankStatement->slug, $parameters = array(), $secure = null);
    }

    /**
     * Get total bankStatement count
     * @param bool $all
     * @return mixed
     */
    protected function totalBankStatements($filter = array()) {
      
        $query = $this->bankStatement->select('bankStatements.id');
        if(is_array($filter)){
            //khusus filter is_uncategorized harus diset diluar switch
            if(isset($filter['is_uncategorized']) && $filter['is_uncategorized'] === true){
                //hanya menampilkan yang tidak berkategori / tidak memiliki user_expense_id
                $query->whereNull('user_expense_id');
            }elseif(!isset($filter['is_uncategorized']) && isset($filter['transaction_monthly'])){
                //hanya menampilkan data mutasi bank per bulan
                // $query->whereRaw("to_char(transaction_date, 'YYYY-MM-DD') >= trim('".$filter['transaction_monthly']."')");
                $query->where('transaction_date', '>=', $filter['transaction_monthly']);
                $query->where('transaction_date', '<=', $filter['transaction_monthly_next']);
            }else{
                //hanya menampilkan yang memiliki user_expense_id
                $query->whereNotNull('user_expense_id');
            }
            foreach($filter as $ff => $term){
                if(trim($term) != ''){
                    switch ($ff) {
                        case 'transaction_type':
                            $query->where('transaction_type', $term);
                        break;
                        case 'transaction_date_period':
                            // $start_date_raw = Carbon::createFromFormat('Y-m-d' , $term.'-01')->startOfMonth();
                            // $start_date = $start_date_raw->format('Y-m-d H:i:s'); 
                            // $end_date = $start_date_raw->endOfMonth()->format('Y-m-d H:i:s');
                            $query->where('transaction_date', '>=', $filter['transaction_date_period']) //$start_date)
                            ->where('transaction_date', '<=', $filter['transaction_date_period_next']); //$end_date);
                        break;
                        case 'transaction_mutation_daily':
                            $query->whereRaw("to_char(transaction_date, 'YYYY-MM-DD') = trim('".$term."')");
                        break;
                        case 'user_id':
                            $query->where('user_id', $term);
                        break;
                        case 'transaction_type':
                            $query->where('transaction_type', $term);
                        break;
                    }
                }
            }
        }


        /**
         | -----------------------
         | 08 Agustus 2018
         | -----------------------
         | Soft delete ini harus sama dengan function *paginate()*
         | supaya jumlah count(*) dengan jumlah data sama
        */
        //coding Gugun
        //$query->where('bank_statements.record_flag', '!=','D');

        //coding Andriansyah
        //$query->whereRaw("bank_statements.record_flag is null or bank_statements.record_flag != 'D'");

        return $query->count();
    }

    public function getTransactionAmount($filter=array())
    {
        $query = BankStatement::query();
        if(is_array($filter)){
            foreach($filter as $ff => $term){
                if(is_array($term) || trim($term) != ''){
                    switch ($ff) {
                        case 'period':
                            $query->whereRaw("to_char(transaction_date, 'YYYY-MM-DD') = trim('".$term."')");
                        break;
                        case 'period-monitor':
                            $query->whereRaw("to_char(transaction_date, 'YYYY-MM') = trim('".$term."')");
                        break;
                        case 'period-last':
                            $query->where('transaction_date', '>=', $term);
                        break;
                        case 'period-next':
                            $query->where('transaction_date', '<=', $term);
                        break;
                        case 'client_id':
                            $query->where('user_id', $term);
                        break;
                        case 'transaction_type':
                            $query->where('transaction_type', $term);
                        break;
                        case 'kategori-expenses':
                            $query->whereNotIn('user_expense_id', $term);
                        break;
                        case 'user_expense_id':
                            $query->where('user_expense_id', $term);
                        break;
                        case 'bank_account_id':
                            $query->whereIn('bank_account_id', $term);//wherein bank_account_id
                        break;
                        case 'bank-account-id':
                            $query->where('bank_account_id', $term);//where bank_account_id
                        break;
                    }
                }
            }
        }

        return $query->sum('transaction_amount');
    }

    public function getLastTransactionBalance($filter=array())
    {
        $query = BankStatement::query();
        if(is_array($filter)){
            foreach($filter as $ff => $term){
                if(is_array($term) || trim($term) != ''){
                    switch ($ff) {
                        case 'period-now':
                            $query->whereRaw("to_char(transaction_date, 'YYYY-MM-DD') = trim('".$term."')");
                        break;
                        case 'period-last':
                            $query->where('transaction_date', '<', $term.'-01');
                        break;
                        case 'period-last-cutoff':
                            $query->where('transaction_date', '<', $term);
                        break;
                        case 'client_id':
                            $query->where('user_id', $term);
                        break;
                        case 'bank-account-id':
                            $query->where('bank_account_id', $term);
                        break;
                        case 'period':
                            $start_date_raw = Carbon::createFromFormat('Y-m-d' , $term.'-01')->startOfMonth();
                            $start_date = $start_date_raw->format('Y-m-d H:i:s'); 
                            $end_date = $start_date_raw->endOfMonth()->format('Y-m-d H:i:s');
                            $query->where('transaction_date', '>=', $start_date)
                            ->where('transaction_date', '<=', $end_date);
                        break;
                    }
                }
            }
        }

        return $query->orderBy('id', 'desc')->take(1)->offset(0)->get();
    }

    public function getLastTransactionBalanceLast($client,$period)
    {
        $query = DB::select("select y.*, x.* from (
            select 
                z.bank_account_id
                , (select c.id from bank_statements c 
                   where c.bank_account_id = z.bank_account_id 
                   and to_char(transaction_date, 'YYYY-MM-DD') <= trim('$period')
                   order by c.id DESC limit 1) ID
            from (
                SELECT distinct a.bank_account_id
                FROM bank_statements a
                where
                    a.user_id = $client
                ) z
            ) y
            INNER JOIN bank_statements x ON y.ID = x.ID");

        return $query;
    }

    public function getLastTransactionBalanceNow($client)
    {
        $query = DB::select("select y.*, x.* from (
            select 
                z.bank_account_id
                , (select c.id from bank_statements c 
                   where c.bank_account_id = z.bank_account_id
                   order by c.id DESC limit 1) ID
            from (
                SELECT distinct a.bank_account_id
                FROM bank_statements a
                where
                    a.user_id = $client
                ) z
            ) y
            INNER JOIN bank_statements x ON y.ID = x.ID");

        return $query;
    }
}
