<?php namespace App\Repositories\EmailTemplate;

use App\Models\EmailTemplate;
use Config;
use Response;
use App\Category;
use Str;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface as CrudableInterface;
use App\Repositories\RepositoryInterface as RepositoryInterface;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\AbstractValidator as Validator;
use LaravelLocalization;
use Notification;


class EmailTemplateRepository extends RepositoryAbstract implements EmailTemplateInterface, CrudableInterface {


    protected $perPage;
    protected $emailTemplate;
    /**
     * Rules
     *
     * @var array
     */
    protected static $rules;
    protected static $attributeNames;

    /**
     * @param EmailTemplate $emailTemplate
     */
    public function __construct(EmailTemplate $emailTemplate) {

        $this->emailTemplate = $emailTemplate;

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        $_rules['email_template_module'] = 'required';

        $setAttributeNames['subject'] = trans('app.subject');
        $setAttributeNames['body'] = trans('app.content');

        $setAttributeNames['email_template'] = trans('app.email_template');

        return [
            'rules' => $_rules,
            'attributeNames' => $setAttributeNames
        ];
    }

    /**
     * @return mixed
     */
    public function all() {
        return $this->emailTemplate->orderBy('created_at', 'DESC')->where('is_published', 1)->get();
    }

    /**
     * @param $limit
     * @return mixed
     */
    public function getLastEmailTemplate($limit) {

        return $this->emailTemplate->orderBy('created_at', 'desc')->take($limit)->offset(0)->get();
    }

    /**
     * @return mixed
     */
    public function lists() {

        //return $this->emailTemplate->get()->lists('title', 'id');
        return $this->emailTemplate->all()->lists('title', 'id');
    }

    /**
     * Get paginated emailTemplates
     *
     * @param int $page Number of emailTemplates per page
     * @param int $limit Results per page
     * @param boolean $all Show published or all
     * @return StdClass Object with $items and $totalItems for pagination
     */
    public function paginate($page = 1, $limit = 10, $all = false) {

        $result = new \StdClass;
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();

        //$query = $this->emailTemplate->with('tags')->orderBy('created_at', 'DESC');
        $query = $this->emailTemplate->with(['emailTemplateModule'])->orderBy('created_at', 'DESC');

        if(!$all) {
            $query->where('is_published', 1);
        }

        $emailTemplates = $query->skip($limit * ($page - 1))->take($limit)->get();

        $result->totalItems = $this->totalEmailTemplates($all);
        $result->items = $emailTemplates->all();
        return $result;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id) {
        return $this->emailTemplate->with(['emailTemplateModule'])->find($id);
        //return $this->emailTemplate->with(['tags', 'category'])->findOrFail($id);
    }

    /**
     * @param $slug
     * @return mixed
     */
    /*public function getBySlug($slug) {
        //return $this->emailTemplate->with(['tags', 'category'])->where('slug', $slug)->first();
        return $this->emailTemplate->with(['category'])->where('slug', $slug)->first();
    }*/
     public function getBySlug($slug, $isPublished = false) {
        if($isPublished === true)
           return $this->emailTemplate->select('emailTemplates.id', 'emailTemplate_translations.slug')
            ->join('emailTemplate_translations', 'emailTemplates.id', '=', 'emailTemplate_translations.emailTemplate_id')
            ->where('slug', $slug)->where('is_published', true)->firstOrFail();

        return $this->emailTemplate->select('emailTemplates.id', 'emailTemplate_translations.slug')
            ->join('emailTemplate_translations', 'emailTemplates.id', '=', 'emailTemplate_translations.emailTemplate_id')
            ->where('slug', $slug)->firstOrFail();
    }

    /**
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function create($attributes) {
        $t_attributes = array();
        //$t_attributes['email_template_module_id'] = $attributes['email_template_module'];
        //dd($t_attributes);
        if($this->isValid($attributes)) {
            //foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes += [ //[$locale] = [
                    'email_template_module_id' => $attributes['email_template_module'],
                    'subject' => $attributes['subject'],//[$locale],
                    'body' => $attributes['body']//[$locale]
                ];
            //}
            $this->emailTemplate->fill($t_attributes)->save();

            return true;
        }
        throw new ValidationException('Email template validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function update($id, $attributes) {
        $t_attributes = array();
        //$t_attributes['email_template_module_id'] = $attributes['email_template_module'];
        if($this->isValid($attributes)) {
            $this->emailTemplate = $this->find($id);
            //foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes=[//[$locale] = [
                    'email_template_module_id' => $attributes['email_template_module'],
                    'subject' => $attributes['subject'],//[$locale],
                    'body' => $attributes['body'],//[$locale]
                ];
            // /}
            $this->emailTemplate->fill($t_attributes)->save();

            return true;
        }


        throw new ValidationException('EmailTemplate validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @return mixed|void
     */
    public function delete($id) {

        $emailTemplate = $this->emailTemplate->find($id);
        //$emailTemplate->tags()->detach();
        if($emailTemplate)
            $emailTemplate->delete();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function togglePublish($id) {

        $emailTemplate = $this->emailTemplate->find($id);

        $emailTemplate->is_published = ($emailTemplate->is_published) ? false : true;
        $emailTemplate->save();

        return Response::json(array('result' => 'success', 'changed' => ($emailTemplate->is_published) ? 1 : 0));
    }

    /**
     * @param $id
     * @return string
     */
    function getUrl($id) {

        $emailTemplate = $this->emailTemplate->findOrFail($id);
        return url('emailTemplate/' . $id . '/' . $emailTemplate->slug, $parameters = array(), $secure = null);
    }

    /**
     * Get total emailTemplate count
     * @param bool $all
     * @return mixed
     */
    protected function totalEmailTemplates($all = false) {

        if(!$all) {
            return $this->emailTemplate->where('is_published', 1)->count();
        }

        return $this->emailTemplate->count();
    }
}
