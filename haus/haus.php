<!DOCTYPE html>
<html lang="de">
  <head>
    <base target="_top">
    <link rel="stylesheet" href="./css/haus.css">
    <script src="js/code.js" defer></script>
    <title>Haus-Anfragen</title>
  </head>
  <body>
    
    <h2> Hausverkauf</h2>
    <main>
      <nav>
        <p><input class='anfrage-neu' type="button" value="neue Anfrage"></p>
      </nav>

      <h3> Interessenten </h3>
      <div id="Interessenten"></div>
      
      <h3> Termine </h3>
      <div id="Termine"></div>

      <!-- ##################################################################   -->
      <!--                     Formular fuer Anfragen                           -->
      <!-- ##################################################################   -->
      <dialog id="AnfrageDlg">
      <form method="post" id="AnfrageForm" autocomplete="off">
        <div id=InteressentenId></div>
        <div class="text-input">
          <label for="name">Name</label>
          <input type="text" name="InteressentenName" value="name" id="name"><p>
          <label for="bemerkung">Bemerkung</label><p>
          <textarea id="bemerkung" name="bemerkung" rows="4" cols="50"></textarea>
        </div>
            
        <fieldset>
          <legend>Art des Interessenten</legend>
          <input type="radio" id="privat" name="InteressentenArt" value="privat" checked>
          <label for="privat"> Privat</label> <span>      </span>
          <input type="radio" id="Makler" name="InteressentenArt" value="Makler">
          <label for="Makler"> Makler</label> <br>
        </fieldset>
        <br>

        <fieldset>
          <legend>Status der Anfrage</legend>
          <input type="radio" id="stat_1" name="Status" value="neu" checked>
          <label for="stat_1">neu</label> <p>
          <input type="radio" id="stat_2" name="Status" value="Termin vorgeschlagen">
          <label for="stat_2">Termin vorgeschlagen</label> <p>
          <input type="radio" id="stat_3" name="Status" value="Termin best&auml;tigt">
          <label for="stat_3">Termin best&auml;tigt</label> <p>
          <input type="radio" id="stat_4" name="Status" value="besichtigt">
          <label for="stat_4">besichtigt</label> <p>
          <input type="radio" id="stat_5" name="Status" value="abgesagt">
          <label for="stat_5">abgesagt</label> <p>
          <input type="radio" id="stat_6" name="Status" value="2. Termin">
          <label for="stat_5">2. Termin</label> <p>
        </fieldset>
        <br>

        <input type="submit" value="speichern">
        <input type="reset" value="Abbruch">
      </form>
      </dialog>

      <!-- ##################################################################   -->
      <!--                     Formular fuer Termine                            -->
      <!-- ##################################################################   -->
      <dialog id="TerminDlg">
        <h4 >Interessent: <span id=termName> InteressentenName </span></h4> 
        Id: <span id=termAnfId> InteressentenId  </span><br>

          <!-- <form method="post" id="TerminForm" onsubmit="speichereTermin()" onchange="FuelleTerminForm()"> -->
          <form method="post" id="TerminForm">

            <div class="text_input">
              <label for="Tag">Tag</label>
              <input type="date" id="Tag" name="Tag" value="" min="2024-04-06" max="2025-12-31" >

              <label for="stunden">Zeit</label>
              <select name="stunden" id="stunden">
                <option>08</option><option>09</option><option>10</option><option>11</option>
                <option>12</option><option>13</option><option selected>14</option>
                <option>15</option><option>16</option><option>17</option><option>18</option>
                <option>19</option><option>20</option>
              </select>
              
              <select name="minuten" id="minuten">
                <option selected>00</option><option>15</option><option>30</option><option>45</option>
              </select>
            </div>
            
            <br>
            <input type="submit" value="speichern" >
            <input type="reset" value="Abbruch">
            <!-- <input type="button" value="Abbruch" onclick="TerminCloseClick()" > -->
            
          </form>
        <hr>

        <h4>vergebene Termine</h4>
        <div id="termine">Lade ...</div>

      </dialog>
      
    </main>
    <footer>
      <hr>
      <a href= index.html>Hauptseite</a>
    </footer>

  </body>
  
  </html>