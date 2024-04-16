<!doctype html><html>
<head><meta charset='utf-8'>
<title>gespeichert</title>
</head>
<body>
<h3>Gespeichert</h3>

<?php
    require 'db.php';
    // header('Content-Type: application/json');

    // Bekomme Body der Anfrage
    // $body = file_get_contents('php://input');
    // echo $body;
    // var_dump($_POST);
    $datum = new DateTime(); //now
    $tz = new DateTimeZone("Europe/Berlin");
    $datum->setTimezone($tz);

    $datstr = $datum->format('c');

    $sql = "INSERT INTO aufrufe(stand, aufrufe, gemerkt) VALUES (?, ?, ?);";
    $params = [[$datstr, PDO::PARAM_STR],
            [$_POST['aufrufe'], PDO::PARAM_INT],
            [$_POST['gemerkt'], PDO::PARAM_INT]];
    execQry($sql, 'edit', $params);

    echo "Datum: {$datstr}<br>
    Aufrufe: {$_POST['aufrufe']}<br>
    gemerkt: {$_POST['gemerkt']}<br>";
?>
</body></html>

