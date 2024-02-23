<?php

namespace App\CompetitionCommands\InviteUser\Interfaces;

interface InviteStrategy
{
    public function invite($user);

}