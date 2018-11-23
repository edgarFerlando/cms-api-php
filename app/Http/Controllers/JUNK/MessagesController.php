<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\User;
use Carbon\Carbon;
use Cmgmyr\Messenger\Models\Thread;
use Cmgmyr\Messenger\Models\Message;
use Cmgmyr\Messenger\Models\Participant;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;

use View;
use Validator;

class MessagesController extends Controller {

    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
        if(empty(Auth::user()->id)){
            return redirect(route(getLang().'.login'));
        }
        
		$currentUserId = Auth::user()->id;
        // All threads, ignore deleted/archived participants
        $threads = Thread::getAllLatest()->with('participants')->whereHas('participants', function($q) use ($currentUserId){
            $q->where('user_id', $currentUserId);
        })->get();

        $role_id = 8;
        $current_user = User::with(['roles'])->where('id', Auth::id())->whereHas('roles', function($q) use ($role_id){
            $q->where('id', $role_id);
        })->get();

        //$participants = Thread::->find()->get();
        //dd($threads);
        //$userMeta = userMeta(User::with('userMetas')->find(1)->get()->userMetas);
        // All threads that user is participating in
        // $threads = Thread::forUser($currentUserId)->latest('updated_at')->get();
        // All threads that user is participating in, with new messages
        // $threads = Thread::forUserWithNewMessages($currentUserId)->latest('updated_at')->get();
        return view('frontend.auth.messenger.index', compact('threads', 'currentUserId','current_user'))->with('user', $this->user);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
        if(empty(Auth::user()->id)){
            return redirect(route(getLang().'.login'));
        }
        $currentUserId = Auth::user()->id;
        // All threads, ignore deleted/archived participants
        $threads = Thread::getAllLatest()->with('participants')->whereHas('participants', function($q) use ($currentUserId){
            $q->where('user_id', $currentUserId);
        })->get();

        $participants = array();

        foreach ($threads as $thread) {
            foreach ($thread->participants as $key => $value) {
                $participants[$key] = $value->user_id;
            }
        }

		$role_id = 9;
		//$users = User::where('id', '!=', Auth::id())->get();
		$users = User::with(['roles'])->where('id', '!=', Auth::id())->whereHas('roles', function($q) use ($role_id){
			$q->where('id', $role_id);
		})->whereNotIn('id', $participants)->get();
        
        //dd($participants);
		//dd($users);
        return view('frontend.auth.messenger.create', compact('users'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$input = Input::all();

        $rules['subject'] = 'required';
        $rules['message'] = 'required';
        $rules['recipients'] = 'required|integer';

        $messages = [
            'recipients.required' => 'We need to know your destination send messages!',
            'recipients.integer' => 'account not found!'
        ];

        $recipients = array($input['recipients']);
        //dd($recipients);
        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails())
        { 
            return redirect(route('messages.create'))->withInput()->withErrors($validator->errors());
        }

        $recipients = array($input['recipients']);
        //dd($validator);

        $thread = Thread::create(
            [
                'subject' => $input['subject'],
            ]
        );
        // Message
        Message::create(
            [
                'thread_id' => $thread->id,
                'user_id'   => Auth::user()->id,
                'body'      => $input['message'],
            ]
        );
        // Sender
        Participant::create(
            [
                'thread_id' => $thread->id,
                'user_id'   => Auth::user()->id,
                'last_read' => new Carbon
            ]
        );
        // Recipients
        if (Input::has('recipients')) {
            $thread->addParticipants($recipients);
        }
        return redirect('messages');
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
        if(empty(Auth::user()->id)){
            return redirect(route(getLang().'.login'));
        }
        
		try {
            $thread = Thread::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            Session::flash('error_message', 'The thread with ID: ' . $id . ' was not found.');
            return redirect('messages');
        }
        // show current user in list if not a current participant
        // $users = User::whereNotIn('id', $thread->participantsUserIds())->get();
        // don't show the current user in list
        $userId = Auth::user()->id;
        //$users = User::whereNotIn('id', $thread->participantsUserIds($userId))->get();
        $thread->markAsRead($userId);
        return view('frontend.auth.messenger.show', compact('thread'));//, 'users'));
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
        $input = Input::all();

		try {
            $thread = Thread::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            Session::flash('error_message', 'The thread with ID: ' . $id . ' was not found.');
            return redirect('messages');
        }
        $thread->activateAllParticipants();
        // Message
        $rules['message'] = 'required';

        $validator = Validator::make($input, $rules);

        if ($validator->fails())
        { 
            return redirect('messages/' . $id)->withInput()->withErrors($validator->errors());
        }

        //dd($validator);
        Message::create(
            [
                'thread_id' => $thread->id,
                'user_id'   => Auth::id(),
                'body'      => Input::get('message'),
            ]
        );
        // Add replier as a participant
        $participant = Participant::firstOrCreate(
            [
                'thread_id' => $thread->id,
                'user_id'   => Auth::user()->id
            ]
        );
        $participant->last_read = new Carbon;
        $participant->save();
        // Recipients
        if (Input::has('recipients')) {
            $thread->addParticipants(Input::get('recipients'));
        }
        return redirect('messages/' . $id);
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
