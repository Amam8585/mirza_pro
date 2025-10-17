<?php
ini_set('error_log', 'error_log');
date_default_timezone_set('Asia/Tehran');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../botapi.php';
require_once __DIR__ . '/../function.php';
require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../jdf.php';
$setting = select("setting", "*");
$midnight_time = date("H:i");
if(intval($setting['scorestatus']) == 1){
$otherreport = select("topicid","idreport","report","otherreport","select")['idreport'];
if ($midnight_time == "00:00") {
// if(true){
$temp = [];
$Lottery_prize = json_decode($setting['Lottery_prize'],true);
foreach ($Lottery_prize as $lottery){
    $temp[] = $lottery;
}
$Lottery_prize = $temp;
if($setting['Lotteryagent'] == "1"){
$stmt = $pdo->prepare("SELECT * FROM user WHERE User_Status = 'Active' AND score != '0' ORDER BY score DESC LIMIT 3");
$stmt->execute();    
}else{
$stmt = $pdo->prepare("SELECT * FROM user WHERE User_Status = 'Active' AND score != '0' AND agent = 'f' ORDER BY score DESC LIMIT 3");
$stmt->execute();
}
        $count = 0;
        $textlotterygroup = "📌 ادمین عزیز کاربران زیر برنده قرعه کشی و حسابشان شارژ گردید.

";
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $textbotlang = json_decode(file_get_contents('../text.json'),true)[$result['language']];
            $balance_last = intval($result['Balance']) + intval($Lottery_prize[$count]);
            update("user","Balance",$balance_last,"id",$result['id']);
            $balance_last = number_format($Lottery_prize[$count]);
            $countla = $count +1;
            $textlottery = "🎁 نتیجه قرعه کشی 

😎 کاربر عزیز تبریک شما  نفر $countla برنده $balance_last تومان موجودی شدید و حساب شما شارژ گردید.";
            sendmessage($result['id'], $textlottery, null, 'html');
            $count  += 1;
            $textlotterygroup .= "
نام کاربری : @{$result['username']}
آیدی عددی : {$result['id']}
مبلغ : $balance_last
نفر : $countla
--------------";
        }
        telegram('sendmessage',[
            'chat_id' => $setting['Channel_Report'],
            'message_thread_id' => $otherreport,
            'text' => $textlotterygroup,
            'parse_mode' => "HTML"
        ]);
        
        update("user","score","0",null,null);

}
}