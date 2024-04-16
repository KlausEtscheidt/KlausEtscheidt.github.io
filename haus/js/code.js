/**
 * Auslesen und Darstellen von Anfrageinformationen
 * Ablauf:
 * Ereignis DOMContentLoaded ruft initMain
 * initMain ruft preventFormSubmit (noch nötig ?) und holeAnfragen
 * holeAnfragen holt per fetch die Daten und stellt sie per callback datenAusgabe dar
 * datenAusgabe füllt die Seite mit Anfragen und Terminen und ruft dann registriereHandler
 * Funktionen, die Daten ändern, aktualisieren die Seite mit reLoad
 */

  const REST_READ_URL = "inc/rest_read.php";
  const REST_EDIT_URL = "inc/rest_edit.php";
  const BASE_URL = "haus.php";

  //timestamp formatieren
  const optsDay = {year: "numeric", month: "2-digit", day: "2-digit"};
  const IntFDay = new Intl.DateTimeFormat("de-DE", optsDay);
  const optsTime = {hour: "numeric" , minute: "numeric" };
  const IntFTime = new Intl.DateTimeFormat("de-DE", optsTime);

  let startreading; //nur zur Laufzeiterfassung

  //Handler registrieren und Hauptseite initialisieren und auf Anfrage positionieren
  document.addEventListener("DOMContentLoaded", initMain);

  function initMain() {
    preventFormSubmit();
    holeAnfragen();
  }

  /* ------------------------------------------------------------------------
   *  Event Handler
   * ------------------------------------------------------------------------*/

  function registriereHandler() {

    //Button ganz oben zur Neuanlage einer Anfrage
    const anfrageNeu = document.querySelector('.anfrage-neu');
    anfrageNeu.addEventListener('click', anfrageNeuClickHandler);
    
    //Alle Evts im Bereich der Anfragen/Interessenten
    const elems = document.querySelectorAll('div.anfrage');
    for (let parent of elems) {
      const anfrageEdit = parent.querySelector('.anfrage-edit');
      //Parameterübergabe mit bind !!!!
      // 1. Argument (anfrageEdit) steht nicht in Parameterliste und wird zu this
      // der Evtl wird nicht übergeben steht aber IMMER als letztes in Parameterliste
      anfrageEdit.addEventListener('click', anfrageEditClickHandler.bind(anfrageEdit, parent));

      const terminBest = parent.querySelectorAll('.termin-best');
      //Wenn es Termine gibt
      for (let elem of terminBest) {
        elem.addEventListener('click', terminBestClickHandler.bind(elem, parent));
      }
      const terminDel = parent.querySelectorAll('.termin-del');
      for (let elem of terminDel) {
        elem.addEventListener('click', terminDelClickHandler.bind(elem, parent));
      }
      const terminNeu = parent.querySelector('.termin-neu');
      if (terminNeu) {
        terminNeu.addEventListener('click', terminNeuClickHandler.bind(terminNeu, parent));
      }
    }

    // Handler für Formulare
    const anfrage = document.getElementById('AnfrageDlg')
    anfrage.addEventListener('submit', speichereAnfrage);
    anfrage.addEventListener('reset', function () {document.getElementById('AnfrageDlg').close()});
    
    const termin = document.getElementById('TerminForm');
    termin.addEventListener('change', FuelleTerminForm); //update bei Datumswahl
    termin.addEventListener('submit', speichereTermin);
    termin.addEventListener('reset', function () {document.getElementById('TerminDlg').close()});
  }

  function anfrageNeuClickHandler(evt) {
    //id ins Formular
    document.getElementById('InteressentenId').innerHTML = 0;
    FuelleAnfrageForm(0);
    document.getElementById('AnfrageDlg').showModal();
  }

  function anfrageEditClickHandler(parent, evt) {
    const id = parent.dataset.anfid;
    sessionStorage.setItem("last_id", id);
    //id ins Formular
    document.getElementById('InteressentenId').innerHTML = id;
    FuelleAnfrageForm(id);
    document.getElementById('AnfrageDlg').showModal();
  }

  function terminNeuClickHandler(parent, evt) {
    const anfId = parent.dataset.anfid;
    const name = parent.querySelector('.anfrage-name').innerHTML;

    //Id und Name des Interessenten ins Formular
    //muss hier geschehen, wird sonst beim Formular update von FuelleTerminForm gelöscht 
    document.getElementById('termAnfId').innerHTML = anfId;
    document.getElementById('termName').innerHTML = name;

    // console.log('del Termin id: %s zu Anfrage %s', idTermin, anfId);
    sessionStorage.setItem("last_id", anfId);
    FuelleTerminForm();
  }

  function terminDelClickHandler(parent, evt) {
    const anfId = parent.dataset.anfid;
    const termin = this.parentNode;
    const idTermin = parseInt(termin.dataset.terminid);
    // console.log('del Termin id: %s zu Anfrage %s', idTermin, anfId);
    sessionStorage.setItem("last_id", anfId);
    loescheTermin(idTermin);
  }

  function terminBestClickHandler(parent, evt) {
    // evt.preventDefault();
    const chkBox = evt.target; // oder this
    const anfId = parent.dataset.anfid;
    const termin = this.parentNode.parentNode;
    const idTermin = parseInt(termin.dataset.terminid);
    console.log(anfId);
    console.log(idTermin);
    sessionStorage.setItem("last_id", anfId);
    Terminbestaetigen(idTermin, chkBox);
  }

  /* ------------------------------------------------------------------------
   *  Allgemeine Funktionen
   * ------------------------------------------------------------------------*/
 
   function reLoad() {
    holeAnfragen();
    // window.top.location = (window.top.location.hostname + window.top.location.pathname)
    //  window.open(BASE_URL,'_top');
   }

  // Prevent forms from submitting.
  function preventFormSubmit() {
    var forms = document.querySelectorAll('form');
    for (var i = 0; i < forms.length; i++) {
      forms[i].addEventListener('submit', function(event) {
        event.preventDefault();
      });
    }
  }

  //hole Daten für alle Interessenten aus der DB
  function holeAnfragen() {
    startreading = new Date();
    fetch(REST_READ_URL + "?action=holeAlleInteressenten")
      .then((response) => response.json())
      .then((json) => datenAusgabe(json));
  }

  function datenAusgabe(data) {
    const gelesen = new Date();
    console.log(gelesen-startreading);
    // console.log(data);
    if (!data['ok']) {
      alert(data['errmsg']);
    }
    const records = data['result'];
    // console.log(records);
    const anfragenObjekte = erzeugeAnfragenObjekte(records);
    anfragenObjekteAusgeben(anfragenObjekte);
    const terminObjekte = erzeugeTerminObjekte(records);
    terminObjekteAusgeben(terminObjekte);
    registriereHandler();
    //id des Interessenten, der zuletzt eine Aktion ausgelöst hat als Sprungziel
    let last_id = sessionStorage.getItem("last_id");
    // sessionStorage.setItem("firstcall", false);
    if (last_id) {
      // window.open(BASE_URL+'#anfrage' + last_id,'_top');
    }
    
  }

  function terminObjekteAusgeben(terminObjekte) {
    const container = document.getElementById('Termine');
    container.innerHTML = "";
    terminObjekte.forEach(tag => {
      container.appendChild(document.createElement('hr'));
      let tagElm = document.createElement('b');
      tagElm.innerText = tag.str;
      container.appendChild(tagElm);
      container.appendChild(document.createElement('p'));
      tag.termine.forEach(termin => {
        container.appendChild(
          document.createTextNode(termin.zeitStr + ': '));
        const link = document.createElement('a');
        link.setAttribute("href", "#anfrage" + termin.a_id);
        link.setAttribute("target", "_top");
        link.innerText = termin.name;
        container.appendChild(link);
        container.appendChild(
            document.createTextNode(' (' + termin.a_id + ')'));
        let char = '&#63;'
        if (termin.bestaetigt) {
          char = '&#10004;'
        }
        const btn = document.createElement('span');
        btn.innerHTML = char;
        container.appendChild(btn);
        container.appendChild(document.createElement('br'));
      });

    });
  }

  function anfragenObjekteAusgeben(anfragenObjekte) {
    const container = document.getElementById('Interessenten');
    container.innerHTML = "";
    anfragenObjekte.forEach(anf => {
      //div als äußerste Klammer Speicher der Anfrage id
      let Anfrage = document.createElement('div');
      Anfrage.classList.add("anfrage");
      Anfrage.setAttribute("data-anfId",anf.a_id);

      //target f links
      let newElm = document.createElement('p');
      newElm.setAttribute("id","anfrage" + anf.a_id);
      Anfrage.appendChild(newElm);
      //id, Name, art der Anfrage
      Anfrage.insertAdjacentHTML('beforeend', anf.a_id + '. ');
      newElm = document.createElement('b');
      newElm.innerText = anf.name;
      newElm.classList.add("anfrage-name");
      Anfrage.appendChild(newElm);
      Anfrage.insertAdjacentHTML('beforeend', ' (' + anf.art + ')');
      // Button zum Editieren 
      const editbtn = '<span class="anfrage-edit">&#9998;</span><br>';
      Anfrage.insertAdjacentHTML('beforeend', editbtn);
      // Ausgabe Status
      const status = 'Status: ' + anf.status + '<br>';
      Anfrage.insertAdjacentHTML('beforeend', status);
      // Ausgabe Bemerkung
      const bem =anf.bemerkung + '<br>';
      Anfrage.insertAdjacentHTML('beforeend', bem);
      
      //Termindaten
      //-----------
      anf.termine.forEach(termin => {
        //span als Klammer f query und Speichern der Termin Id
        let termin_span = document.createElement('span');
        termin_span.classList.add("termin");
        termin_span.setAttribute("data-terminId",termin.t_id );
        
        //Ausgabe id und Termin
        const ihtml = 'Termin (' + termin.t_id + '): ' + termin.datumZeitStr +
        // Button zum loeschen
         '<span class="termin-del">&#10134;</span>' +
         // Button bestaetigt
         '<label>best&auml;tigt' +
        '<input class="termin-best" type="checkbox" name="termin_best" value="best" ' + termin.checked + '>' +
        '</label><br>';
        termin_span.innerHTML = ihtml;
        
        Anfrage.appendChild(termin_span);
        
      });
      
      const terminneu = 'neuer Termin: <span class="termin-neu">&#10133;</span><br>';
      Anfrage.insertAdjacentHTML('beforeend', terminneu);

      container.appendChild(Anfrage);

      //Abschluss linie
      Anfrage.appendChild(document.createElement('hr'));

    });
  }

  function erzeugeTerminObjekte(records) {
    records.sort((a, b) => {
      const dateA = new Date(a.datum);
      const dateB = new Date(b.datum);
      return dateA - dateB;
    });
    // Umspeichern
    let alterTag= "";
    let TageListe = [];
    let n_Tage = -1;
    records.forEach(record => {
      //gibt es Termine ?
      if (record.datum) {
        let datum = new Date(record.datum);
        let tagStr = IntFDay.format(datum);
        if (alterTag != tagStr) {
          let tag = {}; //neues Tag-Objekt
          alterTag = tagStr; //merken
          tag.str = tagStr;
          tag.termine = [];  //leere Terminliste ins Objekt
          n_Tage++;
          TageListe[n_Tage] = tag; //Tag-Objekt in Liste
        }
        let termin = {};
        termin.a_id = record.a_id; //id der Anfrage
        termin.t_id = record.t_id; //id Termin
        termin.name = record.name; //name des Interesssenten
        termin.bestaetigt = record.bestaetigt; 
        termin.zeitStr = IntFTime.format(datum);
        TageListe[n_Tage].termine.push(termin); //Termin zum Tag-Objekt
      }
    });
    return TageListe;
  }

  function erzeugeAnfragenObjekte(records) {

    // Umspeichern
    let alteId=0;
    let anfrListe = [];
    let n_anfr = -1;
    records.forEach(record => {
      //neuer Interessent
      if (alteId !=  record.a_id) {
        alteId =  record.a_id;
        n_anfr++;
        let anf = {};
        anf['a_id']       = record.a_id;
        anf['name']       = record.name;
        anf['art']        = record.art;
        anf['status']     = record.status;
        anf['bemerkung']  = record.bemerkung;
        anf['termine']    = [];
        anfrListe[n_anfr] = anf;
      }
      //gibt es Termine ?
      if (record.datum) {
        let termin = {}; //neues Termin-Obj
        termin['t_id'] = record.t_id;
        const datum = new Date(record.datum);
        termin['datumZeitStr'] = IntFDay.format(datum) + ' ' + IntFTime.format(datum);
        termin['checked'] = '';
        if (record.bestaetigt) {
            termin['checked'] = 'checked';
        }
        anfrListe[n_anfr]['termine'].push(termin);
        // n_termine++;
        // anfrListe[n_anfr]['termine'][n_termine] = termin;
      }
    
    });
    return anfrListe;
  }

  /* ------------------------------------------------------------------------
   *  Anfrage Formular
   * ------------------------------------------------------------------------ */

  //ermöglicht Ändern einer Anfrage
  async function FuelleAnfrageForm(id) {
    //hole Daten für den Interessenten mit id aus der DB
    const response = await fetch(REST_READ_URL + "?action=hole1Interessenten&id=" + id);
    const data = await response.json();
    console.log(data);
    if (!data['ok']) {
      alert(data['errmsg']);
    }
    const record = data['result'][0];
    console.log(record);
    
    let form = document.forms['AnfrageForm'];
    form.InteressentenName.value = record.name;
    form.bemerkung.value = record['bemerkung'];
    
    let radios = form["Status"];
    for (let i=0; i<radios.length; i++)  {
      if (radios[i].value == record.status) {
        radios[i].checked = true;
      }
    }
    radios = form["InteressentenArt"];
    for (var i=0; i<radios.length; i++) {
      if (radios[i].value == record.art) {
        radios[i].checked = true;
      }
    }
  }

  async function speichereAnfrage() {
    let row = {};
    let senddata = {};
    row.id = document.getElementById('InteressentenId').innerHTML;
    row.art = document.querySelector('input[name="InteressentenArt"]:checked').value;
    row.status = document.querySelector('input[name="Status"]:checked').value;
    row.name = document.getElementById('name').value;
    row.bemerkung = document.getElementById('bemerkung').value;
    document.getElementById('AnfrageDlg').close();
    console.log(row);
    
    senddata.action = 'speichereAnfrage';
    senddata.data = row;
    
    const response = await fetch(REST_EDIT_URL, {
      method: "POST",
      body: JSON.stringify(senddata),
      headers: {"Content-type": "application/json; charset=UTF-8"}
    });
    const data = await response.json();
    console.log(data);
    
    // .then((response) => response.json())
    // .then((json) => console.log(json));
    reLoad();
  }
  
  /* ------------------------------------------------------------------------
   *  Termin Formular
   * ------------------------------------------------------------------------ */

  //Termin bestätigt ja/nein
  async function Terminbestaetigen(idTermin,chkBox) {
    let senddata = {};
    let row = {};

    row['bestaetigt'] = chkBox.checked;
    row['id'] = idTermin;

    senddata.action = 'bestaetigeTermin';
    senddata.data = row;

    const response = await fetch(REST_EDIT_URL, {
      method: "POST",
      body: JSON.stringify(senddata),
      headers: {"Content-type": "application/json; charset=UTF-8"}
    });
    const data = await response.json();
    console.log(data);
    if (!data.ok) {
      alert(data.errmsg);
    }
    reLoad();

  }
  
  // füllt und zeigt das Terminformular
  async function FuelleTerminForm() {
    let tag;
    let datum;
    // let ts;
    let datstr;
    let records;
    let record;

    //Berechne timestamp für Tag, ab dem Termine gelesen werden sollen
    tag = document.getElementById('Tag').value;
    if (tag=="") {
      datum = new Date();
      datum.setHours(0,0,0,0);
    } else {
      tag = tag+'T00:00';
      datum=new Date(tag);
    }
    // ts = datum.getTime();
    datstr = datum.toISOString();
    console.log(datstr);

    //lies Termine
    // const response = await fetch(REST_READ_URL + "?action=holeTermine&abTag=" + ts);
    const response = await fetch(REST_READ_URL + "?action=holeTermine&abTag=" + datstr);
    const data = await response.json();
    // console.log(data);
    if (data['ok'] == false) {
      alert(data['errmsg']);
    }
    records = data['result'];
    console.log(records);

    var html = '';
    for (var i = 0; i < records.length; i++) { 
      record = records[i];
      datum = new Date(record.datum);
      html += 'Termin: ' + IntFDay.format(datum) + '&nbsp;'; 
      html += IntFTime.format(datum) + '  ' + record.name + '(id: ' + record.a_id + ') <p>';
    }
    var div = document.getElementById('termine');
    div.innerHTML = html;
    document.forms['TerminForm'].parentNode.showModal();
  }

  async function speichereTermin() {
    let row = {};
    let senddata = {};

    const tag = document.getElementById('Tag').value;
    const stunden = document.getElementById('stunden').value;
    const minuten = document.getElementById('minuten').value;
    
    //als Namen sollten hier schon Datenbank-Feldnamen verwendet werden 
    row.id_anfr = document.getElementById('termAnfId').innerHTML;
    row.datum = tag+'T' + stunden + ':' + minuten;
    row.datum = new Date(row.datum);
    console.log(row);
    console.log(row.datum.toString());
    
    document.getElementById('TerminDlg').close();

    senddata.action = 'speichereTermin';
    senddata.data = row;
    
    const response = await fetch(REST_EDIT_URL, {
      method: "POST",
      body: JSON.stringify(senddata),
      headers: {"Content-type": "application/json; charset=UTF-8"}
    });
    const data = await response.json();
    console.log(data);
    if (!data.ok) {
      alert(data.errmsg);
    }
    reLoad();
  }
  
  async function loescheTermin(idTermin) {
    let senddata = {};
    senddata.action = 'loescheTermin';
    senddata.data = idTermin;
    const response = await fetch(REST_EDIT_URL, {
      method: "POST",
      body: JSON.stringify(senddata),
      headers: {"Content-type": "application/json; charset=UTF-8"}
    });
    const data = await response.json();
    console.log(data);
    if (!data.ok) {
      alert(data.errmsg);
    }
    reLoad();
  }

