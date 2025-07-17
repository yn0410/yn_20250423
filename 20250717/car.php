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

interface Money{ //克金 所以叫Money
    public function fly();
}

interface Money100{
    public function swim();
}


// 可掛多個interface
class Car implements Money,Money100
{

    // porperties(物件導向 變數)
    public $name;
    public $color;


    // methods(物件導向 函式)
    function run()
    {
        echo "$this->color 的 $this->name 正在跑";
    }


    // interface
    function fly()
    {
        echo "$this->color 的 $this->name 正在飛<br>";
    }

    function swim()
    {
        echo "$this->color 的 $this->name 正在游泳<br>";
    }
}

class Car2 implements Money100
{

    // porperties
    public $name;
    public $color;


    // methods
    function run()
    {
        echo "$this->color 的 $this->name 正在跑<br>";
    }
 
    function swim()
    {
        echo "$this->color 的 $this->name 正在游泳<br>";
    }
}


// new (object?)
$car = new Car();
$car->name = '小汽車';
$car->color = '紅色';
$car->run();
$car->fly();

$tank = new Car();
$tank->name = '坦克車';
$tank->color = '綠色';
$tank->run();


dd($car);
dd($tank);

?>