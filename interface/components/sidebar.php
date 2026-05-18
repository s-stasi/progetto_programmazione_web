<?php
  $currentPage = basename($_SERVER['PHP_SELF']);
?>

<aside class="sidebar">
  <nav class="navigazione">
    <h2>Dashboard</h2>
    <ul>
      <li><a href="index.php" class="nav-link <?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>">Mappa Lido</a></li>
      <li><a href="clienti.php" class="nav-link <?php echo ($currentPage == 'clienti.php') ? 'active' : ''; ?>">Gestione Clienti</a></li>
      <li><a href="tariffe.php" class="nav-link <?php echo ($currentPage == 'tariffe.php') ? 'active' : ''; ?>">Listino Tariffe</a></li>
      <li><a href="contratti.php" class="nav-link <?php echo ($currentPage == 'contratti.php') ? 'active' : ''; ?>">Contratti</a></li>
    </ul>
  </nav>

  <div class="sidebar-filters">
    <h3 style="margin-bottom: 15px; color: #856404;">Filtri</h3>
    
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

          $data_nascita = $_GET['data_nascita'] ?? '';
          $isOpen = (!empty($data_nascita)) ? 'open' : '';
          ?>
          <details class="filter-group" <?php echo $isOpen; ?>>
            <summary class="filter-title">Nome</summary>
            <div class="filter-content">
              <label>Nome: 
                <input type="text" name="search_nome" placeholder="e.g. Mario" value="<?php echo htmlspecialchars($nomeFiltro); ?>">
              </label>
              <label>Cognome: 
                <input type="text" name="search_cognome" placeholder="e.g. Rossi" value="<?php echo htmlspecialchars($cognomeFiltro); ?>">
              </label>
            </div>
          </details>
          <details class="filter-group" <?php echo $isOpen; ?>>
            <summary class="filter-title">Data di nascita</summary>
            <div class="filter-content">
              <label>Data: 
                <input type="date" name="data_nascita" value="<?php echo htmlspecialchars($data_nascita); ?>">
              </label>
            </div>
          </details>
          
        <?php 
        } else {
          echo "<p style='font-size: 0.85em; color: #888;'>No filters available for this view.</p>";
        } 
        ?>

      </div>
      
      <button type="submit" class="btn-apply">Applica Filti</button>
    </form>
  </div>
</aside>