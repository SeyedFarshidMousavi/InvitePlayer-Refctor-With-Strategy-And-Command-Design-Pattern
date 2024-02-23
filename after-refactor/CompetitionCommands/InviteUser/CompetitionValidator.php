<?php

namespace App\CompetitionCommands\InviteUser;

use App\Models\Competition;
use App\Models\User;
use Illuminate\Http\Request;

class CompetitionValidator {

    protected Competition $competition;
    protected Request $request;
    private User $loggedUser;

    public function __construct($request ,$loggedUser,$competition) {
        $this->competition = $competition;
        $this->request = $request;
        $this->loggedUser = $loggedUser;

    }

    public function validate() {
        if (!$this->competition->hasCapacity()) {
            return 'ظرفیت مسابقه تکمیل است';
        }

        if (!$this->competition->purchasable()) {
            return 'بازی شروع شده است';
        }

        $inviteeUsername = $this->request->get('invite_to');
        if ($inviteeUsername &&  $this->loggedUser->username === $inviteeUsername) {
            return 'شما نمی‌توانید خودتان را دعوت کنید';
        }


        return true;
    }
}
