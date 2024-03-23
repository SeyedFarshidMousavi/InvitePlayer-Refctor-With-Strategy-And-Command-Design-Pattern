<?php

namespace App\Competition\Commands;

use App\Interfaces\Command;
use App\Services\CompetitionService;
use App\Services\OTPService;
use Exception;

class CommandHandler {
    protected $commands = [];
    protected $OTPService;
    protected CompetitionService $competitionService;


    public function __construct() {
        $this->registerCommands();

    }

    public function register($action, Command $command) {
        $this->commands[$action] = $command;
    }

    protected function registerCommands() {

        $this->commands['cancel']           = app()->make(CancelCompetitionCommand::class);
        $this->commands['start']            = app()->make(StartCompetitionCommand::class);
        $this->commands['shuffle']          = app()->make(ShuffleCompetitionCommand::class);
        $this->commands['restrict']         = app()->make(RestrictCompetitionCommand::class);
        $this->commands['unrestrict']       = app()->make(UnRestrictCompetitionCommand::class);
        $this->commands['cancel-invite']    = app()->make(CancelInviteCompetitionCommand::class);
        $this->commands['set-time']         = app()->make(SetTimeCompetitionCommand::class);
        $this->commands['set-result']       = app()->make(SetResultCompetitionCommand::class);
        $this->commands['upload-media']     = app()->make(UploadMediaCompetitionCommand::class);
        $this->commands['remove-media']     = app()->make(RemoveMediaCompetitionCommand::class);
        $this->commands['media-info']       = app()->make(MediaInfoCompetitionCommand::class);
        $this->commands['new-step']         = app()->make(NewStepCompetitionCommand::class);



        $this->commands['update-settings']  = app()->make(UploadSettingsCompetitionCommand::class);
        $this->commands['invite']           = app()->make(InvitePlayerCommand::class);
        $this->commands['makeGroups']       = app()->make(MakeGroupsCompetitionCommand::class);


    }

    public function handle($action, $request, $competition) {

        if (!isset($this->commands[$action])) {
            return ['ok' => false, 'msg' => 'اکشن مورد نظر یافت نشد'];
        }
         return  $this->commands[$action]->execute($request, $competition);


    }

}
