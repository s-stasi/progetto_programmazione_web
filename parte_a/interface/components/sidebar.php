<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<aside class="sidebar">
  <nav class="navigation">
    <h2 class="ricerca-titolo">Dashboard</h2>
    <ul>
      <li><a href="index.php" class="nav-link <?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>">Mappa
          Lido</a></li>
      <li><a href="clients.php" class="nav-link <?php echo ($currentPage == 'clients.php') ? 'active' : ''; ?>">Gestione
          Clienti</a></li>
      <li><a href="rates.php" class="nav-link <?php echo ($currentPage == 'rates.php') ? 'active' : ''; ?>">Listino
          Tariffe</a></li>
      <li><a href="contracts.php"
          class="nav-link <?php echo ($currentPage == 'contracts.php') ? 'active' : ''; ?>">Contratti</a></li>
    </ul>
  </nav>

  <div class="sidebar-filters">
    <h2 class="ricerca-titolo">Filtri</h2>

    <form action="" method="GET" class="filters-form" id="<?php 
      if ($currentPage == 'index.php') echo 'form-filtri-mappa';
      elseif ($currentPage == 'rates.php') echo 'form-filtri-tariffe';
    ?>">
      <div class="filters-container">

        <?php
        if ($currentPage == 'index.php') {
          ?>
          <details class="filter-group" open>
            <summary class="filter-title">Intervallo data</summary>
            <div class="filter-content">
              <label>Da:
                <input type="date" id="start-date" name="inizio" value="<?php echo date('Y-m-d'); ?>">
              </label>
              <label>A:
                <input type="date" id="end-date" name="fine" value="<?php echo date('Y-m-d'); ?>">
              </label>
            </div>
          </details>

        <?php
        } elseif ($currentPage == 'rates.php') {
          $da = $_GET['data_da'] ?? date('Y-m-d');
          $a = $_GET['data_a'] ?? date('Y-m-d');
          $isOpenRates = (!empty($_GET['data_da']) || !empty($_GET['data_a'])) ? 'open' : '';
          ?>
          <details class="filter-group" <?php echo $isOpenRates; ?>>
            <summary class="filter-title">Intervallo Data</summary>
            <div class="filter-content">
              <label>Da:
                <input type="date" id="sidebar-tariffe-da" name="data_da" value="<?php echo htmlspecialchars($da); ?>">
              </label>
              <label>A:
                <input type="date" id="sidebar-tariffe-a" name="data_a" value="<?php echo htmlspecialchars($a); ?>">
              </label>
            </div>
          </details>

        <?php
        } elseif ($currentPage == 'contracts.php') {
          $da = $_GET['data_da'] ?? '';
          $a = $_GET['data_a'] ?? '';
          $isOpenContratti = (!empty($da) || !empty($a)) ? 'open' : '';
          ?>
          <details class="filter-group" <?php echo $isOpenContratti; ?>>
            <summary class="filter-title">Intervallo Data</summary>
            <div class="filter-content">
              <label>Da:
                <input type="date" id="sidebar-contratti-da" name="data_da" value="<?php echo htmlspecialchars($da); ?>">
              </label>
              <label>A:
                <input type="date" id="sidebar-contratti-a" name="data_a" value="<?php echo htmlspecialchars($a); ?>">
              </label>
            </div>
          </details>

        <?php
        } elseif ($currentPage == 'clients.php') {
          $cognomeFiltro = $_GET['search_cognome'] ?? '';
          $nomeFiltro = $_GET['search_nome'] ?? '';
          $isOpenName = (!empty($cognomeFiltro) || !empty($nomeFiltro)) ? 'open' : '';

          $annoNascita = $_GET['anno_nascita'] ?? '';
          $isAnnoOpen = (!empty($annoNascita)) ? 'open' : '';

          $emailFiltro = $_GET['search_email'] ?? '';
          $telefonoFiltro = $_GET['search_telefono'] ?? '';
          $isContattiOpen = (!empty($emailFiltro) || !empty($telefonoFiltro)) ? 'open' : '';
          ?>
          <details class="filter-group" <?php echo $isOpenName; ?>>
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
      if (in_array($currentPage, ['clients.php', 'contracts.php', 'rates.php'])):
        $chiaviFiltro = ['search_nome', 'search_cognome', 'anno_nascita', 'search_email', 'search_telefono', 'data_da', 'data_a', 'inizio', 'fine'];
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

<script src="javascript/sidebar.js"></script>