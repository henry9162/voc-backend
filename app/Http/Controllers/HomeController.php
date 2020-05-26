<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use PhpJunior\LaravelVideoChat\Facades\Chat;
use PhpJunior\LaravelVideoChat\Models\File\File;
use App\ChatRegistrationApi;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->has('isAdmin') && $request->has('user') ){

            $isAdmin = $request->get('isAdmin');
            $nyscUser = $request->get('user');
            $user = User::where('user_id', $nyscUser)->first();
            if ($user) {
                $this->guard()->login($user);
            } else { 
                $this->authenticateNysc($isAdmin, $nyscUser);
            }
        } 

        $groups = Chat::getAllGroupConversations();
        $threads = Chat::getAllConversations();

        return view('home')->with([
            'threads' => $threads,
            'groups'  => $groups
        ]);
    }

    public function chat($id)
    {
        $conversation = Chat::getConversationMessageById($id);

        return view('chat')->with([
            'conversation' => $conversation
        ]);
    }

    public function groupChat($id)
    {
        $conversation = Chat::getGroupConversationMessageById($id);

        return view('group_chat')->with([
            'conversation' => $conversation
        ]);
    }

    public function send(Request $request)
    {
        Chat::sendConversationMessage($request->input('conversationId'), $request->input('text'));
    }

    public function groupSend(Request $request)
    {
        Chat::sendGroupConversationMessage($request->input('groupConversationId'), $request->input('text'));
    }

    public function sendFilesInConversation(Request $request)
    {
        Chat::sendFilesInConversation($request->input('conversationId') , $request->file('files'));
    }

    public function sendFilesInGroupConversation(Request $request)
    {
        Chat::sendFilesInGroupConversation($request->input('groupConversationId') , $request->file('files'));
    }

    protected function authenticateNysc(string $isAdmin, string $nyscUser)
    {
        $chatInfo = new ChatRegistrationApi($isAdmin, $nyscUser);
        $chatInfo->get_loggedin_user_details();
        $user_details = $chatInfo->user_data;
        event(new Registered($user = $this->createEngageUser($user_details)));
        $this->guard()->login($user);
    }

    protected function createEngageUser(array $data)
    {
        return User::forceCreate([
            'first_name' => $data['data'][0]['firstname'],
            // 'last_name' => $data['data']['lastname'],
            'email' => $data['data'][0]['emailAddress'],
            'password' => bcrypt('password'),
            'is_admin' => $data['data'][0]['isAdmin'],
            'user_id' => $data['data'][0]['userId']
        ]);
    }

    protected function guard()
    {
        return Auth::guard();
    }
}
