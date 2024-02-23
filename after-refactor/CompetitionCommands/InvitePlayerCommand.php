<?php

namespace App\CompetitionCommands;

use App\CompetitionCommands\InviteUser\CompetitionValidator;
use App\CompetitionCommands\InviteUser\InviteRegisteredUserStrategy;
use App\CompetitionCommands\InviteUser\InviteRequestValidator;
use App\CompetitionCommands\InviteUser\InviteUnregisteredUserStrategy;
use App\Interfaces\Command;
use App\Models\User;
use App\Services\OTPService;

class InvitePlayerCommand implements Command {
    protected  $OTPService;
    protected $request;
    protected $competition;
    protected $loggedUser;



    public function __construct(OTPService $OTPService) {
        $this->OTPService = $OTPService;
    }

    protected function getStrategy() {

        switch ($this->request->get('type')) {
            case "registered":
                return new InviteRegisteredUserStrategy($this->competition,$this->loggedUser);
            case "unregistered":
                return new InviteUnregisteredUserStrategy($this->OTPService,$this->request,$this->competition, $this->loggedUser);
            default:
                return null;
        }
    }

    public function execute( $request, $competition) {

        $this->competition =$competition;
        $this->request =$request;
        $this->setLoggedUser();



        $validationResult =  (new InviteRequestValidator($request, $this->loggedUser))->validate();
        if ($validationResult !== true) {
            return ['ok' => false, 'msg' => $validationResult];
        }

        //Check Capacity for Invite
        $competitionValidationResult =  (new CompetitionValidator($request, $this->loggedUser,$competition))->validate();
        if ($competitionValidationResult !== true) {
            return ['ok' => false, 'msg' => $competitionValidationResult];
        }


        $strategy = $this->getStrategy();

        if ($strategy === null) {
            return ['ok' => false, 'msg' => "نوع دعوت نامعتبر است"];
        }


        $user = $this->getUserFromRequest();

        return $strategy->invite($user);

    }

    private function getUserFromRequest()
    {
        switch ($this->request->get('type')) {
            case "registered":
                return User::firstWhere(['username' =>  $this->request->get('invite_to')]);
            case "unregistered":
                return   User::where('phone', $this->request->get('phone'))->first();

        }

    }

    private function setLoggedUser()
    {

        $this->loggedUser = auth()->user();
    }


}