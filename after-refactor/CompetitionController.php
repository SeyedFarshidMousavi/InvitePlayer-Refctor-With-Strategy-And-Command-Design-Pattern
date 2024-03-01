<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Competition;
use App\Models\Competition_logs;
use App\Models\Game;
use App\Models\Matches;
use App\Models\Participant;
use App\Models\Unregistered;
use App\Models\User;
use App\Notifications\CreateCompetition;
use App\Notifications\Invited;
use App\Notifications\Restricted;
use App\Notifications\StartCompetition;
use App\Notifications\UnRestricted;
use App\Rules\HTMLContent;
use App\Rules\Media\GalleryFile;
use App\Rules\Media\GalleryLimit;
use App\Rules\PhoneNumber;
use App\Rules\TimeStamps\AfterNow;
use App\Rules\TimeStamps\CompetitionTime;
use App\Rules\TimeStamps\MatchTime;
use App\Rules\Winner;
use App\Services\OTPService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;


class CompetitionController extends Controller
{
    private const COMPETITION_PER_PAGE = 10;


    /**
     * @var
     * @description Get Authenticated User
     */
    private $user;

    public function __construct(CommandHandler $commandHandler) {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            return $next($request);
        });
        $this->commandHandler = $commandHandler;

    }


    /**
     * @param Request $request
     * @param $competition_id
     * @description get some actions by tournament manager and handle it with some private functions
     */
    public function actionHandler(Request $request, $competition_id,OTPService $OTPService)
    {

        $competition = $this->getCompetition($competition_id);
        $action = $request->get('action');

        return $this->commandHandler->handle($action, $request, $competition);
    }

    private function invitePlayer(Request $request, $competition,OTPService $OTPService)
    {

        if (!$competition->hasCapacity()) {
            return array(
                'ok' => false,
                'msg' => 'ظرفیت مسابقه تکمیل است'
            );
        }
        else if (!$competition->purchasable()) {
            return array(
                'ok' => false,
                'msg' => 'بازی شروع شده است',
            );
        }
        $rules = array(
            'type'        => ['required', 'string', 'in:registered,unregistered'],
            'invite_type' => ['required_if:type,registered', 'string', 'in:User'],
            'invite_to'   => ['required_if:type,registered', 'string'],
            'name'        => ['required_if:type,unregistered', 'string'],
            'phone'       => ['required', 'string', 'max:16', 'min:11', new PhoneNumber()],
        );
        if ($request->get('invite_type') == 'User') {
            $rules['invite_to'][] = 'exists:users,username';
        }
        $validator = Validator::make($request->all(), $rules);
        $results = array('ok' => true);
        if ($validator->fails()) {
            $results = array(
                'ok' => false,
                'msg' => $validator->errors()->first()
            );
        } else {
            $type = $request->get('type');
            if ($type === 'registered') {
                $user = User::firstWhere(['username' => $request->get('invite_to')]);
                if (!$competition->hasPlayer($user)) {
                    //Registered Users

                    $results = $this->invite_user($user,$competition);

                } else {
                    $results['ok'] = false;
                    $results['msg'] = 'شرکت کننده در حال حاضر در مسابقه حضور دارد';
                }
            }
            elseif ($type == 'unregistered') {

                $existing_user_with_this_email = User::where('phone', $request->get('phone'))->first();
                if($existing_user_with_this_email){
                    $results = $this->invite_user($existing_user_with_this_email,$competition);
                }else{
                    $receiver_item =$request->get('phone');
                    $Otp_type = 0 ; //Send
                    $res =  $OTPService->setReceiverAddress($receiver_item)
                        ->setDeviceType(1)
                        ->setToken(generateNDigitRandomNumber(4))
                        ->set_for($OTPService::request_for_invite_competition)
                        ->setCallBackRouteName('dashboard.payments.check')
                        ->set_otp_type($Otp_type)
                        ->handel_OTP();
                    $competition_name = $competition->title;
                    $competition_link = $competition->url();
                    if($res){
                        $user = Unregistered::firstOrCreate([
                            'email' => Str::lower($request->get('phone')),
                            'name' => $request->get('name'),
                        ]);
                        $results['msg'] = 'شرکت کننده با موفقیت افزوده شد';

                    }
                    else{
                        $results['ok'] = false;
                        $results['msg'] = 'خطادر ارسال پیامک';
                    }
                }
            }
            else {
                $results['ok'] = false;
                $results['msg'] = 'نوع کاربر مشخص نمیباشد';
            }
        }
        return $results;
    }

    private function shuffleHandler(Request $request, $competition)
    {
        if ($competition->canShuffle()) {
            $shuffle = $competition->shuffleAction();
            if($shuffle){
                $competitionLog = Competition_logs::create([
                    'competition_id' => $competition->id,
                    'type' =>  1,
                    'data' => 'تغییرات براکت ( شافل ) صورت گرفت ',
                ]);

            }
            return array(
                'ok' => (bool)$shuffle,
                'msg' => $shuffle ? 'مسابقات با موفقیت شافل شد' : 'مشکلی در شافل رخ داد',
            );
        } else {
            return array(
                'ok' => false,
                'msg' => 'در حال حاضر این امکان وجود ندارد',
            );
        }
    }

    private function restrictHandler(Request $request, $competition)
    {
        if ($competition->isEnd()){
            return array(
                'ok' => false,
                'msg' => 'در حال حاضر این امکان وجود ندارد',
            );
        }
        $validator = Validator::make($request->all(), array(
            'reason' => ['required', 'string','max:255'],
        ));
        if ($validator->fails()) {
            $results = array(
                'ok' => false,
                'msg' => $validator->errors()->first()
            );
        }else {
            $player = $this->getPlayer($request->get('player'), $competition);
            if ($player->status == 0){
                return array(
                    'ok' => false,
                    'msg' => 'این بازیکن قبلا محروم شده است',
                );
            }else {
                $player->status = 0;
                $player->save();
                $reason = strip_tags($request->get('reason'));
                $player->meta()->updateOrCreate(['key' => 'restrict_reason'], ['value' => $reason]);
                if ($player->isRegistered() && $player->participant_id != $this->user->id){
                    $player->participant->notify(new Restricted($competition, $reason, $player));
                }
                $results = array(
                    'ok' => true,
                    'msg' => "بازیکن {$player->displayName()} با موفقیت محروم شد"
                );
            }
        }
        return $results;
    }

    private function invite_user($user,$competition)
    {
        if ($user->id !== $this->user->id) {
            $invited = (bool)$competition->inviteAction($user, $this->user);
            $results['ok'] = $invited;
            $results['msg'] = $invited ? 'دعوتنامه با موفقیت ارسال شد' : 'دعوتنامه ای قبلا برای کاربر ارسال شده است';
        } else {
            (bool)$competition->participateAction($user);
            $results['msg'] = 'با موفقیت به شرکت کنندگان افزوده شدید';
        }
        return $results ;

    }


}
