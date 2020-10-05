<?php

namespace App\Traits;

   /*
      Takes care of member status changes when they need to happen
      Used in membership register and nightly taskss
   */

trait UserStatusTrait {


   // runs when an applicant becomes an active user
   protected static function new_member_induction($user) {
      // new member stuff - welcome emails, setup accounts, etc


   }

   // runs when a member goes from active/hiatus to withdrawn/withdrawn (abandoned)/withdrawn (terminated)
   // outgoing notifications can be controlled (in case of termination)
   protected static function member_withdrawal($user) {
      // exit interview, slack removal, etc

      // honor send_notifications from config file

   }

   // checks a user's current status to see if it needs to be modified from a user status change
   protected static function check_current_userstatus($user = NULL) {
      if ($user != NULL) {

         // grab status that would be in effect now
         $current_status = $user->current_status()->get()->first();

         $status_result = NULL;

         // if there are no statuses for user at all, mark user as unknown and force change
         if ($current_status == NULL) {
            $user->status = 'unknown';
            $user->save();
            $status_result = '[' . $user->get_name() . '] No status updates exist for this user, changed to ' . $user->status;
         } else {
            // update user if status field is inconsistent with computed current_status
            if ($user->status != $current_status->status) {
               $prev_status = $user->status;

               $extra_tasks = NULL;
               // applicant becoming a member
               if (($user->status == 'applicant') && ($current_status->status == 'active')) {
                  //$this->new_member_induction($user);
                  $extra_tasks = '(New Member Induction)';
                  $user->date_admitted = $current_status->created_at;
               }
               if (($current_status->status == 'inactive') || ($current_status->status == 'inactive-abandoned') || ($current_status->status == 'terminated') ) {
                  $user->date_withdrawn = $current_status->created_at;
               }

               $user->status = $current_status->status;
               $user->save();
               $status_result = '[' . $user->get_name() . '] status changed from ' . $prev_status . ' to ' . $user->status . ' ' . $extra_tasks;
            }
         }
         return $status_result;

      }
      return NULL;
   }
}