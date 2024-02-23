<?php

namespace App\CompetitionCommands;

use App\Interfaces\Command;
use App\Services\OTPService;

class CommandHandler {
    protected $commands = [];
    protected $OTPService;


    public function __construct(OTPService $OTPService) {
        $this->OTPService = $OTPService;
        $this->registerCommands();
    }

    public function register($action, Command $command) {
        $this->commands[$action] = $command;
    }

    protected function registerCommands() {

        $this->commands['cancel']  = new CancelCompetitionCommand();
        $this->commands['start']  = new StartCompetitionCommand();
        $this->commands['shuffle']  = new ShuffleCompetitionCommand();
        $this->commands['restrict']  = new RestrictCompetitionCommand();
        $this->commands['unrestrict']  = new UnRestrictCompetitionCommand();
        $this->commands['cancel-invite']  = new CancelInviteCompetitionCommand();
        $this->commands['set-time']  = new SetTimeCompetitionCommand();
        $this->commands['set-result']  = new SetResultCompetitionCommand();
        $this->commands['upload-media']  = new UploadMediaCompetitionCommand();
        $this->commands['remove-media']  = new RemoveMediaCompetitionCommand();
        $this->commands['media-info']  = new MediaInfoCompetitionCommand();
        $this->commands['update-settings']  = new UploadSettingsCompetitionCommand();
        $this->commands['new-step']  = new NewStepCompetitionCommand();

        $this->commands['invite'] = new InvitePlayerCommand($this->OTPService);

    }

    public function handle($action, $request, $competition) {

        if (!isset($this->commands[$action])) {
            return ['ok' => false, 'msg' => 'اکشن مورد نظر یافت نشد'];
        }

        return $this->commands[$action]->execute($request, $competition);
    }







}
