<?php
function lokalDatumAusText($datstr) {
    // <!-- $datum = date_create($datstr . "+0100"); -->
    $datum = date_create($datstr);
    // var_dump($datum);
    $tz = new DateTimeZone("Europe/Berlin");
    $datum->setTimezone($tz);
    // var_dump($datum);
    return $datum;
}

function InteressentenListe() {
    //Hole aus DB
    $erg = holeInteressenten();
    if ($erg['ok'] == false) {
      echo $erg['errmsg'];
      var_dump($erg);
      die;
    }
    $erg = $erg['result'];
    //Umspeichern
    $anfListe = [];

    $id_merker = 0;
    $n_anfr = -1;
    foreach ($erg as $rec) {
        //neuer Interessent
        if ($id_merker !=  $rec->a_id) {
            $id_merker = $rec->a_id;
            $n_anfr++;
            $n_termine = -1;
            $anf['a_id']       = $rec->a_id;
            $anf['name']       = $rec->name;
            $anf['art']        = $rec->art;
            $anf['status']     = $rec->status;
            $anf['bemerkung']  = $rec->bemerkung;
            $anf['termine']    = [];
            $anfListe[$n_anfr] = $anf;
        }
        //gibt es Termine ?
        if ($rec->datum) {
            $termin['t_id'] = $rec->t_id;
            $datum = lokalDatumAusText($rec->datum);
            $termin['datumZeitStr'] = $datum->format("d.m.y H:i");
            $termin['checked'] = '';
            if ($rec->bestaetigt) {
                $termin['checked'] = 'checked';
            }
            $n_termine++;
            $anfListe[$n_anfr]['termine'][$n_termine] = $termin;
        }
    }
    // dumpInteressentenListe($anfListe);
    return $anfListe;
}

function dumpInteressentenListe($anfListe) {
    foreach ($anfListe as $anf) {
        // echo $anf;
        echo $anf['a_id'] . "<br>\n";
        echo $anf['name'] . "<br>\n";
        echo $anf['art'] . "<br>\n";
        echo $anf['status'] . "<br>\n";
        echo $anf['bemerkung'] . "<br>\n";
        // var_dump( $anf['termine']);
        echo "<br>\n";
        foreach ($anf['termine'] as $datum) {
            echo $datum['t_id'] . ' ' . $datum['datumZeitStr'] . ' checked:--' . $datum['checked'] . " --<br>\n";
        }
    }
}

//nur Code Speicher: nicht benutzt
function Anfragen() {
    // ------------------------------------- Ausgabe der Interessenten/Anfragen
    $anfListe = InteressentenListe();

    foreach ($anfListe as $anf) {

    $a_id = $anf['a_id']; 
    // ------------------ div class="anfrage" ----------------------------
    //div als Klammer fuer query und speicher der Anfrage id
    echo '<div class="anfrage" data-anfId="' . $a_id . '">';
        //link target
        echo '<p id="anfrage' . $a_id . '"></p>';
        //Ausgabe id, Name, art
        echo $a_id . '. <b class="anfrage-name">' . $anf['name'] . '</b> (' . $anf['art'] . ')';
        // Button zum Editieren 
        echo '<span class="anfrage-edit">&#9998;</span><br>';
        // Ausgabe Status
        echo 'Status: ' . $anf['status'] . '<br>';
        // Ausgabe Bemerkung
        echo $anf['bemerkung'] . '<br>';

        //Termine, wenn vorhanden
        foreach ($anf['termine'] as $datum) {
        //span als Klammer f query und Speichern der Termin Id
        echo '<span class="termin" data-terminId="' . $datum['t_id'] . '">';
            //Ausgabe id und Termin
            echo 'Termin (' . $datum['t_id'] . '): ' . $datum['datumZeitStr'] ;
            // Button zum loeschen
            echo '<span class="termin-del">&#10134;</span>';
            // Button beestaetigt
            echo '<label>best&auml;tigt';
            echo '<input class="termin-best" type="checkbox" name="termin_best" value="best" ' . $datum['checked'] . '>';
            echo '</label><br>';
        echo '</span>';
        }
        
        //Abschluss erzeugen
        //anlegen neuer Termin
        echo 'neuer Termin: <span class="termin-neu">&#10133;</span><br>';
    echo '</div>'; //Ende eine Anfrage
    echo '<hr>';
    }
}      $erg = holeTermine();
if ($erg['ok'] == false) {
  echo $erg['errmsg'];
  var_dump($erg);
  die;
}
// var_dump($erg);
$erg = $erg['result'];
$alter_tag="";

// ------------------------------------- Ausgabe der Termine zu den Interessenten
foreach ($erg as $z) { 
  if ($z->datum) {
    // var_dump($z);
    $datum = lokalDatumAusText($z->datum);
    $tag = $datum->format("d.m.Y");
    $zeit = $datum->format("H:i");
    
    if ($tag != $alter_tag) {
      echo '<hr>';
      $alter_tag = $tag;
      echo '<b>' . $tag . ' </b><p>';
    }
    echo $zeit . ':  <a href="#anfrage' . $z->a_id . '" target="_top" >';
    echo $z->name . '</a> (' . $z->a_id . ') ';
    
    if ($z->bestaetigt) {
      echo '&#10004;';
    } else {
      echo '&#63;';
    }
    echo '<p>';
  }
} //foreach

function ausgabeTermine() {

    $erg = holeTermine();
    if ($erg['ok'] == false) {
      echo $erg['errmsg'];
      var_dump($erg);
      die;
    }
    // var_dump($erg);
    $erg = $erg['result'];
    $alter_tag="";
    
    // ------------------------------------- Ausgabe der Termine zu den Interessenten
    foreach ($erg as $z) { 
      if ($z->datum) {
        // var_dump($z);
        $datum = lokalDatumAusText($z->datum);
        $tag = $datum->format("d.m.Y");
        $zeit = $datum->format("H:i");
        
        if ($tag != $alter_tag) {
          echo '<hr>';
          $alter_tag = $tag;
          echo '<b>' . $tag . ' </b><p>';
        }
        echo $zeit . ':  <a href="#anfrage' . $z->a_id . '" target="_top" >';
        echo $z->name . '</a> (' . $z->a_id . ') ';
        
        if ($z->bestaetigt) {
          echo '&#10004;';
        } else {
          echo '&#63;';
        }
        echo '<p>';
      }
    } //foreach

}


?>