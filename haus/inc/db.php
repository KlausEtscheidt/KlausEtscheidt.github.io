<?php
// echo date("H:i:s");
error_reporting(E_ALL);
// error_reporting(0);

function openDb() {
    try {
        // $sqlite = false;
        $sqlite = true;
        if ($sqlite) {
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            if ($uri == "/haus/haus.php") {
                $db = new PDO('sqlite:./inc/anfrage.db');
            } else {
                $db = new PDO('sqlite:anfrage.db');
            }
        } else {
            $db = new PDO('mysql:host=localhost;dbname=ketscheidt;charset=utf8', 'ketscheidt', 'grisu');
            // $db->set_charset('utf8');
        }
        return get_result(true, '', null, $db);
    } catch (PDOException $e) {
        // print_r ($e->getMessage());
        // die ($e->getMessage());
        return get_result(false, 'konnte db nicht öffnen', $e, null);
    }
}

// ############################################################################
// Helper
// montiert einheitliche Antwort für korrekte Ausführung oder Exceptions
function get_result($ok, $errmsg, $err, $result) {
    if ($err) {
        $errmsg = $errmsg . $err->getMessage();
    }
    $erg = array("ok" => $ok, "errmsg" => $errmsg, "err" => $err, "result" => $result);
    return $erg;
}


// ############################################################################
// Generische prepared-Abfrage
// lesen
// $paramlist: [[wert, typ(PDO::PARAM_xxx)], ...]
function execQry($sql, $art, $paramlist=null) {
    $res = openDb();
    if ($res['ok']==false) {
        return $res;
    }
    $db = $res['result'];
    try {
        $errmsg = "Fehler in prepare:";
        $suche = $db->prepare($sql);
        if (! $suche) {
            return get_result(false, 'Prepare nio', null, $sql);
        }
        $errmsg = "Fehler in bind:";
        if ($paramlist) {
            for ($i=0; $i<count($paramlist); $i++) {
                $row = $paramlist[$i];
                $suche->bindParam($i+1, $row[0], $row[1]);
            }
        }
        $errmsg = "Fehler in execute:";
        $success = $suche->execute();
        if ($art=='read') {
            $errmsg = "Fehler in fetch:";
            $result = $suche->fetchAll(PDO::FETCH_OBJ);
        } else {
            $errmsg = "Fehler in rowCount:";
            $result = $suche->rowCount();
        }
    } catch (PDOException $e) {
        return get_result(false, $errmsg, $e, $sql);
    }
    $db=null;
    return get_result(true, '', null, $result);
}

// ###########################################################################
// Abfragen, die ändern
function loescheTermin($data) {
    $sql = "DELETE FROM Termine WHERE id=?;";
    $params = [[$data, PDO::PARAM_INT]];
    return execQry($sql, 'edit', $params);
}

function bestaetigeTermin($data) {
    $sql = "UPDATE Termine SET bestaetigt=? WHERE id= ?";
    $params = [[$data['bestaetigt'], PDO::PARAM_BOOL],
               [$data['id'], PDO::PARAM_INT]];
    return execQry($sql, 'edit', $params);
}

function speichereTermin($data) {
    $sql = "INSERT INTO Termine(id_anfr, datum) VALUES (?, ?)";
    $params = [[$data['id_anfr'], PDO::PARAM_INT],
               [$data['datum'], PDO::PARAM_STR]];
    return execQry($sql, 'edit', $params);
}

// Neue oder geänderte Anfrage speichern
function speichereAnfrage($data) {
    
    $params = [[$data['art'], PDO::PARAM_STR],
    [$data['status'], PDO::PARAM_STR],
    [$data['name'], PDO::PARAM_STR],
    [$data['bemerkung'], PDO::PARAM_STR]
    ];
    
    if ($data['id']=='0') {
        //append
        $sql = "INSERT INTO Anfragen(art, status, name, bemerkung) VALUES (?, ?, ?, ?)";
    } else {
        //update
        $sql = "UPDATE Anfragen SET art=?, status=?, name=?, bemerkung=? WHERE id = ?";
        $params[4] = [$data['id'], PDO::PARAM_INT];
    }
    return execQry($sql, 'edit', $params);
}

// ###########################################################################
// Abfragen, die lesen

// Statistik lesen
function holeStatistik() {
    $sql = "SELECT stand, aufrufe, gemerkt FROM aufrufe ORDER BY stand DESC;";
    return execQry($sql, 'read');
}

// Daten eines Interessenten mit id=$id
function holeInteressentenId($id) {
    $sql = "SELECT id, art, status, name, bemerkung FROM Anfragen WHERE id=?;";
    $params = [[$id, PDO::PARAM_INT]];
    return execQry($sql, 'read', $params);
}

// Daten aller Interessenten mit Terminen sortiert nach Anfragen id
function holeInteressenten() {
    // return lieswas($sql);
    $sql = "SELECT Anfragen.id AS a_id, art, status, name, bemerkung, datum, Termine.id as t_id, bestaetigt FROM Anfragen " 
    . "LEFT JOIN Termine ON Anfragen.id = Termine.id_anfr ORDER BY Anfragen.id;";
    return execQry($sql, 'read');
}

// Daten aller Interessenten mit Terminen sortiert nach Termin
// und evtl nach Datum gefiltert (datum >= $abTag)
function holeTermine($abTag=null) {
    if (! $abTag) {
        $abTag = "2000-01-01T00:00:00.000Z"; //hole alle
    }
    $sql = "SELECT Anfragen.id AS a_id, art, status, name, bemerkung, datum, Termine.id as t_id, bestaetigt FROM Anfragen " 
    . "LEFT JOIN Termine ON Anfragen.id = Termine.id_anfr WHERE datum >=? ORDER BY datum;";
    $params = [[$abTag, PDO::PARAM_STR]];
    return execQry($sql, 'read', $params);
}

?>