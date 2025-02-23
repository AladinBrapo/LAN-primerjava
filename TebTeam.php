<?php
// Pridobivanje podatkov o ekipah
$teams = [];
$tournaments = [];
$teamsError = '';
$tournamentsError = '';

$teamsJson = @file_get_contents("https://lanparty.scv.si/api/teams");
if ($teamsJson === false) {
    $teamsError = "Napaka pri pridobivanju ekip.";
} else {
    $teams = json_decode($teamsJson, true);
}

// Pridobivanje podatkov o turnirjih
$tournamentsJson = @file_get_contents("https://lanparty.scv.si/api/tournaments");
if ($tournamentsJson === false) {
    $tournamentsError = "Napaka pri pridobivanju turnirjev.";
} else {
    $tournaments = json_decode($tournamentsJson, true);
}

// Določitev izbranega turnirja (privzeto "All")
$selectedTournament = isset($_GET['tournament']) ? $_GET['tournament'] : "All";

// Filtriranje ekip glede na izbran turnir
if ($selectedTournament === "All") {
    $filteredTeams = $teams;
} else {
    $filteredTeams = array_filter($teams, function($team) use ($selectedTournament) {
        return strval($team['tournament_id']) === strval($selectedTournament);
    });
}

// Razdelitev ekip v skupine po 5 (uporabimo array_chunk)
$teamGroups = array_chunk($filteredTeams, 5);

// Določitev imena izbranega turnirja
if ($selectedTournament === "All") {
    $selectedTournamentName = "vse turnirje";
} else {
    $selectedTournamentName = "neznani turnir";
    foreach ($tournaments as $tournament) {
        if (strval($tournament['id']) === strval($selectedTournament)) {
            $selectedTournamentName = $tournament['name'];
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ekipe</title>
  <link rel="stylesheet" href="css/TebTeam.css">
</head>
<body>
  <div class="team-container">
    <h1 class="team-title">Ekipe</h1>

    <!-- Prikaz morebitnih sporočil o napaki -->
    <?php if ($teamsError): ?>
      <p><?php echo $teamsError; ?></p>
    <?php endif; ?>
    <?php if ($tournamentsError): ?>
      <p><?php echo $tournamentsError; ?></p>
    <?php endif; ?>

    <!-- Izbira turnirja (filter) -->
    <form method="GET" action="">
      <div class="filter-section">
        <label for="tournament-select" class="filter-label">Izberi turnir:</label>
        <select name="tournament" id="tournament-select" class="tournament-select" onchange="this.form.submit()">
          <option value="All" <?php if ($selectedTournament === "All") echo 'selected'; ?>>Vsi turnirji</option>
          <?php foreach ($tournaments as $tournament): ?>
            <option value="<?php echo $tournament['id']; ?>" <?php if ($selectedTournament == $tournament['id']) echo 'selected'; ?>>
              <?php echo htmlspecialchars($tournament['name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <p class="registration-count">
          <?php echo count($filteredTeams); ?> ekip registriranih za <?php echo htmlspecialchars($selectedTournamentName); ?>
        </p>
      </div>
    </form>

    <!-- Prikaz ekip -->
    <?php if (empty($teams)): ?>
      <p>Še ni prijavljene nobene ekipe.</p>
    <?php else: ?>
      <div class="team-groups">
        <?php foreach ($teamGroups as $index => $group): ?>
          <div class="team-group">
            <h2>Ekipa <?php echo ($index * 5) + 1; ?> - <?php echo ($index * 5) + count($group); ?></h2>
            <div class="team-list">
              <?php foreach ($group as $team): ?>
                <button class="team-button"><?php echo htmlspecialchars($team['name']); ?></button>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
