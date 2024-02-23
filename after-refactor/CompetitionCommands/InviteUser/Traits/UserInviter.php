<?php

namespace App\CompetitionCommands\InviteUser\Traits;

trait UserInviter
{



    public  function inviteUser($user, $competition, $loggedUser) {

        if($competition->hasPlayer($user)){
            return array(
                'ok' => false,
                'msg' => 'شرکت کننده در حال حاضر در مسابقه حضور دارد',
            );
        }

        $invited = (bool) $competition->inviteAction($user, $loggedUser);
        $results['ok'] = $invited;
        $results['msg'] = $invited ? 'دعوتنامه با موفقیت ارسال شد' : 'دعوتنامه ای قبلا برای کاربر ارسال شده است';
        return $results;
    }







}