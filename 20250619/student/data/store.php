<?php
function dd($data)
{
    echo "<pre>";
    print_r($data);
    // var_dump($data);
    echo "</pre>";
}

$input = $_GET;
$input['rank'] = 'A';
// dd($input);
echo json_encode($input);





?>