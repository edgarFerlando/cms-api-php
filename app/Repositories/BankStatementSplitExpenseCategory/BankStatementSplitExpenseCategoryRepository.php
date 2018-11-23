<?php namespace App\Repositories\BankStatementSplitExpenseCategory;

use App\Models\BankStatementSplitExpenseCategory;
use App\Repositories\BankStatement\BankStatementRepository as BankStatement;
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
use DB;

class BankStatementSplitExpenseCategoryRepository extends RepositoryAbstract implements BankStatementSplitExpenseCategoryInterface, CrudableInterface {

    protected $bankStatementSplitExpenseCategory;
    protected $bankStatement;

    protected static $rules;
    protected static $attributeNames;

    public function __construct(BankStatementSplitExpenseCategory $bankStatementSplitExpenseCategory, BankStatement $bankStatement) {
        $this->bankStatementSplitExpenseCategory = $bankStatementSplitExpenseCategory;
        $this->bankStatement = $bankStatement;

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        $_rules['user_expense_id'] = 'required';
        return [
            'rules' => $_rules,
            'attributeNames' => $setAttributeNames
        ];
    }

    public function find($id) {
        return $this->bankStatementSplitExpenseCategory->find($id);
    }

    public function all() {
        return $this->bankStatementSplitExpenseCategory->orderBy('created_at', 'DESC')->get();
    }

    public function paginate($page = 1, $limit = 10, $filter = array()) {

        $result = new \StdClass;
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();

        $query = $this->bankStatementSplitExpenseCategory->select('*');
        $query->orderBy('created_at', 'DESC');

        $split = $query->skip($limit * ($page - 1))->take($limit)->get();


        $result->totalItems = $this->totalSplit($filter);
        $result->items = $split->all();

        foreach ($result->items as $key => $item) {
            $result->items[$key]->codes = $item;
        };

        return $result;
    }

    public function create($attributes) {
        $t_attributes = array();
        $user_id = Auth::user()->id;

        foreach ($attributes as $idx => $value) { 

            BankStatementSplitExpenseCategory::create([
                'bank_statement_id' => $value['bank_statement_id'],
                'user_expense_id' => $value['user_expense_id'],
                'description' => $value['description'],
                'amount' => $value['amount'],
                'user_id' => $value['user_id'],
                'bank_account_id' => $value['bank_account_id'],
                'created_at' => Carbon::now(),
                'created_by' => $user_id,
                'updated_at' => Carbon::now(),
                'updated_by' => $user_id,
                'record_flag' => 'N'
            ]);
            DB::commit();

        }

        return $attributes;
    }

    public function update($id, $attributes) {
        $t_attributes = array();
        if($this->isValid($attributes)) {
            $bankStatementSplitExpenseCategory = $this->find($id); 
            if(is_null($bankStatementSplitExpenseCategory)){
                throw new ValidationException('Bank Statement Split Expense Category validation failed', ['bank_statement_split' => 'not_found']);
            }else{
                $user_id = $bankStatementSplitExpenseCategory->user_id;
                $t_attributes += [
                    // 'is_categorized' => $attributes['is_categorized'],
                    'updated_by' => $user_id,
                    'updated_at' => Carbon::now(),
                    'record_flag' => 'U'
                ];
                $bankStatementSplitExpenseCategory->fill($t_attributes)->save();
                return true;
            }
        }
        throw new ValidationException('Bank Statement Split Expense Category validation failed', $this->getErrors());
    }

    public function updateBankStatement($id, $attributes) {
        $t_attributes = array();
        $bankStatement = $this->bankStatement->find($id);
        if(is_null($bankStatement)){
            throw new ValidationException('Bank Statement validation failed', ['bank_statement' => 'not_found']);
        }else{
            $user_id = $bankStatement->user_id;
            $t_attributes += [
                'is_categorized' => $attributes['is_categorized'],
                'updated_by' => $user_id,
                'updated_at' => Carbon::now(),
                'record_flag' => 'U'
            ];
            $bankStatement->fill($t_attributes)->save();
            return true;
        }
    }

    public function updateUserExpense($id, $attributes) {
        $t_attributes = array();
        $bankStatement = $this->bankStatement->find($id);
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
            return true;
        }
    }

    public function delete($id) {

        // $bankStatementSplitExpenseCategory = $this->bankStatementSplitExpenseCategory->findOrFail($id);
        // //$bankStatement->tags()->detach();
        // $bankStatementSplitExpenseCategory->delete();
        $bankStatementSplitExpenseCategory = $this->find($id); 
        if(is_null($bankStatementSplitExpenseCategory)){
            throw new ValidationException('Bank Statement Split Expense Category validation failed', ['bank_statement_split' => 'not_found']);
        }else{
            $user_id = $bankStatementSplitExpenseCategory->user_id;
            $t_attributes = [
                'deleted_by' => $user_id,
                'deleted_at' => Carbon::now(),
                'record_flag' => 'D'
            ];
            $bankStatementSplitExpenseCategory->fill($t_attributes)->save();
            return true;
        }
    }

}