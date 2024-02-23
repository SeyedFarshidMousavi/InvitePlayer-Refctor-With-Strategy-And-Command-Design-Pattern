<?php

namespace App\CompetitionCommands\InviteUser;

use App\Rules\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class InviteRequestValidator {

    protected Request $request;

    public function __construct( $request,$user) {
        $this->request = $request;
        $this->user = $user; // inviter

    }

    public function validate() {
        $rules = [
            'type' => ['required', 'string', Rule::in(['registered', 'unregistered'])],
            'invite_type' => ['required_if:type,registered', 'string', Rule::in(['User'])],
            'invite_to' => ['required_if:type,registered', 'string'],
            'name' => ['required_if:type,unregistered', 'string'],
        ];

        if ($this->request->get('type') === 'registered') {
            $rules['invite_to'][] = 'exists:users,username';
        }

        if ($this->request->get('type') === 'unregistered') {
            $rules['phone'] = ['required', 'string', 'max:16', 'min:11', new PhoneNumber()];
        }


        $validator = Validator::make($this->request->all(), $rules);
        if ($validator->fails()) {
            return $validator->errors()->first();
        }

        $inviteeUsername = $this->request->get('invite_to');
        if ($inviteeUsername && $this->user->username === $inviteeUsername) {
            return 'شما نمی‌توانید خودتان را دعوت کنید';
        }


        return true;
    }
}
