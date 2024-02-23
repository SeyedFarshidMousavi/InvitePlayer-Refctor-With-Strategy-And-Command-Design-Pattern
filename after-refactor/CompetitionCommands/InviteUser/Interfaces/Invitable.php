<?php

namespace App\CompetitionCommands\InviteUser\Interfaces;


interface Invitable {
    public function inviteUser($user, $competition, $loggedUser);
}
