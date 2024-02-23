<?php

namespace App\CompetitionCommands\InviteUser;

use App\CompetitionCommands\InviteUser\Interfaces\Invitable;
use App\CompetitionCommands\InviteUser\Interfaces\InviteStrategy;
use App\CompetitionCommands\InviteUser\Traits\UserInviter;

class InviteRegisteredUserStrategy implements InviteStrategy, Invitable {

    use UserInviter;

    protected $competition;
    protected $loggedUser;


    public function __construct($competition, $loggedUser) {
        $this->competition = $competition;
        $this->loggedUser = $loggedUser;
    }

    public function invite($existingUser) {

        return   $this->inviteUser($existingUser, $this->competition, $this->loggedUser);

    }



}