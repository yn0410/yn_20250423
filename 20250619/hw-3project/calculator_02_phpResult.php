<?php

function dd($data)
{
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}

$input  = $_GET;
// dd($input);

$n1 = $_GET['num1'];
$opt = $_GET['opt'];
$n2 = $_GET['num2'];
/* echo "n1=".$n1;
echo "<br>";
echo "opt=".$opt;
echo "<br>";
echo "n2=".$n2; */

$result = 0;
switch($opt){
    case '+':
        $result = $n1 + $n2;
        break;
    case '-':
        $result = $n1 - $n2;
        break;
    case '*':
        $result = $n1 * $n2;
        break;
    case '/':
        $result = $n1 / $n2;
        break;
    default:
        break;
}

// echo "result=".$result;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>簡易計算機-phpR</title>
    <style>
        .container{
            width: 300px;
            height: 300px;
            /* margin: auto; */
            border: 1px solid #DDD;
            padding: 5px;
        }
    </style>
</head>
<body>
    
    <div class="container">
        <p>
            <input type="number" name="num1" id="num1" value="<?= $n1;?>">
        </p>
        <p>
            <input type="text" name="opt" id="opt" value="<?= $opt;?>">
        </p>
        <p>
            <input type="number" name="num2" id="num2" value="<?= $n2;?>">
        </p>
        <p>
            <button type="submit" id="myBtn" value="submit" >計算</button>
        </p>
    
        <hr>
        <p id="resultP">
            <?= $n1 . $opt . $n2 . " = " .$result;?>
        </p>
    </div>
    
    </script>
</body>
</html>