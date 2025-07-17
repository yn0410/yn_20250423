<?php
// 一樣全是老師程式碼
// 講解PHP的物件導向的
// 要開xampp才能執行喔!


// $car = '紅色的小汽車';
// $tank = '綠色的坦克車';

function dd($data)
{
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}


class Car
{

    // porperties(物件導向 變數)
    public $name;
    public $color;


    // methods(物件導向 函式)
    function run()
    {
        echo "$this->color 的 $this->name 正在跑";
    }
}


// new (object?)
$car = new Car();
$car->name = '小汽車';
$car->color = '紅色';
$car->run();

$tank = new Car();
$tank->name = '坦克車';
$tank->color = '綠色';
$tank->run();


dd($car);
dd($tank);

?>