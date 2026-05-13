<?php include 'components/header.php'; ?>
<?php include 'components/sidebar.php'; ?>

<main class="corpo-pagina">
  <div class="table-header">
    <h2>Tariff Management</h2>
    <button class="btn-primary" onclick="alert('Form in arrivo!')">+ Add New Tariff</button>
  </div>

  <div class="table-container">
    <table class="gestionale-table">
      <thead>
        <tr>
          <th>Code</th>
          <th>Start Date</th>
          <th>End Date</th>
          <th>Min. Days</th>
          <th>Price (€)</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        require_once('../php/config.php');
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($conn->connect_error) {
          echo "<tr><td colspan='6' style='color:red;'>Database connection failed: " . $conn->connect_error . "</td></tr>";
        } else {
          // Fetch tariffs ordering by date (newest first) and then by minimum days
          $sql = "SELECT codice, dataInizio, dataFine, numMinGiorni, prezzo FROM Tariffa ORDER BY dataInizio DESC, numMinGiorni ASC LIMIT 50";
          $result = $conn->query($sql);

          if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
              echo "<tr>";
              // Use null coalescing to provide an empty string if null
              echo "<td>" . htmlspecialchars($row['codice'] ?? '') . "</td>";
              
              // Safely handle potentially null dates
              $start = !empty($row['dataInizio']) ? date('d/m/Y', strtotime($row['dataInizio'])) : '-';
              $end = !empty($row['dataFine']) ? date('d/m/Y', strtotime($row['dataFine'])) : '-';
              
              echo "<td>" . htmlspecialchars($start) . "</td>";
              echo "<td>" . htmlspecialchars($end) . "</td>";
              
              // Handle null minimum days
              $minDays = $row['numMinGiorni'] ?? 0;
              echo "<td>" . htmlspecialchars((string)$minDays) . " days</td>";
              
              // Handle null price and format it
              $priceValue = $row['prezzo'] ?? 0;
              $price = number_format((float)$priceValue, 2, ',', '.');
              echo "<td><strong>€ " . htmlspecialchars($price) . "</strong></td>";
              
              // Action buttons
              echo "<td>
                      <button class='btn-edit'>Edit</button>
                      <button class='btn-delete' onclick='deleteTariff(\"" . htmlspecialchars($row['codice'] ?? '') . "\")'>Delete</button>
                    </td>";
              echo "</tr>";
            }
          } else {
            echo "<tr><td colspan='6' style='text-align: center; padding: 20px;'>No tariffs found in the database.</td></tr>";
          }
          $conn->close();
        }
        ?>
      </tbody>
    </table>
  </div>
</main>

<script>
  // Placeholder function for future delete API
  function deleteTariff(code) {
    if (confirm(`Are you sure you want to delete tariff ${code}?`)) {
      alert("API di cancellazione da implementare!");
      // Logic for deleting will go here
    }
  }
</script>

<?php include 'components/footer.php'; ?>