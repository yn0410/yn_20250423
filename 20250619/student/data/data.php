<?php
function dd($data)
{
    echo "<pre>";
    print_r($data);
    // var_dump($data);
    echo "</pre>";
}


$data = [
    [
        'id' => 1,
        'name' => 'amy',
    ],
    [
        'id' => 2,
        'name' => 'bob',
    ],
    [
        'id' => 3,
        'name' => 'cat',
    ],
];

// dd($data);

// array to json
echo json_encode($data);


// 1.php 產出 $data array
// 2.轉換成json
// echo json_encode($data);
?>