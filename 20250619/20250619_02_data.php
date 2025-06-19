<?php
function dd($data){
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}

$input = $_GET;
dd($input);

?>