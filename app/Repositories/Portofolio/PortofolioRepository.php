<?php namespace App\Repositories\Portofolio;

use App\Models\Portofolio;
use App\Models\PortofolioDetail;

use Auth;
use Config;
use Response;
use Illuminate\Support\Str;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface as CrudableInterface;
use App\Repositories\RepositoryInterface as RepositoryInterface;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\AbstractValidator as Validator;
use LaravelLocalization;
use Notification;
use App\User;


class PortofolioRepository extends RepositoryAbstract implements PortofolioInterface, CrudableInterface {

    protected $perPage;
    protected $portofolio;
    protected $portofolioDetail;
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
    public function __construct(Portofolio $portofolio) {

        $config = Config::get('holiday');
        $this->perPage = $config['per_page'];
        $this->portofolio = $portofolio;
        $this->portofolioDetail = new PortofolioDetail();

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        
        $_rules['portofolio_name'] = 'required|max:255';
        $setAttributeNames['portofolio_name'] = trans('app.portofolio_name');
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
        return $this->portofolio->orderBy('created_by', 'DESC')->get();
    }

    /**
     * @return mixed
     */
    public function lists($name, $key) {

        //return $this->role->get()->lists('title', 'id');
        return $this->portofolio->all()->lists($name, $key);
    }

    public function paginate($page = 1, $limit = 10, $all = false) {

        $result = new \StdClass;
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();

        $query = $this->portofolio->orderBy('created_by', 'DESC');
        //dd($query);
        if(!$all) {
            $query->where('is_published', 1);
        }

        //\DB::enableQueryLog();

        $portofolios = $query->skip($limit * ($page - 1))->take($limit)->get();

        //dd(\DB::getQueryLog());
        //dd($categoryCodes);

        $result->totalItems = $this->totalportofolios($all);
        $result->items = $portofolios->all();

        foreach ($result->items as $key => $item) {
            $result->items[$key]->portofolio = $item;

            $userCreate = User::find($item->created_by);
            //dd($item->created_by);
            $result->items[$key]->userCreate = $userCreate->name;

            if(!is_null($item->updated_by))
            {
                $userUpdate = User::find($item->updated_by);
                $result->items[$key]->userUpdate = $userUpdate->name;
            }
            
        };
        
        //dd($result);

        return $result;
    }

    public function find($id) {
        return $this->portofolio->findOrFail($id);
    }

    public function findWithDetail($id) {
       $portofolio = $this->portofolio->with(['portofolioDetails'])->find($id); 
       //dd($portofolio);
       $variations = [];
       foreach($portofolio->portofolioDetails as $idx => $sku){ 
           $variations[$sku->id]['id'] = $sku->id;
           $variations[$sku->id]['detail_name'] = $sku->detail_name;
           $variations[$sku->id]['detail_keterangan'] = $sku->keterangan;
       }

       $portofolio->variations = $variations;
       //dd($portofolio);
       return $portofolio;
    }

    public function create($attributes) {
        if($this->isValid($attributes)) {
            $portofolio_detail[] = array();

            $t_attributes = array();
            $t_attributes['portofolio_name'] = $attributes['portofolio_name'];
            $t_attributes['keterangan'] = $attributes['keterangan'];
            $t_attributes['created_by'] = Auth::user()->id;
            $t_attributes['created_on'] = date("Y-m-d H:i:s");
            $t_attributes['record_flag'] = 'N';

            //insert portofolio
            $portofolio = $this->portofolio->create([
                            'portofolio_name' => $t_attributes['portofolio_name'],
                            'keterangan' => $t_attributes['keterangan'],
                            'created_by' => $t_attributes['created_by'],
                            'created_on' => $t_attributes['created_on'],
                            'record_flag' => $t_attributes['record_flag']
                        ]);

            //set variable array for portofolio detail
            if(isset($attributes['portofolio_detail_datarow'])){ 
                $variant_datarow = buildPOST_fromJS($attributes['portofolio_detail_datarow']);

                foreach ($variant_datarow as $key => $data) {
                    foreach ($data as $key1 => $value) {

                        $portofolio_detail[$key][$key1] = $value['val']; 
                        $portofolio_detail[$key]['portofolio_id'] = $portofolio->id;
                    }
                }

                //dd($portofolio_detail);
                //insert into portofolio detail
                foreach ($portofolio_detail as $data) {
                    $this->portofolioDetail->create([
                                'detail_name' => $data['detail_name'],
                                'portofolio_id' => $portofolio->id,
                                'keterangan' => $data['detail_keterangan'],
                                'created_by' => $t_attributes['created_by'],
                                'created_on' => $t_attributes['created_on'],
                                'record_flag' => $t_attributes['record_flag']
                            ]);
                }
            }

            return true;
        }
        throw new ValidationException('Portofolio attribute validation failed', $this->getErrors());
    }

    public function update($id, $attributes) {
        if($this->isValid($attributes)) {
            $t_attributes = array();
            $this->portofolio = $this->find($id);
            
            $t_attributes['portofolio_name'] = $attributes['portofolio_name'];
            $t_attributes['keterangan'] = $attributes['keterangan'];
            $t_attributes['updated_by'] = Auth::user()->id;
            $t_attributes['updated_on'] = date("Y-m-d H:i:s");
            $t_attributes['record_flag'] = 'U';

            //update portofolio
            $this->portofolio->fill($t_attributes)->save();

            //set variable array for portofolio detail
            if(isset($attributes['portofolio_detail_datarow'])){ 
                $variant_datarow = buildPOST_fromJS($attributes['portofolio_detail_datarow']);

                foreach ($variant_datarow as $key => $data) {
                    foreach ($data as $key1 => $value) {

                        $portofolio_detail[$key][$key1] = $value['val']; 
                        $portofolio_detail[$key]['portofolio_id'] = $id;
                    }
                }

                //dd($portofolio_detail);
                $portofolioDetailIds = [];

                foreach ($portofolio_detail as $key => $data) {
                    if(!isset($data['id'])){ 
                        //insert into portofolio detail
                        $portofolioDetail = $this->portofolioDetail->create([
                                    'detail_name' => $data['detail_name'],
                                    'portofolio_id' => $id,
                                    'keterangan' => $data['detail_keterangan'],
                                    'created_by' => $t_attributes['updated_by'],
                                    'created_on' => $t_attributes['updated_on'],
                                    'record_flag' => 'N'
                                ]);

                        $portofolioDetailIds[$key] = $portofolioDetail->id;

                    }else{ 

                        //update into portofolio detail
                        $detailUpdate = $this->portofolioDetail->find($data['id']);
                        //dd($detailUpdate);
                        $detailUpdate->detail_name = $data['detail_name'];
                        $detailUpdate->portofolio_id = $id;
                        $detailUpdate->keterangan = $data['detail_keterangan']; 
                        $detailUpdate->updated_by = $t_attributes['updated_by'];
                        $detailUpdate->updated_on = $t_attributes['updated_on'];
                        $detailUpdate->record_flag = $t_attributes['record_flag'];

                        $detailUpdate->save();

                        $portofolioDetailIds[$key] = $data['id'];
                    }
                }

                $this->portofolioDetail->where('portofolio_id', $id)->whereNotIn('id', $portofolioDetailIds)->delete();
            }

            

            return true;
        }
        throw new ValidationException('Portofolio attribute validation failed', $this->getErrors());
    }

    public function delete($id) {
        $portofolioDetail = $this->portofolioDetail->where('portofolio_id', $id);
        $portofolioDetail->delete();
        
        $portofolio = $this->portofolio->findOrFail($id);
        $portofolio->delete();
    }

    protected function totalportofolios($all = false) {
        return $this->portofolio->count();
    }
}
