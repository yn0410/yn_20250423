<!-- 老師code -->

<?php
$data = [
    [
        'id' => 1,
        'name' => 'kai',
        'color' => 'blue',
        'price' => '1000'
    ],
    [
        'id' => 2,
        'name' => 'bob',
        'color' => 'red'
    ],
    [
        'id' => 3,
        'name' => 'cat',
        'color' => 'green'

    ]
];


?>
<!-- data-color="#blue" =bootstrap 用法? -->
<button type="button" data-name="kai" data-color="#blue">blueBtn</button>
<button type="button" data-name="bob" data-color="#red">redBtn</button>
<button type="button" data-name="cat" data-color="#green">greenBtn</button>

<?php foreach($data as $item) :?>
<button type="button" data-name="<?= $item['name'] ?>" data-color="<?= $item['color'] ?>"><?= $item['color'] ?>Btn</button>
<?php endforeach;?>
