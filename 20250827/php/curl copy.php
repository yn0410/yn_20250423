<?php
// "curl.php"檔複製的沒改的，而"curl.php"刪了些註解，以方便閱讀&理解(?) / 老師刪的

function dd($data)
{
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}

//init curl
$ch = curl_init();
//curl_setopt可以設定curl參數
//設定url
// curl_setopt($ch, CURLOPT_URL, "https://data.taipei/api/v1/dataset/5048d475-7642-43ee-ac6f-af0a368d63bf?scope=resourceAquire");

// 設定要步要顯示 所有抓回資料 CURLOPT_RETURNTRANSFER
// default false 顯示
// true 不顯示
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//執行，並將結果存回

$url = 'https://data.taipei/api/v1/dataset/5048d475-7642-43ee-ac6f-af0a368d63bf?scope=resourceAquire';
curl_setopt_array($ch, [
    CURLOPT_URL            => $url,
    CURLOPT_RETURNTRANSFER => true
]);


$result = curl_exec($ch);

// curl_exec
// 抓出來$result type string
// {"result":{"limit":20,"offset":0,"count":314,"sort":"","results":[{"_id":1,"_importdate":{"date":"2025-

// 透過json_decode 
// type array
// (
//     [result] => Array
//         (
//             [limit] => 20
//             [offset] => 0
//             [count] => 314
//             [sort] => 
//             [results] => Array
//                 (
//                     [0] => Array
//                         (
//                             [_id] => 1
//                             [_importdate] => Array
//                                 (
//                                     [date] => 2025-06-09 11:26:48.083629
//                                     [timezone_type] => 3
//                                     [timezone] => Asia/Taipei
//                                 )


// $resultType = gettype($result);
// dd($resultType);
// dd($result);

//關閉連線
curl_close($ch);

// decode 解開
// encode 封起來
// $data = json_decode($result, true);
dd($result);
// // $data = $result;

// // // 用 dd() 秀出來
// $result = gettype($data);
// dd($result);
// dd($data);
