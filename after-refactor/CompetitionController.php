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


}
