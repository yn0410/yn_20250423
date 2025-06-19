<?php
// include "./calculator_02_phpData.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>簡易計算機-php</title>
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
        <form action="./calculator_02_phpResult.php" method="get">

            <p>
                <input type="number" name="num1" id="num1" value="100">
            </p>
            <p>
                <input type="text" name="opt" id="opt" value="+">
            </p>
            <p>
                <input type="number" name="num2" id="num2" value="50">
            </p>
            <p>
                <button type="submit" id="myBtn" value="submit" >計算</button>
            </p>
        </form>
        <hr>
        <p id="resultP">
            //計算結果
        </p>
    </div>
    
    </script>
</body>
</html>