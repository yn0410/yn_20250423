<?php
// 所有Domain都能用了(用192.168.211.57 || localhost開檔案(連進此程式)，都能正常使用，不會報錯了)
header("Access-Control-Allow-Origin: *");


function dd($data)
{
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}

$data = [
    'id' => 7,
    'name' => '詹雅年',
    'msg' => "生活本來就是在普通裡面找亮點✨"
];
// dd($data);

echo json_encode($data, JSON_UNESCAPED_UNICODE); //JSON_UNESCAPED_UNICODE =顯示中文
?>