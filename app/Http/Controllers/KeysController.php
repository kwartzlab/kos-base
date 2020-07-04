<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KeysController extends Controller
{

   // main json parsing function - redirects to internal procs and outputs result
   public function index(Request $request) {

      if ($request->has('status')) {
         $status = $request->input('status');
      } else {
         $status = NULL;
      }

      // make sure we have enough info to proceed
      if ((!$request->has('auth_key')) || (!$request->has('endpoint')) ) {
         return response()->json([
            'code' => '400',
            'text' => 'Bad Request',
            'timestamp' => date('Y-m-d H:i:s'),
         ]);
      }

      $gatekeeper = $this->process_auth($request->input('auth_key'), $status, $request->ip());

      // only continue if we have a gatekeeper object...
      if ($gatekeeper != NULL) {

         $endpoint = $request->input('endpoint');
         $payload = json_decode($request->input('payload'), true);

         $response = NULL;
         switch ($endpoint) {
            case 'get_keys':$response = $this->getkeys($payload,$gatekeeper);break;
            case 'send_auths':$response = $this->sendauths($payload,$gatekeeper);break;
            case 'ping':$response = $this->sendping();break;
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

   // authenticates the key sent by the gatekeeper and updates ip address
   private function process_auth($auth_key,$status_json,$ip_address) {

      if ($auth_key != NULL) {
         $gatekeeper = \App\Gatekeeper::where('auth_key',$auth_key)->where('status','enabled')->first();
         if ($gatekeeper != NULL) {
            $gatekeeper->last_seen = now();
            $gatekeeper->ip_address = $ip_address;
            $gatekeeper->save();

            // update status record (create if doesn't exist)
            $new_status = json_decode($status_json);

            if (isset($new_status->status)) {
               $status = $gatekeeper->current_status()->first();

               // if no record, create one
               if ($status == NULL) {
                  $status = new \App\GatekeeperStatus();
               }

               // verify more parameters if tool is in use
               if ($new_status->status == 'inuse') {
                  if ((isset($new_status->user_lock_in)) && (isset($new_status->user_rfid))) {
                     // convert key into user id
                     $user_key = \App\Key::where('rfid', $new_status->user_rfid)->first();

                     if ($user_key == NULL) {
                        // unknown user key - should not happen so return 403
                        return NULL;
                     }
                     $status->user_id = $user_key->user()->value('id');
                     $status->lock_in = $new_status->user_lock_in;
                  } else {
                     return NULL;
                  }
               }

               if (isset($new_status->status_text)) {
                  $status->status_text = $new_status->status_text;
               }

               $status->gatekeeper_id = $gatekeeper->id;
               $status->status = $new_status->status;
               $status->last_seen = now();
               $status->ip_address = $ip_address;
               $status->save();

               return $gatekeeper;
            } else {
               return $gatekeeper;
            }
         } else {
            return NULL;
         }
      } else {
         return NULL;
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


   // accept ping from gatekeeper (status is updated during authentication)
   public function sendping() {
      return $this->build_response('0','PING? PONG!');
   }


   // returns all active key IDs (used by gatekeeper to sync to it's local database)
   // only sends the keys authorized for that gatekeeper - default gatekeepers get all keys
   public function getkeys($payload,$gatekeeper) {

         $key_list = array();

         if ($gatekeeper->is_default == 0) {
            // non-default gatekeeper -- only get authorized keys
            foreach(\App\User::where('status','active')->get() as $user) {
               if ($gatekeeper->shared_auth != 0) {
                  $gatekeeper_id = $gatekeeper->shared_auth;
               } else {
                  $gatekeeper_id = $gatekeeper->id;
               }
               
               if (($user->is_authorized($gatekeeper_id)) && (!$user->flags->contains('flag', 'keys_disabled'))) {
                  foreach ($user->keys as $key) {
                     $key_list[] = $key->rfid;
                  }	
               }

            }
         } else {
            // default gatekeeper - get all active users and their keys
            foreach(\App\User::where('status','active')->get() as $user) {
               if (!$user->flags->contains('flag', 'keys_disabled')) {
                  foreach ($user->keys as $key) {
                     $key_list[] = $key->rfid;
                  }	
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

         if ($key != NULL) {
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
