<?php
// 放老師code
// $data = [
//     'msg' => 'ok'
// ];

function dd($data)
{
    print_r('<pre>');
    print_r($data);
    print_r('</pre>');
}

$data = [
    [
        'id' => 1,
        'class' => 'alert-info',
        'text' => 'Hello'
    ],
    [
        'id' => 2,
        'class' => 'alert-success',
        'text' => '你好嗎'
    ],
    [
        'id' => 3,
        'class' => 'alert-info',
        'text' => '衷心感謝'
    ],
    [
        'id' => 4,
        'class' => 'alert-warning',
        'text' => '珍重再見'
    ],
    [
        'id' => 5,
        'class' => 'alert-danger',
        'text' => '期待再相逢'
    ],
];
// dd($data);


echo json_encode($data);

?>