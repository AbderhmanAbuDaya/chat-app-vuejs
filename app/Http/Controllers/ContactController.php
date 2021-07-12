<?php

namespace App\Http\Controllers;

use App\Events\NewMessage;
use App\Message;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{

    public function get(){
        $contacts=User::where('id','!=',auth()->id())->get();

        // get a collection of items where sender_id is the user who sent us a message
        // and messages_count is the number of unread messages we have from him
        $unreadIds = Message::select(\DB::raw('`from` as sender_id, count(`from`) as messages_count'))
            ->where('to', auth()->id())
            ->where('read', false)
            ->groupBy('from')
            ->get();


        // add an unread key to each contact with the count of unread messages
        $contacts = $contacts->map(function($contact) use ($unreadIds) {
            $contactUnread = $unreadIds->where('sender_id', $contact->id)->first();

            $contact->unread = $contactUnread ? $contactUnread->messages_count : 0;

            return $contact;
        });

        return response()->json($contacts);
    }

    public function getMessagesFor($id){
        $messages=Message::where('to',$id)->orWhere('from',$id)->get();
        $messages=Message::where(function ($q) use ($id){
            $q->where('to',auth()->id());
            $q->where('from',$id);
        })->orWhere(function ($q) use ($id){
            $q->where('from',auth()->id());
            $q->where('to',$id);
        })->get();
        return response()->json($messages);
    }

    public function send(Request $request){
      $message=  Message::create([
            'from'=>auth()->id(),
            'to'=>$request->contact_id,
            'text'=>$request->text
        ]);
       broadcast(new NewMessage($message));
        return response()->json($message);
    }

}
