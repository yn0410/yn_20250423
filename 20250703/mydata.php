<?php
// 我自己try的code(還沒做出來)
function dd($data){
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}

$data = [
    [
        'id'=> 1,
        'class'=> 'alert-info',
        'text'=> 'Hello'
    ],
    [
        'id'=> 1,
        'class'=> 'alert-success',
        'text'=> '你好嗎'
    ],
    [
        'id'=> 2,
        'class'=> 'alert-info',
        'text'=> '衷心感謝'
    ],
    [
        'id'=> 3,
        'class'=> 'alert-warning',
        'text'=> '珍重再見'
    ],
    [
        'id'=> 4,
        'class'=> 'alert-danger',
        'text'=> '期待再相逢'
    ]
];
// dd($data);

echo json_encode($data);


?>