<?php
// 與"20250605_08_array.html"對照看
// 全是老師coode
$indexArr = [1, 2, 3];

$assocArr = [
    's1' => 'amy',
    's2' => 'bob',
    's3' => 'cat'
];


$data = [
    [
        'id' => 1,
        'name' => '台北店',
        'mobile' => '0911',
        'love' => [
            'js',
            'php',
            'css'
        ]
    ],
    [
        'id' => 2,
        'name' => '台中店',
        'mobile' => '0922',
        'love' => [
            [
                't1' => [1, 2, 3]
            ],
            [
                't2' => [
                    ['key1' => 'value1'],
                    ['key2' => 'value2'],
                ]
            ],
            [
                't3' => 'ok2'
            ]
        ]
    ],
    [
        'id' => 3,
        'name' => '高雄店',
        'mobile' => '0933',
    ],
];

foreach ($indexArr as $key => $value) {
    # code...
}

foreach ($assocArr as $key => $value) {
    # code...
}
?>