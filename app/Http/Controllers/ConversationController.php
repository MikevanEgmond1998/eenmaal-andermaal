<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Conversation;
use App\Message;
use App\DB;
use function App\Helpers\countLengthNewlinesOneCharacter;
use function App\Helpers\textAreaNewlinesToSimpleNewline;

class ConversationController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('check.user');
	}

	public function list(Request $request)
	{
		$userId = $request->session()->get('user')->id;
		$convoMessages = Conversation::getAllMessagesForUser($userId);

		$convosArray = [];
		foreach ($convoMessages as $convo) {
			$convosArray[$convo->auction_conversation_id]['auction_id'] = $convo->auction_id;
			$convosArray[$convo->auction_conversation_id]['auction_title'] = $convo->title;
			$convosArray[$convo->auction_conversation_id]['conversation_id'] = $convo->auction_conversation_id;

			if (!isset($convosArray[$convo->auction_conversation_id]['messages'])) {
				$convosArray[$convo->auction_conversation_id]['messages'] = [];
			}

			array_push($convosArray[$convo->auction_conversation_id]['messages'], (object) [
				'message' => $convo->message,
				'user_id' => $convo->user_id,
				'created_at' => $convo->created_at
			]);
		}

		$convos = [];
		foreach ($convosArray as $convo) {
			$obj = (object) [];
			foreach ($convo as $key => $value) {
				$obj->$key = $value;
			}

			array_push($convos, $obj);
		}

		usort($convos, function ($a, $b) {
			$aLast = end($a->messages)->created_at;
			$bLast = end($b->messages)->created_at;
			return strtotime($bLast) - strtotime($aLast);
		});

		return view('messages')->with(['convos' => $convos]);
	}

	public function send(Request $request)
	{
		$this->validate($request, array(
			'message' => ['required', 'string'],
		));
        if (countLengthNewlinesOneCharacter($request->get("message")) > 250)
            return redirect()->back()->withInput($request->all())->withErrors(["message" => "Bericht mag niet uit meer dan 250 tekens bestaan."]);

		$auctionId = $request->auctionId;
		$userId = $request->session()->get('user')->id;

		$convId = 0;
		if ($request->has('conversationId')) {
			$convId = $request->get('conversationId');
		} else {
			$convId = Conversation::getOrCreate($auctionId, $userId)->id;
		}

		$message = DB::insertOne(
			"INSERT INTO auction_messages (auction_conversation_id, user_id, message)
			VALUES (" . $convId . ", " . $userId . ", :message)",
			[
				'message' => textAreaNewlinesToSimpleNewline($request->message)
			]
		);

		if (!$message) {
			return redirect()->back()->withInput($request->all())->withErrors(["message" => "Bericht is niet verzonden!"]);
		}
		return redirect()->route("messages",["conversation" => $convId])->with('message', 'Uw bericht is verzonden!');
	}
}
