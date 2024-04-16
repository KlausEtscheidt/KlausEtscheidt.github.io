<?php
require 'db.php';
// header('Content-Type: application/json');
// $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (array_key_exists('action', $_GET)) {
    $action = $_GET['action'];
} else {
    echo json_encode(get_result(false, 'Keine Aktion übergeben.', null, null));
    die;
}

if ($action=='holeAlleInteressenten') {
    $data = holeInteressenten();
} elseif ($action=='hole1Interessenten') {
    $id = $_GET['id'];
    $data = holeInteressentenId($id);
} elseif ($action=='holeTermine') {
    $abTag = $_GET['abTag'];
    $data = holeTermine($abTag);
} elseif ($action=='holeStatistik') {
    $data = holeStatistik();
} else {
    echo json_encode(get_result(false, "Aktion $action nicht implementiert.", null, null));
    die();
}

// echo $action . '<p>';
// echo $id . '<p>';
// $data = holeInteressenten($id);
echo json_encode($data);
?>
 