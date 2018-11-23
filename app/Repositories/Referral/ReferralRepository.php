<?php namespace App\Repositories\Referral;

use App\Models\Referral;
use Config;
use Response;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface as CrudableInterface;
use App\Repositories\RepositoryInterface as RepositoryInterface;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\AbstractValidator as Validator;
use LaravelLocalization;
use Notification;
use Carbon\Carbon;
use Auth;
use App\Models\UserMeta;

use App\User;
use App\Repositories\User\UserRepository;
use App\Models\BookingHeader;
use App\Repositories\Booking\BookingRepository;

class ReferralRepository extends RepositoryAbstract implements ReferralInterface, CrudableInterface {


    protected $perPage;
    protected $referral;
    /**
     * Rules
     *
     * @var array
     */
    protected static $rules;
    protected static $attributeNames;

    /**
     * @param Referral $referral
     */
    public function __construct(Referral $referral) {

        //$config = Config::get('holiday');
        $this->perPage = config_db_cached('settings::backend_per_page');
        $this->referral = $referral;
        $this->user = new UserRepository(new User);
        $this->booking =  new BookingRepository(new BookingHeader);

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
    
        $_rules = array();
        $setAttributeNames = array();
        //$_rules['top_up_reseller'] = 'required';
        //$_rules['top_up_transfer_date'] = 'required|date_format:m/d/Y|before:now+1';
        //$_rules['top_up_bank_acc_name'] = 'required';
        //$_rules['top_up_amount'] = 'required';
        //$_rules['note'] = 'required';
        //$setAttributeNames['top_up_reseller'] = trans('app.reseller_name');
        //$setAttributeNames['top_up_transfer_date'] = trans('app.transfer_date');
        //$setAttributeNames['top_up_bank_acc_name'] = trans('app.bank_acc_name');
        //$setAttributeNames['top_up_amount'] = trans('app.amount');
       // $setAttributeNames['note'] = trans('app.note');

        return [
            'rules' => $_rules,
            'attributeNames' => $setAttributeNames
        ];
    }

    /**
     * @return mixed
     */
    public function all() {

        //return $this->referral->with('tags')->orderBy('created_at', 'DESC')->where('is_published', 1)->get();
        return $this->referral->orderBy('created_at', 'DESC')->where('is_published', 1)->get();
    }

    /**
     * @param $limit
     * @return mixed
     */
    public function getLastReferral($limit) {

        return $this->referral->orderBy('created_at', 'desc')->take($limit)->offset(0)->get();
    }

    /**
     * @return mixed
     */
    public function lists($name, $key) {

        //return $this->referral->get()->lists('title', 'id');
        return $this->referral->all()->lists($name, $key);
    }

    /**
     * Get paginated referrals
     *
     * @param int $page Number of referrals per page
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

        //$query = $this->referral->with('tags')->orderBy('created_at', 'DESC');
        $query = $this->referral->with(['booking.bookingStatus', 'booking.allBookingConfirmedDetails', 'booking.allBookingDetails'])->orderBy('booking_id', 'DESC');

        if(is_array($filter)){
            foreach($filter as $ff => $term){
                if(trim($term) != ''){
                    switch ($ff) {
                        case 'affiliate_name':
                                $query->where('affiliate_id', $term);
                            break;
                        case 'periode':
                                $query->whereHas('booking', function($q) use ($term) {
                                    $periode_date = Carbon::parse($term)->format('Y-m');
                                    $q->where(\DB::raw('DATE_FORMAT(created_at, "%Y-%m")'), $periode_date);
                                });
                            break;
                        /*case 'with' :
                            $query->whereHas('productSpecialOffers', function($q) {
                                $q->havingRaw('COUNT(DISTINCT `product_id`) > 0');
                            });
                            break;
                        case 'product_category':
                            $query->where('product_category_id', $term);
                            break;
                        case 'title':
                            $query->whereHas('productTranslation', function($q) use ($term) {
                                $q->where('title', 'like', '%'.$term.'%');
                            });
                            break;
                        case 'user':
                            $query->whereHas('user', function($q) use ($term) {
                                $q->where('name', 'like', '%'.$term.'%');
                            });
                            break;   
                        case 'code':
                            $query->where('code', 'like', '%'.$term.'%');
                            break;
                        case 'name':
                            $query->where('name', 'like', '%'.$term.'%');
                            break;*/
                        case 'status':
                            $query->whereHas('booking', function($q) use ($term) {
                                $q->where('status_id', $term);
                            });
                            break;/*
                        case 'reseller_id':
                            $query->where('reseller_id', $term);
                            break;*/
                    }
                }
            }
        }

        $referral = $query->skip($limit * ($page - 1))->take($limit)->get();

        $result->totalItems = $this->totalReferral($filter);
        $result->items = $referral->all();

        return $result;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id) {
        return $this->referral->findOrFail($id);
    }

    /**
     * @param $slug
     * @return mixed
     */
     public function getBySlug($slug, $isPublished = false) {
        if($isPublished === true)
           return $this->referral->select('referrals.id', 'referral_translations.slug')
            ->join('referral_translations', 'referrals.id', '=', 'referral_translations.referral_id')
            ->where('slug', $slug)->where('is_published', true)->firstOrFail();

        return $this->referral->select('referrals.id', 'referral_translations.slug')
            ->join('referral_translations', 'referrals.id', '=', 'referral_translations.referral_id')
            ->where('slug', $slug)->firstOrFail();
    }

    /**
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function create($attributes) {

        if($this->isValid($attributes)) {
            $user = User::with('userMetas')->find($attributes['top_up_reseller']);
            $existing_metas = userMeta($user->userMetas);

            $top_up_transfer_date = carbon_format_store($attributes['top_up_transfer_date']);
            $clean_attributes = [
                'reseller_id' => $attributes['top_up_reseller'],
                'bank_acc_name' => $attributes['top_up_bank_acc_name'],
                'amount' => unformat_money($attributes['top_up_amount']),
                'note' => $attributes['top_up_note'],
                'transferred_at' => $top_up_transfer_date,
                'status' => $attributes['top_up_status'],
                'created_at' => Carbon::now(),
                'created_by' => Auth::user()->id
            ];

            //email notif
            $email_template_module_id = 7;
            $email_replace_vars = [
                '{name}' => $user->name,
                '{transfer_date}' => date_trans($top_up_transfer_date),
                '{bank_account}' => $attributes['top_up_bank_acc_name'],
                '{amount}' => 'Rp '.$attributes['top_up_amount'],
                '{note}' => $attributes['top_up_note']
            ];

            if($attributes['top_up_status'] == 'confirmed'){
                //$existing_metas_q = UserMeta::where('user_id', $attributes['top_up_reseller'])->get();
                //$existing_metas = userMeta($existing_metas_q);
                $existing_metas->balance = isset($existing_metas->balance)?$existing_metas->balance:0;

                $prev_balance = $existing_metas->balance;
                $current_balance = $prev_balance + unformat_money($attributes['top_up_amount']);//balance setelah penambahan
                $clean_attributes += [
                    'prev_balance' => $prev_balance,
                    'current_balance' => $current_balance,
                    'updated_at' => Carbon::now(),
                    'updated_by' => Auth::user()->id,
                    'confirmed_at' => Carbon::now(),
                    'confirmed_by' => Auth::user()->id
                ];

                //email notif
                $email_template_module_id = 8;
                $email_replace_vars += [
                    '{prev_balance}' => 'Rp '.money($prev_balance),
                    '{current_balance}' => 'Rp '.money($current_balance)
                ];
            }

            //email notif
            $email_replace_vars += [
                '{status}' => trans('app.'.$attributes['top_up_status'])        
            ];

            if($this->referral->create($clean_attributes)){
                
                //update user meta balance
                if($attributes['top_up_status'] == 'confirmed'){
                    $this->user->update_userMeta($attributes['top_up_reseller'], $existing_metas, [ 'balance' => $current_balance ]);
                }

                sendEmailWithTemplate([
                    'email_template_module_id' => $email_template_module_id,//Top up balance : confirmed
                    'to' => $user->email,
                    'replace_vars' => $email_replace_vars
                ]);
            }
            
            return true;
        }

        throw new ValidationException('Balance history validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function update($id, $attributes) {
        if($this->isValid($attributes)) {
            //$user = User::with('userMetas')->find($attributes['top_up_reseller']);
            //$existing_metas = userMeta($user->userMetas);

            $referral = $this->find($id);
            //$top_up_transfer_date = carbon_format_store($attributes['top_up_transfer_date']);
            $clean_attributes = [
                //'reseller_id' => $attributes['top_up_reseller'],
                //'bank_acc_name' => $attributes['top_up_bank_acc_name'],
                //'amount' => unformat_money($attributes['top_up_amount']),
                'note' => $attributes['note'],
                //'transferred_at' => $top_up_transfer_date,
                'status' => $attributes['status'],
                'updated_at' => Carbon::now(),
                'updated_by' => Auth::user()->id
            ];

            //email notif
            /*$email_template_module_id = 7;
            $email_replace_vars = [
                '{name}' => $user->name,
                '{transfer_date}' => date_trans($top_up_transfer_date),
                '{bank_account}' => $attributes['top_up_bank_acc_name'],
                '{amount}' => 'Rp '.$attributes['top_up_amount'],
                '{note}' => $attributes['top_up_note']
            ];*/

            if($attributes['status'] == 'confirmed'){
                //$existing_metas_q = UserMeta::where('user_id', $attributes['top_up_reseller'])->get();
                //$existing_metas = userMeta($existing_metas_q);
                //$existing_metas->balance = isset($existing_metas->balance)?$existing_metas->balance:0;

                //$prev_balance = $existing_metas->balance;
                //$current_balance = $prev_balance + unformat_money($attributes['top_up_amount']);//balance setelah penambahan

                $clean_attributes += [
                    //'prev_balance' => $prev_balance,
                    //'current_balance' => $current_balance,
                    'confirmed_at' => Carbon::now(),
                    'confirmed_by' => Auth::user()->id
                ];

                //email notif
                //$email_template_module_id = 8;
               //$email_replace_vars += [
                //    '{prev_balance}' => 'Rp '.money($prev_balance),
                //    '{current_balance}' => 'Rp '.money($current_balance)
               // ];
            }

            //email notif
           // $email_replace_vars += [
            //    '{status}' => trans('app.'.$attributes['top_up_status'])        
           //];

            

            //$referral->update($clean_attributes);
            if($referral->update($clean_attributes)){
                if($attributes['status'] == 'confirmed'){
                    $booking_id = $referral->booking_id;
                    $booking = $this->booking->find($booking_id);
                    $booking->status_id = 3;//paid
                    $booking->save();
                }
            }

            return true;
        }

        throw new ValidationException('referral validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @return mixed|void
     */
    public function delete($id) {
        $this->referral->findOrFail($id)->delete();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function togglePublish($id) {

        $referral = $this->referral->find($id);

        $referral->is_published = ($referral->is_published) ? false : true;
        $referral->save();

        return Response::json(array('result' => 'success', 'changed' => ($referral->is_published) ? 1 : 0));
    }

    /**
     * @param $id
     * @return string
     */
    function getUrl($id) {
        $referral = $this->referral->findOrFail($id);
        return url('referral/' . $id . '/' . $referral->slug, $parameters = array(), $secure = null);
    }

    /**
     * Get total referral count
     * @param bool $all
     * @return mixed
     */
    protected function totalReferral($filter) {
        $query = $this->referral->orderBy('booking_id', 'DESC');//harus ada order by kalo ngga , where nya ga ikut ke ambil
        if(is_array($filter)){
            foreach($filter as $ff => $term){
                if(trim($term) != ''){
                    switch ($ff) {
                        case 'affiliate_name':
                                $query->where('affiliate_id', $term);
                            break;
                        case 'periode':
                                $query->whereHas('booking', function($q) use ($term) {
                                    $periode_date = Carbon::parse($term)->format('Y-m');
                                    $q->where(\DB::raw('DATE_FORMAT(created_at, "%Y-%m")'), $periode_date);
                                });
                            break;
                        /*case 'with' :
                            $query->whereHas('productSpecialOffers', function($q) {
                                $q->havingRaw('COUNT(DISTINCT `product_id`) > 0');
                            });
                            break;
                        case 'product_category':
                            $query->where('product_category_id', $term);
                            break;
                        case 'title':
                            $query->whereHas('productTranslation', function($q) use ($term) {
                                $q->where('title', 'like', '%'.$term.'%');
                            });
                            break;
                        case 'user':
                            $query->whereHas('user', function($q) use ($term) {
                                $q->where('name', 'like', '%'.$term.'%');
                            });
                            break;
                        case 'email':
                            $query->where('email', 'like', '%'.$term.'%');
                            break;*/
                        case 'status':
                            $query->whereHas('booking', function($q) use ($term) {
                                $q->where('status_id', $term);
                            });
                            break;
                        /*case 'reseller_id':
                            $query->where('reseller_id', $term);
                            break;*/
                    }
                }
            }
        }
        return $query->count();
    }
}
