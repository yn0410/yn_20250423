<?php
// 註解要寫在php中(不能寫在html中)，不然回傳(給ajax))的data不會是json格式

/* (目錄)
後台-煮飯
1.接收菜單
2.菜單內容 麵線 甜不辣
3.煮 麵線 甜不辣
4.送餐 前面服務生
5.送 麵線甜不辣 */


/* 後台-煮飯
1.接收菜單
2.菜單內容 麵線 甜不辣
3.煮 麵線 甜不辣
4.送餐 前面服務生
5.送 麵線甜不辣 */


// 報錯CORS 解決
// 所有Domain都能用了(用192.168.211.57 || localhost開"api.html"(連進此程式)，都能正常使用，不會報錯了)
header("Access-Control-Allow-Origin: *");
// 開放特定網域
// header("Access-Control-Allow-Origin: http://127.0.0.1:5500");

function dd($data){
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}

// 輸出測試
// dd("test ok");

// 假資料 輸出測試
/* $data = [
    [
        'id'=>1,
        'name'=>'amy'
    ],
    [
        'id'=>2,
        'name'=>'bob'
    ],
    [
        'id'=>3,
        'name'=>'cat'
    ]
]; */

// dd($data);



// 測試抓form送出的資料
// $data = $_GET;
// dd($data);

$input = $_GET;
$sum = $input['num1'] + $input['num2'];

$data = [
    'num1'=>$input['num1'],
    'num2'=>$input['num2'],
    'sum'=>$sum
];
dd($data);

echo json_encode($data);

?>