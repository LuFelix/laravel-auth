<?php

namespace App\Console\Commands;


use Spatie\SlashCommand\Handlers\BaseHandler;
use Spatie\SlashCommand\Request;
use Spatie\SlashCommand\Response;
use App\Models\Message;
use DB;

class SlackCommand extends BaseHandler
{

    public $guid;
    /**
     * If this function returns true, the handle method will get called.
     *
     * @param \Spatie\SlashCommand\Request $request
     *
     * @return bool
     */
    public function canHandle(Request $request): bool
    {
        return true;
    }

    /**
     * Handle the given request. Remember that Slack expects a response
     * within three seconds after the slash command was issued. If
     * there is more time needed, dispatch a job.
     *
     * @param \Spatie\SlashCommand\Request $request
     *
     * @return \Spatie\SlashCommand\Response
     */
    public function handle(Request $request): Response
    {

        $re = '/[guid]/m';
        $str = $request->text;
        $subst = '';

        $result = preg_replace($re, $subst, $str);
        if($result) {
            $resultArr = explode(" ", $result);
            $user_id = $resultArr[0];
            $this->guid = $user_id;
        }


        $theMessage = str_replace("guid".$this->guid, '', $str);

        $message = new Message();
        $message['message'] = ltrim($theMessage);
        $message['user_id'] = $this->guid;
        $message['flag']    = 'server';
        if(ltrim($theMessage) === "clear")
        {
            $messages = Message::where('user_id', '=', $this->guid)->get();
            foreach($messages as $message)
            {
                $message->delete();
            }
        }
        $message->save();

        return $this->respondToSlack("You have typed this text: `{$request->text}`");
    }
}