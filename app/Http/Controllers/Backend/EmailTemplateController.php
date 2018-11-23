<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\EmailTemplate\EmailTemplateInterface;
//use App\Repositories\EmailTemplateModule\EmailTemplateModuleInterface;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Repositories\EmailTemplate\EmailTemplateRepository as EmailTemplate;
//use App\Repositories\EmailTemplateModule\EmailTemplateModuleRepository as Category;
use App\Exceptions\Validation\ValidationException;
use Config;

use App\Models\EmailTemplateModule;

class EmailTemplateController extends Controller {

	protected $emailTemplate;
    protected $emailTemplateModule;

    public function __construct(EmailTemplateInterface $emailTemplate) {

        View::share('active', 'blog');
        $this->emailTemplate = $emailTemplate;
        $this->emailTemplateModule = new EmailTemplateModule;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $page = Input::get('page', 1);
        $perPage = config_db_cached('settings::backend_per_page');
        $pagiData = $this->emailTemplate->paginate($page, $perPage, true);

        $emailTemplates = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);

        $emailTemplates->setPath("");

        return view('backend.emailTemplate.index', compact('emailTemplates'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create() {
        $emailTemplateModule_options[''] = '-';
        $emailTemplateModule_options += $this->emailTemplateModule->lists('name', 'id');

        $emailTemplateModules_raw = $this->emailTemplateModule->get();
        $emailTemplateModules = [];
        foreach ($emailTemplateModules_raw as $emailTemplateModule) {
            $emailTemplateModules[$emailTemplateModule->id]['name'] = $emailTemplateModule->name;
            $variables = [];
            foreach(explode(',', $emailTemplateModule->available_variables) as $variable){
                $variables[] = '<li>'.$variable.'</li>';
            }
            $emailTemplateModules[$emailTemplateModule->id]['available_variables'] = $variables;
        }

        $emailTemplateModules = rawurlencode(json_encode($emailTemplateModules));
        //dd($emailTemplateModules);
        return view('backend.emailTemplate.create', compact('emailTemplateModule_options', 'emailTemplateModules'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        try {
            $this->emailTemplate->create(Input::all());
            Notification::success( trans('app.data_added') );
            return langRedirectRoute('admin.settings.email-template.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.settings.email-template.create')->withInput()->withErrors($e->getErrors());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id) {

        $emailTemplate = $this->emailTemplate->find($id);
        return view('backend.emailTemplate.show', compact('emailTemplate'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id) {

        $emailTemplate = $this->emailTemplate->find($id);
        $emailTemplateModule_options[''] = '-';
        $emailTemplateModule_options += $this->emailTemplateModule->lists('name', 'id');

        $emailTemplateModules_raw = $this->emailTemplateModule->get();
        $emailTemplateModules = [];
        foreach ($emailTemplateModules_raw as $emailTemplateModule) {
            $emailTemplateModules[$emailTemplateModule->id]['name'] = $emailTemplateModule->name;
            $variables = [];
            foreach(explode(',', $emailTemplateModule->available_variables) as $variable){
                $variables[] = '<li>'.$variable.'</li>';
            }
            $emailTemplateModules[$emailTemplateModule->id]['available_variables'] = $variables;
        }

        $emailTemplateModules = rawurlencode(json_encode($emailTemplateModules));
        return view('backend.emailTemplate.edit', compact('emailTemplate','emailTemplateModule_options', 'emailTemplateModules'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id) {
        try {
            $this->emailTemplate->update($id, Input::all());
            Notification::success( trans('app.data_updated') );
            return langRedirectRoute('admin.settings.email-template.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.settings.email-template.edit', [ 'id' => $id ] )->withInput()->withErrors($e->getErrors());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id) {

        $this->emailTemplate->delete($id);
        Notification::success( trans('app.data_deleted') );
        return langRedirectRoute('admin.settings.email-template.index');
    }

    public function confirmDestroy($id) {

        $emailTemplate = $this->emailTemplate->find($id);
        return view('backend.emailTemplate.confirm-destroy', compact('emailTemplate'));
    }

    public function togglePublish($id) {

        return $this->emailTemplate->togglePublish($id);
    }

}