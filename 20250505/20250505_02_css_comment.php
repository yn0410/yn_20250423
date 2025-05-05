<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <!-- 都是老師打的code 他檔名叫"test.html" -->
    <!-- html code -->

    <table>
        <!-- 把程式語言切分乾淨 較好讀(?) -->
        <!-- 註解記得是下在什麼語言之下的(其實也不用記啦 按"ctrl + /"便可註解&解除註解！) -->
        <!-- php 與 html 分開 / way 1-->
        <?php foreach ($variable as $key => $value) : ?>
            <tr>
                <td>

                </td>
            </tr>
        <? endforeach; ?>

        <!-- php 與 html 分開 / way 2-->
        <?php
        echo "<tr>";
        echo "<td>";
        echo "</td>";
        echo "</tr>";
        ?>



    </table>
    <?php

    ?>

    <!-- php code -->
</body>

</html>