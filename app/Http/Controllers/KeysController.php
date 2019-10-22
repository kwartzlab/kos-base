<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KeysController extends Controller
{

	// main json parsing function - redirects to internal procs and outputs result
	public function index(Request $request) {

		$gatekeeper = $this->process_auth($request->input('auth_key'));

		// only continue if we have a gatekeeper object...
		if ($gatekeeper != NULL) {

			$endpoint = $request->input('endpoint');
			$payload = json_decode($request->input('payload'), true);

			$response = NULL;
			switch ($endpoint) {
				case 'get_keys':$response = $this->getkeys($payload,$gatekeeper);break;
				//case 'get_user':$response = $this->get_user($request->input('payload'));break;
				//case 'get_all_users':$response = $this->get_all_users($request->input('payload'));break;
				case 'send_auths':$response = $this->sendauths($payload,$gatekeeper);break;
			}

			if ($response != NULL) {
				return response()->json($response);
			} else {
				return response()->json([
					'code' => '404',
					'text' => 'Not Found',
					'timestamp' => date('Y-m-d H:i:s'),
				]);
			}


		} else {
			return response()->json([
				'code' => '403',
				'text' => 'Unauthorized: ' . $request->input('auth_key'),
				'timestamp' => date('Y-m-d H:i:s')
			]);
		}

	}

	// authenticates the key sent by the gatekeeper
	private function process_auth($auth_key) {

		if ($auth_key != NULL) {
			$result = \App\Gatekeeper::where('auth_key',$auth_key)->where('status','enabled')->get()->first();
		} else {
			$result = NULL;
		}

		if (count($result) === 0) {
			return NULL;
		} else {
			return $result;
		}

	}

	// Formats a response with required parameters
	private function build_response($code,$text,$payload = NULL) {

		$response_array = array(
			'code' => $code,
			'text' => $text,
			'timestamp' => date('Y-m-d H:i:s')
		);

		if ($payload != NULL) {
			$response_array['payload'] = $payload;
		}

		return $response_array;

	}


	# returns a user record
	private function get_user($payload) {
	
		$key = \App\Key::where('rfid', $payload['rfid'])->first();

		$user = \App\User::find($key->user_id);

		$user_rec = array(
			'name' => $user->name,
			'email' => $user->email,
			'status' => $user->status,
			'member_id' => $user->member_id,
			'acl' => $user->acl,
			);

		return array(
			'code' => '0',
			'text' => 'OK',
			'timestamp' => date('Y-m-d H:i:s'),
			'payload' => $user_rec
			);

	}

	// returns a basic list of all active users
	private function get_all_users($payload) {

		$user_list = array();
		
		if ($payload['limit'] != NULL) { $max = $payload['limit']; } else { $max = 0; }
		if ($payload['offset'] != NULL) { $offset = $payload['offset']; } else { $offset = 0; }

		if ($max>0) {
				if ($offset>0) {
					$users = \App\User::limit($max)->offset($offset)->orderby('name')->where('status','active')->get();
				} else {
					$users = \App\User::limit($max)->orderby('name')->where('status','active')->get();
				}	
		} else {
				if ($offset>0) {
					$users = \App\User::offset($offset)->orderby('name')->where('status','active')->get();
				} else {
					$users = \App\User::orderby('name')->where('status','active')->get();
				}	

		}

		foreach($users as $user) {
			$user_list[] = [
				'name' => $user->name,
				'id' => $user->id
			];

		}

		return array(
			'code' => '0',
			'text' => 'OK',
			'timestamp' => date('Y-m-d H:i:s'),
			'payload' => $user_list
			);

	}

	// returns all active key IDs (used by gatekeeper to sync to it's local database)
	// only sends the keys authorized for that gatekeeper - default gatekeepers get all keys
	public function getkeys($payload,$gatekeeper) {

			$key_list = array();

			if ($gatekeeper->is_default == 0) {
				// non-default gatekeeper -- only get authorized keys
				foreach(\App\User::where('status','active')->get() as $user) {

					if ($user->is_authorized($gatekeeper->id)) {
						foreach ($user->keys as $key) {
							$key_list[] = $key->rfid;
						}	
					}

				}
			} else {
				// default gatekeeper - get all active users and their keys
				foreach(\App\User::where('status','active')->get() as $user) {

					foreach ($user->keys as $key) {
						$key_list[] = $key->rfid;
					}	
				}
			}

			return $this->build_response('0','OK',$key_list);
	}

	# receives authentications from gatekeeper
	public function sendauths($payload,$gatekeeper) {

		$payload_array = array();
		foreach ($payload as $row) {
			// find key in database

			$key = \App\Key::where('rfid', $row['rfid'])->first();

			if (count($key) > 0) {
				$key_id = $key->id;
				$user_id = $key->user_id;
			} else {
				$key_id = 0;
				$user_id = 0;
			}

	        $authentication = new \App\Authentication([
	            'gatekeeper_id' => $gatekeeper->id,
	            'rfid' => $row['rfid'],
	            'result' => $row['result'],
	            'meta_data' => $row['metadata'],
	            'key_id' => $key_id,
	            'user_id' => $user_id,
	            'lock_in' => $row['lock_in'],
	            'lock_out' => $row['lock_out'],
	            'created_at' => $row['created_at'],
	            'updated_at' => $row['created_at']
	            ]);

	        $payload_array[] = $authentication;

	        $authentication->save(['timestamps' => false]);

		}

		return $this->build_response('0','OK');

	}


}
