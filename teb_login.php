<?php
session_start();
// Preveri, če je uporabnik prijavljen (primer, prilagodi glede na svoj sistem)
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
$user = $_SESSION['user']; // Predpostavljamo, da vsebuje 'username' in 'user_code'
?>
<!DOCTYPE html>
<html lang="sl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Prijava v turnir</title>
  <link rel="stylesheet" href="css/teb_login.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
  <div class="login-container">
    <div class="user-name">
      <h1 class="team-name"><?php echo htmlspecialchars($user['username']); ?></h1>
    </div>

    <div class="content-container">
      <!-- Vprašanje o sodelovanju -->
      <div class="login-section">
        <h2>Ali boš sodeloval v večjih turnirjih?</h2>
        <div class="radio-group">
          <label>
            <input type="radio" name="participate" value="yes"> Da
          </label>
          <label>
            <input type="radio" name="participate" value="no" checked> Ne
          </label>
        </div>
      </div>

      <!-- Izbira turnirja -->
      <div class="tournament-selection">
        <label class="label-select">Izberi turnir:</label><br>
        <select class="tournament-select" id="tournament-select">
          <option value="" disabled selected>Izberite turnir</option>
        </select>

        <!-- Drugi turnir, prikaže se samo, če je izbrano "Da" -->
        <div id="secondTournamentContainer" style="display: none;">
          <br>
          <label class="label-select">Izberi še drugi turnir:</label><br>
          <select class="tournament-select" id="second-tournament-select">
            <!-- Opcije bodo dodane dinamično -->
          </select>
        </div>
      </div>

      <!-- Vnos slogana -->
      <div class="slogan-section">
        <label class="input-label">Slogan (ni obvezno)</label>
        <input type="text" class="input-field" id="slogan" placeholder="Calm under pressure">
      </div>
    </div>

    <!-- Gumbi za shranjevanje in odjavo -->
    <div class="button-section">
      <button class="save-button" id="save-btn">Shrani</button>
      <button class="save-button" id="withdraw-btn">Odjavi iz turnerja</button>
    </div>
  </div>

  <script>
  $(document).ready(function() {
    var participate = 'no';

    // Spreminjanje stanja glede na izbrani radio gumb
    $('input[name="participate"]').change(function() {
      participate = $(this).val();
      if (participate === 'yes') {
        $('#secondTournamentContainer').show();
      } else {
        $('#secondTournamentContainer').hide();
      }
    });

    // Funkcija za pridobivanje turnirjev iz API-ja
    function fetchTournaments() {
      $.ajax({
        url: 'https://lanparty.scv.si/api/sol_tournaments',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
          var options = '';
          $.each(data, function(index, tournament) {
            options += '<option value="'+tournament.name+'">'+tournament.name+'</option>';
          });
          $('#tournament-select').append(options);
          $('#second-tournament-select').append(options);
          // Če je na voljo vsaj en turnir, nastavi privzeto vrednost
          if (data.length > 0) {
            $('#tournament-select').val(data[0].name);
          }
        },
        error: function(xhr, status, error) {
          console.error("Napaka pri pridobivanju turnirjev:", error);
        }
      });
    }
    fetchTournaments();

    // Funkcija za pridobivanje obstoječih podatkov uporabnika iz API-ja
    function fetchExistingData() {
      $.ajax({
        url: 'https://lanparty.scv.si/api/solo-tournament/<?php echo htmlspecialchars($user['user_code']); ?>',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
          if (data) {
            // Nastavi vrednosti iz pridobljenih podatkov
            $('input[name="participate"][value="'+data.participate+'"]').prop('checked', true).change();
            $('#tournament-select').val(data.tournament_name);
            $('#second-tournament-select').val(data.second_tournament_name);
            $('#slogan').val(data.slogan);
          }
        },
        error: function(xhr, status, error) {
          if (xhr.status === 404) {
            console.log("Ni obstoječih podatkov za uporabnika.");
          } else {
            console.error("Napaka pri pridobivanju podatkov:", error);
          }
        }
      });
    }
    fetchExistingData();

    // Shranjevanje podatkov (prijava v turnir)
    $('#save-btn').click(function() {
      var selectedTournament = $('#tournament-select').val();
      var secondTournament = (participate === 'yes') ? $('#second-tournament-select').val() : null;
      var slogan = $('#slogan').val();

      if (!participate) {
        alert('Prosim, izberi odgovor na vprašanje.');
        return;
      }

      var postData = {
        user_code: '<?php echo htmlspecialchars($user['user_code']); ?>',
        participate: participate,
        tournament_name: selectedTournament,
        second_tournament_name: (participate === 'yes' ? secondTournament : null),
        slogan: slogan
      };

      $.ajax({
        url: 'https://lanparty.scv.si/api/solo-tournament',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(postData),
        dataType: 'json',
        success: function(response) {
          alert(response.message);
        },
        error: function(xhr, status, error) {
          var res = xhr.responseJSON;
          alert("Napaka: " + (res ? res.message : error));
        }
      });
    });

    // Odjava iz turnirja
    $('#withdraw-btn').click(function() {
      if (!confirm('Ali ste prepričani, da se želite odjaviti iz turnirja?')) return;

      $.ajax({
        url: 'https://lanparty.scv.si/api/solo-tournament/<?php echo htmlspecialchars($user['user_code']); ?>',
        method: 'DELETE',
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
          // Po uspešni odjavi počistimo polja
          $('input[name="participate"][value="no"]').prop('checked', true).change();
          $('#tournament-select').val('');
          $('#second-tournament-select').val('');
          $('#slogan').val('');
          alert(response.message);
        },
        error: function(xhr, status, error) {
          var res = xhr.responseJSON;
          alert("Napaka: " + (res ? res.message : error));
        }
      });
    });
  });
  </script>
</body>
</html>
