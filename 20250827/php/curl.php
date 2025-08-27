<?php
// 此code要用xampp跑!!!

// 老師code
// 用php curl串 臺北市資料大平台 的 臺北市立動物園_服務設施 的API


function dd($data)
{
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}

header("Access-Control-Allow-Origin: *"); //所有Domain都能用了(用192.168.211.57 || localhost開"getCurl.html"，都能正常使用，不會報錯了)

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

echo($result);

//關閉連線
curl_close($ch);



?>