<?php namespace App\Http\Controllers;

/*use Fully\Services\Mailer;
use Fully\Models\FormPost;
use Input;
use Validator;
use Redirect;*/

class ContactUsController extends Controller {
	public function getContact() {
        $transRoute = [ 
            'route' => 'contact',
            'attrs' => []
        ];

        $gmap['lat'] = config_db_cached('contact_us::gmap_lat');
        $gmap['lng'] = config_db_cached('contact_us::gmap_lng');
        return view('frontend.contact.show', compact('gmap', 'transRoute'));
    }

    /*protected $formPost;

    public function __construct(FormPost $formPost) {

        $this->formPost = $formPost;
    }

    public function getContact() {

        return view('frontend.contact.form');
    }

    public function postContact() {

        $formData = array(
            'sender_name_surname' => Input::get('sender_name_surname'),
            'sender_email'        => Input::get('sender_email'),
            'sender_phone_number' => Input::get('sender_phone_number'),
            'subject'             => Input::get('subject'),
            'post'                => Input::get('message')
        );

        $rules = array(
            'sender_name_surname' => 'required',
            'sender_email'        => 'required|email',
            'sender_phone_number' => 'required',
            'subject'             => 'required',
            'post'                => 'required'
        );

        $validation = Validator::make($formData, $rules);

        if($validation->fails()) {
            return Redirect::action('FormPostController@getContact')->withErrors($validation)->withInput();
        }

        $formPost = new FormPost();
        $formPost->sender_name_surname = $formData['sender_name_surname'];
        $formPost->sender_email = $formData['sender_email'];
        $formPost->sender_phone_number = $formData['sender_phone_number'];
        $formPost->subject = $formData['subject'];
        $formPost->message = $formData['post'];
        $formPost->lang = getLang();

        $formPost->save();

        return Redirect::action('FormPostController@getContact')->with('message', 'Success');
    }*/
}
