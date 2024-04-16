<?php
require 'db.php';
header('Content-Type: application/json');

// Bekomme den JSON Body der Anfrage
$body = json_decode(file_get_contents('php://input'), true);

if (array_key_exists('action', $body)) {
    $action = $body['action'];
} else {
    echo json_encode(get_result(false, 'Keine Aktion übergeben.', null, null));
    die;
}

if (array_key_exists('data', $body)) {
    $data = $body['data'];
} else {
    echo json_encode(get_result(false, 'Keine Daten übergeben.', null, null));
    die;
}

if ($action=='speichereAnfrage') {
    $erg = speichereAnfrage($data);
    echo json_encode($erg);
} elseif ($action=='speichereTermin') {
    $erg = speichereTermin($data);
    echo json_encode($erg);
} elseif ($action=='loescheTermin') {
    $erg = loescheTermin($data);
    echo json_encode($erg);
} elseif ($action=='bestaetigeTermin') {
    $erg = bestaetigeTermin($data);
    echo json_encode($erg);
} else {
    echo json_encode(get_result(false, "Aktion $action nicht implementiert.", null, null));
}

?>
 