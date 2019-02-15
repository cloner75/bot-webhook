<?php
header('Content-Type: text/html; charset=utf-8');
require 'Sql.php'; // one class for insert Update Delete Select mysql
class BOT extends Sql
{
    public $Token = "724260205:AAE-pvGXbrdTpDTx0lB2bvihLB6vQ_xaBNo";
    public $Url;
    public $Result;
    public $chatId;
    public $Text;
    public $Type;
    public $Offset = 1;
    public $messageID;
    public $callbackID;

    function __construct()
    {
        parent::__construct();
    }


    // function first for start bot AND get all request
    public function Start()
    {
        $this->Result = file_get_contents("php://input");
        $this->Result = json_decode($this->Result , true);
        if (isset($this->Result['callback_query']['message']))
        {
            $this->chatId = $this->Result['callback_query']['from']['id'];
            $this->Text = $this->Result['callback_query']['data'];
            $this->messageID = $this->Result['callback_query']['message']['message_id'];
            $this->callbackID= $this->Result['callback_query']['id'];
            $this->Type = 'inline';
        }else
        {
            $this->chatId = $this->Result['message']['from']['id'];
            $this->Text = $this->Result['message']['text'];
            $this->messageID = $this->Result['message']['message_id'];
            $this->Type = 'keyboard';
        }
        $this->Response();
    }


    // method for response to user

    private function Response()
    {

        switch ($this->Type) {
            case 'inline':
                $this->show_alert('پیام اپدیت شد 📲'."\n".'برای مشاهده به پیام بازگردید 👆');
                $this->getConditionInline();
                break;
            case 'keyboard':
                $this->getCondition();
                break;
        }
        // $this->Response();
    }


    // method for condition inline
    private function getConditionInline()
    {
        $Inline = explode('_',$this->Text);
        switch ($Inline[0]) {
            case 'more':
                $this->getMore($Inline[1]);
                break;
            case 'code':
                $this->getCode($Inline[1]);
                break;
        }
    }

// method for more inline button
    private function getMore($ID)
    {
        $getData = $this->Select('files','`ID` = '.$ID);
        $Keyboard  = [
            'inline_keyboard'=>[
                [
                    ['text'=>'روش کد ⚙️','callback_data'=> "code_".$ID]
                ]
            ]
        ];
        $this->editMessageText($getData['Title']."\n".$getData['Description'].'<a href="'.$getData['File'].'">​​​​​​​​​​​</a>',$this->messageID,$Keyboard);
    }

    private function getCode($ID)
    {
        $getData = $this->Select('ways','`FID` = '.$ID);
        $Keyboard  = [
            'inline_keyboard'=>[
                [
                    ['text'=>'بیشتر 📋','callback_data'=>"more_".$ID]
                ]
            ]
        ];
        $this->editMessageText($getData['Title']."\n".$getData['Description'].'<a href="'.$getData['File'].'">​​​​​​​​​​​</a>',$this->messageID,$Keyboard);
    }

    public function show_alert($text){
        $url="https://api.telegram.org/bot".urlencode($this->Token)."/answerCallbackQuery?callback_query_id=".urlencode($this->callbackID)."&text=".urlencode($text)."&show_alert=true";
        file_get_contents($url);
    }
    // method for condition keyboard
    private function getCondition()
    {
        $arrayCheck = array(
            'startBot' => '/start',
            'aboutMe' => 'درباره ی من 👨🏻‍💻',
            'getRezome' => 'نمونه کارها 📗'
        );
        $getFunction = array_search($this->Text,$arrayCheck);
        if (empty($getFunction) OR is_null($getFunction))
        {
            $this->sendFile('لطفا گزینه ی صحیح را انتخاب کنید 😒','text','sendMessage');
            die();
        }
        $this->{$getFunction}();

    }

    // method start
    private function startBot()
    {
        $this->Text = "گزینه ی مورد نظر را انتخاب فرمایید 🕹";
        $Keyboard = array(
            'keyboard' => array(
                array('درباره ی من 👨🏻‍💻','نمونه کارها 📗')
            ),"resize_keyboard" => true,"one_time_keyboard" => true
        );
        $this->sendFile($this->Text,'text','sendMessage','',$Keyboard);

    }

    // function for get about mehttp://amlakhezare3.ir/mrYounesi/files/vk2.jpg
    private function aboutMe()
    {
        $this->Result = $this->Select('abouts');
        $this->sendFile($this->Result['Content'].'<a href="'.$this->Result['File'].'">​​​​​​​​​​​</a>','text','sendMessage');
    }

    // method for get
    private function getRezome()
    {
        $this->Result = $this->Selects('files');
        foreach ($this->Result as $Value)
        {
            $Keyboard  = [
                'inline_keyboard'=>[
                    [
                        ['text'=>'روش کد ⚙️','callback_data'=> "code_".$Value['ID']],
                        ['text'=>'بیشتر 📋','callback_data'=>"more_".$Value['ID']]
                    ]
                ]
            ];
            $this->sendFile($Value['Title'].'<a href="'.$Value['File'].'">​​​​​​​​​​​</a>','text','sendMessage','',$Keyboard);
        }
    }

    // method for action user
    private function Action($Type)
    {
        $sendMessageUrl = "https://api.telegram.org/bot".urlencode($this->Token)."/sendChatAction?chat_id=".urlencode($this->chatId)."&action=".urlencode($Type);
        file_get_contents($sendMessageUrl);
    }


// start for methods editmessage

    public function editMessageText($newText,$messageID,$Keyboard = ' ')
    {
        $this->Action('typing');
        $Url = "https://api.telegram.org/bot".urlencode($this->Token)."/editMessageText?chat_id=".urlencode($this->chatId)."&message_id=".urlencode($messageID)."&text=".urlencode($newText)."&parse_mode=HTML&reply_markup=".urlencode(json_encode($Keyboard));
        file_get_contents($Url);
    }


    public function editMessageReplyMarkup($Keyboard,$messageID)
    {
        $this->Action('typing');
        $Url = "https://api.telegram.org/bot".urlencode($this->Token)."/editMessageReplyMarkup?chat_id=".urlencode($this->chatId)."&message_id=".urlencode($messageID)."&reply_markup=".urlencode($Keyboard);
        file_get_contents($Url);
    }
// end methods editMessage

// method for sendPhoto
    private function sendFile($Caption,$Type,$Mehtod,$File = '',$Keyboard = '')
    {
        $Url = "https://api.telegram.org/bot".urlencode($this->Token)."/".$Mehtod."?chat_id=".urlencode($this->chatId);
        if ($Keyboard)
        {
            $Key = json_encode($Keyboard);
            $post_fields['reply_markup'] = $Key;
        }
        if ($File)
        {
            $post_fields[$Type] = new CURLFile(realpath($File));
            $post_fields['caption'] = $Caption;
            switch ($Type) {
                case 'photo':
                    $Action = 'upload_photo';
                    break;
                case 'video':
                    $Action = 'upload_video';
                    break;
                case 'audio':
                    $Action = 'upload_audio';
                    break;
            }

        }else
        {
            $post_fields['parse_mode'] = 'HTML';
            $post_fields['text'] = $Caption;
            $Action = 'typing';
        }
        $this->Action($Action);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type:multipart/form-data"
        ));
        curl_setopt($ch, CURLOPT_URL, $Url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        $output = curl_exec($ch);

    }

}
?>
