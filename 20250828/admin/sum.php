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




function dd($data){
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}

// 輸出測試
// dd("test ok");

// 假資料 輸出測試
$data = [
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
];

// dd($data);


echo json_encode($data);

?>