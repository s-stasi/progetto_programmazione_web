<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<aside class="sidebar">
  <nav class="navigation">
    <h2 class="ricerca-titolo">Dashboard</h2>
    <ul>
      <li><a href="index.php" class="nav-link <?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>">Mappa
          Lido</a></li>
      <li><a href="clienti.php" class="nav-link <?php echo ($currentPage == 'clienti.php') ? 'active' : ''; ?>">Gestione
          Clienti</a></li>
      <li><a href="tariffe.php" class="nav-link <?php echo ($currentPage == 'tariffe.php') ? 'active' : ''; ?>">Listino
          Tariffe</a></li>
      <li><a href="contratti.php"
          class="nav-link <?php echo ($currentPage == 'contratti.php') ? 'active' : ''; ?>">Contratti</a></li>
    </ul>
  </nav>

  <div class="sidebar-filters">
    <h2 class="ricerca-titolo">Filtri</h2>

    <form action="" method="GET" class="filters-form" <?php echo ($currentPage == 'index.php') ? 'id="form-filtri-mappa"' : ''; ?>>
      <div class="filters-container">

        <?php
        // --- FILTERS FOR INDEX (OPERATIONAL MAP) ---
        if ($currentPage == 'index.php') {
          ?>
          <details class="filter-group" open>
            <summary class="filter-title">Intervallo data</summary>
            <div class="filter-content">
              <label>Da:
                <input type="date" id="start-date">
              </label>
              <label>A:
                <input type="date" id="end-date">
              </label>
            </div>
          </details>

          <?php
          // --- FILTERS FOR CONTRATTI ---
        } elseif ($currentPage == 'contratti.php') {
          $da = $_GET['data_da'] ?? '';
          $a = $_GET['data_a'] ?? '';
          $isOpen = (!empty($da) || !empty($a)) ? 'open' : '';
          ?>
          <details class="filter-group" <?php echo $isOpen; ?>>
            <summary class="filter-title">Intervallo Data</summary>
            <div class="filter-content">
              <label>Da:
                <input type="date" name="data_da" value="<?php echo htmlspecialchars($da); ?>">
              </label>
              <label>A:
                <input type="date" name="data_a" value="<?php echo htmlspecialchars($a); ?>">
              </label>
            </div>
          </details>

          <?php
          // --- FILTERS FOR CLIENTI ---
        } elseif ($currentPage == 'clienti.php') {
          $cognomeFiltro = $_GET['search_cognome'] ?? '';
          $nomeFiltro = $_GET['search_nome'] ?? '';
          $isOpen = (!empty($cognomeFiltro) || !empty($nomeFiltro)) ? 'open' : '';

          $annoNascita = $_GET['anno_nascita'] ?? '';
          $emailFiltro = $_GET['search_email'] ?? '';
          $telefonoFiltro = $_GET['search_telefono'] ?? '';
          $isOpen = (!empty($data_nascita)) ? 'open' : '';
          $isAnnoOpen = (!empty($annoNascita)) ? 'open' : '';
          $isContattiOpen = (!empty($emailFiltro) || !empty($telefonoFiltro)) ? 'open' : '';
          ?>
          <details class="filter-group" <?php echo $isOpen; ?>>
            <summary class="filter-title">Nome</summary>
            <div class="filter-content">
              <label>Nome:
                <input type="text" name="search_nome" placeholder="e.g. Mario"
                  value="<?php echo htmlspecialchars($nomeFiltro); ?>">
              </label>
              <label>Cognome:
                <input type="text" name="search_cognome" placeholder="e.g. Rossi"
                  value="<?php echo htmlspecialchars($cognomeFiltro); ?>">
              </label>
            </div>
          </details>
          <details class="filter-group" <?php echo $isAnnoOpen; ?>>
            <summary class="filter-title">Anno di nascita</summary>
            <div class="filter-content">
              <label>Anno:
                <input type="number" name="anno_nascita" placeholder="es. 1990"
                  value="<?php echo htmlspecialchars($annoNascita); ?>">
              </label>
            </div>
          </details>
          <details class="filter-group" <?php echo $isContattiOpen; ?>>
            <summary class="filter-title">Contatti</summary>
            <div class="filter-content">
              <label>Email:
                <input type="text" name="search_email" placeholder="es. mario@email.it"
                  value="<?php echo htmlspecialchars($emailFiltro); ?>">
              </label>
              <label>Telefono:
                <input type="text" name="search_telefono" placeholder="es. 333123..."
                  value="<?php echo htmlspecialchars($telefonoFiltro); ?>">
              </label>
            </div>
          </details>
          <?php
        } else {
          echo "<p class='no-filters-msg'>No filtri attivi</p>";
        }
        ?>

      </div>

      <button type="submit" class="btn-apply">Applica Filtri</button>

      <?php
      if ($currentPage == 'clienti.php' || $currentPage == 'contratti.php'):

        $chiaviFiltro = ['search_nome', 'search_cognome', 'anno_nascita', 'search_email', 'search_telefono', 'data_da', 'data_a'];
        $haFiltriAttivi = false;

        foreach ($chiaviFiltro as $chiave) {
          if (!empty($_GET[$chiave])) {
            $haFiltriAttivi = true;
            break;
          }
        }

        if ($haFiltriAttivi):
          ?>
          <a href="<?php echo $currentPage; ?>"
            style="text-align: center; margin-top: 15px; display: block; color: var(--text-muted, #888); font-size: 13px; text-decoration: none;">Resetta
            Filtri</a>
        <?php
        endif;
      endif;
      ?>
    </form>
  </div>
</aside>

<script>
document.addEventListener("DOMContentLoaded", () => {
  // 1. Controllo per la pagina Contratti (contratti.php)
  const startContratti = document.getElementById('sidebar-contratti-da');
  const endContratti = document.getElementById('sidebar-contratti-a');

  if (startContratti && endContratti) {
    startContratti.addEventListener('input', () => {
      // Imposta il vincolo 'min' nativo sul calendario del campo di fine
      endContratti.min = startContratti.value;
      
      // Se la data di fine inserita diventa inferiore a quella d'inizio, la allinea automaticamente
      if (endContratti.value && endContratti.value < startContratti.value) {
        endContratti.value = startContratti.value;
      }
    });
  }

  // 2. Controllo per la Mappa Lido (index.php)
  const startMap = document.getElementById('start-date');
  const endMap = document.getElementById('end-date');

  if (startMap && endMap) {
    startMap.addEventListener('input', () => {
      endMap.min = startMap.value;
      if (endMap.value && endMap.value < startMap.value) {
        endMap.value = startMap.value;
      }
    });
  }
});
</script>
