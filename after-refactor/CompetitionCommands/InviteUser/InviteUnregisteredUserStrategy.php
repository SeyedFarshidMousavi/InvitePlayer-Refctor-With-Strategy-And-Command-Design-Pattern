<?php

namespace App\CompetitionCommands\InviteUser;

use App\CompetitionCommands\InviteUser\Interfaces\Invitable;
use App\CompetitionCommands\InviteUser\Interfaces\InviteStrategy;
use App\CompetitionCommands\InviteUser\Traits\UserInviter;
use App\Models\Unregistered;
use App\Services\OTPService;
use Illuminate\Support\Str;

class InviteUnregisteredUserStrategy implements InviteStrategy , Invitable  {

    use UserInviter;

    private OTPService $OTPService;
    private Co $competition;
    private $loggedUser;
    private $request;


    public function __construct($OTPService,$request,$competition, $loggedUser) {
        $this->OTPService = $OTPService;
        $this->competition = $competition;
        $this->loggedUser = $loggedUser;
        $this->request = $request;

    }

    public function invite($existingUser) {

        if ($existingUser) {
            return $this->inviteUser( $existingUser, $this->competition, $this->loggedUser);
        }


        $receiverItem = $this->request->get('phone');


        $otpType = 0; // Send
        $res =  $this->OTPService->setReceiverAddress($receiverItem)
            ->setDeviceType(1)
            ->setToken(generateNDigitRandomNumber(4))
            ->set_for($this->OTPService::REQUEST_FOR_INVITE_COMPETITION)
            ->setCallBackRouteName('dashboard.payments.check')
            ->set_otp_type($otpType)
            ->handel_OTP();
        if ($res) {
            // Store Unregistered User Info
            $user = Unregistered::firstOrCreate([
                'email' => Str::lower($this->request->get('phone')),
                'name' => $this->request->get('name'),
            ]);
            return ['ok' => true, 'msg' => 'شرکت کننده با موفقیت افزوده شد'];
        }

        return ['ok' => false, 'msg' => 'خطا در ارسال پیامک'];


    }


}