<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Input;
use Notification;
use Redirect;
use App\Models\EmailTemplateModule;
use App\Models\EmailTemplate;
use App\Models\EmailTemplateModuleTemplate;
use Entrust;

class EmailTemplateMappingController extends Controller {

	protected $emailTemplateModule;

    public function __construct() {
        $this->emailTemplateModule = new EmailTemplateModule;
    }

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		//
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */

	public function create()
	{

		$attr = [
                'title' => trans('app.email_template_mapping')
            ];
        if(!Entrust::can(['mapping_email_template'])){
            $attr += [
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }

		$modules = $this->emailTemplateModule->with(['emailTemplates'])->get(); //dd($modules[0]->emailTemplates);
		$module_template_raw = EmailTemplateModuleTemplate::all();
		$module_template = [];
		foreach($module_template_raw as $module_template_raw){
			$module_template[$module_template_raw->email_template_module_id]['cc'] = $module_template_raw->cc;
			$module_template[$module_template_raw->email_template_module_id]['email_template_id'] = $module_template_raw->email_template_id;
		}
		return view('backend.emailTemplate.mapping', compact('modules', 'module_template'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		try {
			$cces = Input::get('cc');
			$emailTemplates = Input::get('email_template');
			$new_data = [];
			foreach($cces as $module_id => $cc){
				$new_data[$module_id]['email_template_module_id'] = $module_id;
				$new_data[$module_id]['email_template_id'] = $emailTemplates[$module_id];
				$new_data[$module_id]['cc'] = $cc;
			}
			EmailTemplateModuleTemplate::truncate();
			EmailTemplateModuleTemplate::insert($new_data);
            
            Notification::success( trans('app.email_mapping_updated'));
            return Redirect::route('admin.settings.email-mapping');
        } catch (ValidationException $e) {
            return Redirect::route('admin.settings.email-mapping')->withInput()->withErrors($e->getErrors());
        }
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

}
