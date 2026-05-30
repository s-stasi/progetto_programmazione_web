<?php 
include 'components/header.php'; 
include 'components/sidebar.php'; 
require_once '../php/config.php'; 

// Connessione al database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

$filtro_settore = $_GET['settore'] ?? '';
$filtro_fila = $_GET['fila'] ?? '';

// Costruisco la query base con una JOIN per avere il nome della tipologia
$sql = "SELECT o.id, o.settore, o.numFila, o.numPostoFila, t.nome AS nome_tipologia 
        FROM Ombrellone o 
        JOIN Tipologia t ON o.tipologia = t.codice 
        WHERE 1=1";

if ($filtro_settore !== '') {
    $sql .= " AND o.settore = '" . $conn->real_escape_string($filtro_settore) . "'";
}
if ($filtro_fila !== '') {
    $sql .= " AND o.numFila = '" . $conn->real_escape_string($filtro_fila) . "'";
}

$sql .= " ORDER BY o.settore, o.numFila, o.numPostoFila";
$result = $conn->query($sql);
?>

<main class="page-body">
  <div class="table-header">
      <h2 class="ricerca-titolo">Ricerca Ombrelloni</h2>
  </div>

  <div class="ricerca-box">
      <form method="GET" action="ricercaombrelloni.php" class="form-ricerca-inline">
          <div class="form-group">
              <label>Settore:</label>
              <select name="settore">
                  <option value="">Tutti</option>
                  <option value="1" <?= $filtro_settore == '1' ? 'selected' : '' ?>>Settore A</option>
                  <option value="2" <?= $filtro_settore == '2' ? 'selected' : '' ?>>Settore B</option>
                  <option value="3" <?= $filtro_settore == '3' ? 'selected' : '' ?>>Settore C</option>
                  <option value="4" <?= $filtro_settore == '4' ? 'selected' : '' ?>>Settore D</option>
                  <option value="5" <?= $filtro_settore == '5' ? 'selected' : '' ?>>Settore E</option>
              </select>
          </div>
          <div class="form-group">
              <label>Numero Fila:</label>
              <input type="number" name="fila" value="<?= htmlspecialchars($filtro_fila) ?>" placeholder="Es. 1">
          </div>
          <button type="submit" class="btn-primary">Cerca</button>
          <a href="ricercaombrelloni.php" class="btn-reset">Resetta</a>
      </form>
  </div>

  <div class="table-container">
      <table class="gestionale-table">
          <thead>
              <tr>
                  <th>ID Ombrellone</th>
                  <th>Settore</th>
                  <th>Fila</th>
                  <th>Posto</th>
                  <th>Tipologia</th>
                  <th>Azioni</th>
              </tr>
          </thead>
          <tbody>
              <?php if ($result && $result->num_rows > 0): ?>
                  <?php while($row = $result->fetch_assoc()): ?>
                      <?php 
                          // Mappatura numeri settore a lettere
                          $lettere = [1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D', 5 => 'E'];
                          $lettera_settore = $lettere[$row['settore']] ?? $row['settore'];
                      ?>
                      <tr>
                          <td><strong>#<?= $row['id'] ?></strong></td>
                          <td>
                              <a href="ricercaombrelloni.php?settore=<?= $row['settore'] ?>">
                                  <?= $lettera_settore ?>
                              </a>
                          </td>
                          <td><?= $row['numFila'] ?></td>
                          <td><?= $row['numPostoFila'] ?></td>
                          <td><?= $row['nome_tipologia'] ?></td>
                          <td>
                              <button class="btn-edit" onclick="vediDettagli(<?= $row['id'] ?>)">Dettagli</button>
                          </td>
                      </tr>
                  <?php endwhile; ?>
              <?php else: ?>
                  <tr>
                      <td colspan="6" class="center-text">Nessun ombrellone trovato con questi filtri.</td>
                  </tr>
              <?php endif; ?>
          </tbody>
      </table>
  </div>
</main>

<script src="javascript/ricercaombrelloni.js"></script>

<?php 
$conn->close();
include 'components/footer.php'; 
?>