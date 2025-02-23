<?php
session_start();
// Preveri, če je uporabnik prijavljen (prilagodi glede na svoj sistem)
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
$user = $_SESSION['user']; // npr. vsebuje 'username' in 'user_code'
?>
<!DOCTYPE html>
<html lang="sl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registracija ekipe</title>
  <link rel="stylesheet" href="css/teb_register_team.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="teb-register-team-container">
  <!-- Modal za ustvarjanje nove ekipe -->
  <div id="createTeamModal" class="modal-overlay" style="display: none;">
    <div class="modal">
      <h2>Želite ustvariti ekipo?</h2>
      <label for="team-name">Ime ekipe:</label>
      <input type="text" id="team-name" placeholder="Vnesite ime ekipe">
      <label for="select-tournament">Izberite turnir:</label>
      <select id="select-tournament">
        <option value="" disabled selected>Izberite turnir</option>
      </select>
      <div class="modal-buttons">
        <button class="cancel-button" id="create-team-btn">Ustvari ekipo</button>
        <button class="cancel-button" id="cancel-create-team-btn">Ne</button>
      </div>
    </div>
  </div>

  <!-- Prikaz informacij o ekipi, če obstaja -->
  <div id="teamInfo" style="display: none;">
    <div class="team-info">
      <h1 id="teamNameDisplay"></h1>
      <h2>Turnir: <span id="teamTournamentDisplay"></span></h2>
      <h3>Člani ekipe:</h3>
      <ul id="teamMembersList"></ul>

      <!-- Možnost dodajanja novega člana (samo za vodjo ekipe) -->
      <div id="addMemberSection" style="display: none;">
        <h3>Dodaj novega člana</h3>
        <input type="text" id="memberCodeInput" placeholder="Vnesite kodo člana">
        <button class="team-button" id="addMemberBtn">Dodaj</button>
      </div>

      <!-- Gumb za brisanje ekipe (samo za vodjo ekipe) -->
      <button class="delete-team-button" id="deleteTeamBtn" style="display: none;">Izbriši ekipo</button>
    </div>
  </div>

  <!-- Sporočilo, če ekipa ne obstaja -->
  <div id="noTeamMessage" style="display: none;">
    <h1>Ekipa ni bila najdena. Ustvarite novo ekipo.</h1>
  </div>
</div>

<script>
$(document).ready(function(){
    var userCode = '<?php echo htmlspecialchars($user['user_code']); ?>';
    var team = null;
    var teamId = null;
    var isTeamLeader = false;
    var tournaments = [];
    
    // Pridobi ekipo uporabnika
    function fetchTeam() {
        $.ajax({
            url: 'https://lanparty.scv.si/api/team-by-user/' + userCode,
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                team = data.team;
                teamId = team.team_id;
                isTeamLeader = (team.member_1_code === userCode);
                renderTeamInfo();
            },
            error: function(xhr) {
                if (xhr.status === 404) {
                    console.log('Uporabnik še ni član nobene ekipe.');
                    // Prikaži modal za ustvarjanje ekipe in sporočilo
                    $('#createTeamModal').show();
                    $('#noTeamMessage').show();
                } else {
                    console.error('Napaka pri pridobivanju ekipe.');
                }
            }
        });
    }
    
    // Pridobi seznam turnirjev
    function fetchTournaments() {
        $.ajax({
            url: 'https://lanparty.scv.si/api/tournaments',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                tournaments = data;
                populateTournamentSelect();
            },
            error: function(xhr) {
                console.error('Napaka pri pridobivanju turnirjev.');
            }
        });
    }
    
    // Napolni dropdown z opcijami turnirjev
    function populateTournamentSelect() {
        var options = '<option value="" disabled selected>Izberite turnir</option>';
        $.each(tournaments, function(index, tournament) {
            options += '<option value="'+tournament.id+'">'+tournament.name+'</option>';
        });
        $('#select-tournament').html(options);
    }
    
    // Pomagajna funkcija za iskanje imena turnirja po id-ju
    function getTournamentName(id) {
        var tournament = tournaments.find(function(t) {
            return String(t.id) === String(id);
        });
        return tournament ? tournament.name : 'Ni izbran';
    }
    
    // Prikaz informacij o ekipi, če ekipa obstaja
    function renderTeamInfo() {
        if (team) {
            $('#noTeamMessage').hide();
            $('#teamInfo').show();
            $('#teamNameDisplay').text('Ime ekipe: ' + team.team_name);
            $('#teamTournamentDisplay').text(getTournamentName(team.tournament_id));
            
            // Prikaži člane ekipe
            var membersHtml = '';
            for(var i = 1; i <= 5; i++){
                var nameKey = 'member_' + i + '_name';
                var surnameKey = 'member_' + i + '_surname';
                var codeKey = 'member_' + i + '_code';
                if(team[nameKey] && team[surnameKey]) {
                    membersHtml += '<li class="team-member">'+team[nameKey]+' '+team[surnameKey];
                    if(isTeamLeader && team.member_1_code !== team[codeKey]){
                        membersHtml += ' <button class="remove-member-button" data-code="'+team[codeKey]+'" data-key="'+codeKey+'">❌</button>';
                    }
                    membersHtml += '</li>';
                }
            }
            $('#teamMembersList').html(membersHtml);
            
            // Prikaži oddelek za dodajanje člana in gumb za brisanje ekipe, če je uporabnik vodja ekipe
            if(isTeamLeader){
                $('#addMemberSection').show();
                $('#deleteTeamBtn').show();
            } else {
                $('#addMemberSection').hide();
                $('#deleteTeamBtn').hide();
            }
        } else {
            $('#teamInfo').hide();
            $('#noTeamMessage').show();
        }
    }
    
    // Ustvari novo ekipo
    $('#create-team-btn').click(function(){
        var newTeamName = $('#team-name').val();
        var selectedTournament = $('#select-tournament').val();
        if(!newTeamName || !selectedTournament){
            alert('Prosim, vnesite ime ekipe in izberite turnir.');
            return;
        }
        $.ajax({
            url: 'https://lanparty.scv.si/api/teams',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                name: newTeamName,
                member_1: userCode,
                tournament_id: selectedTournament
            }),
            dataType: 'json',
            success: function(data) {
                alert('Ekipa uspešno ustvarjena!');
                $('#createTeamModal').hide();
                teamId = data.team_id;
                team = {
                    team_name: newTeamName,
                    tournament_id: selectedTournament,
                    member_1_code: userCode
                };
                renderTeamInfo();
            },
            error: function(xhr) {
                var res = xhr.responseJSON;
                alert('Napaka: ' + (res ? res.message : 'Napaka pri ustvarjanju ekipe.'));
            }
        });
    });
    
    // Prekliči ustvarjanje ekipe
    $('#cancel-create-team-btn').click(function(){
        $('#createTeamModal').hide();
    });
    
    // Dodaj novega člana
    $('#addMemberBtn').click(function(){
        var memberCode = $('#memberCodeInput').val();
        if(!teamId){
            alert('Ekipa ni bila najdena.');
            return;
        }
        if(!memberCode){
            alert('Prosim, vnesite kodo člana.');
            return;
        }
        $.ajax({
            url: 'https://lanparty.scv.si/api/update-team-members/' + teamId,
            method: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify({ memberCode: memberCode }),
            dataType: 'json',
            success: function(data) {
                alert('Član uspešno dodan!');
                $('#memberCodeInput').val('');
                // Osveži podatke o ekipi
                fetchTeam();
            },
            error: function(xhr) {
                var res = xhr.responseJSON;
                alert('Napaka: ' + (res ? res.message : 'Napaka pri dodajanju člana.'));
            }
        });
    });
    
    // Odstrani člana (dogodek delegiran na seznamu)
    $('#teamMembersList').on('click', '.remove-member-button', function(){
        var memberCode = $(this).data('code');
        var memberKey = $(this).data('key');
        if(!confirm('Ali res želite izbrisati tega člana iz ekipe?')) return;
        $.ajax({
            url: 'https://lanparty.scv.si/api/remove-team-member/' + teamId,
            method: 'DELETE',
            contentType: 'application/json',
            data: JSON.stringify({ memberCode: memberCode, requesterCode: userCode }),
            dataType: 'json',
            success: function(data) {
                alert(data.message);
                // Osveži podatke o ekipi
                fetchTeam();
            },
            error: function(xhr) {
                var res = xhr.responseJSON;
                alert('Napaka: ' + (res ? res.message : 'Napaka pri odstranjevanju člana.'));
            }
        });
    });
    
    // Izbriši ekipo
    $('#deleteTeamBtn').click(function(){
        if(!confirm('Ali res želite izbrisati to ekipo? Ta operacija je nepovratna.')) return;
        $.ajax({
            url: 'https://lanparty.scv.si/api/delete-team/' + teamId,
            method: 'DELETE',
            contentType: 'application/json',
            data: JSON.stringify({ requesterCode: userCode }),
            dataType: 'json',
            success: function(data) {
                alert(data.message);
                team = null;
                renderTeamInfo();
                // Po brisanju prikaži modal za ustvarjanje nove ekipe in sporočilo
                $('#createTeamModal').show();
                $('#noTeamMessage').show();
            },
            error: function(xhr) {
                var res = xhr.responseJSON;
                alert('Napaka: ' + (res ? res.message : 'Napaka pri brisanju ekipe.'));
            }
        });
    });
    
    // Inicialna klica
    fetchTournaments();
    fetchTeam();
});
</script>
</body>
</html>
