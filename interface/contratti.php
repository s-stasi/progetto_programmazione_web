<?php include 'components/header.php'; ?>
<?php include 'components/sidebar.php'; ?>

<main class="corpo-pagina">
  <div class="table-header">
    <h2>Contract Management</h2>
    <button class="btn-primary" onclick="alert('Proceed to Operational Map to create a new contract')">New Reservation</button>
  </div>

  <div class="table-container">
    <table class="gestionale-table">
      <thead>
        <tr>
          <th>Prog. #</th>
          <th>Date</th>
          <th>Client</th>
          <th>Total Amount</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        require_once('../php/config.php');
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($conn->connect_error) {
          echo "<tr><td colspan='5' style='color:red;'>Database connection failed: " . $conn->connect_error . "</td></tr>";
        } else {
          // Fetch contracts with client details using a join
          $sql = "SELECT c.numProgr, c.data, c.importo, cl.nome, cl.cognome 
                  FROM Contratto c
                  JOIN Cliente cl ON c.stipulatoDa = cl.codice
                  ORDER BY c.data DESC, c.numProgr DESC 
                  LIMIT 50";
          
          $result = $conn->query($sql);

          if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
              echo "<tr>";
              echo "<td>#" . htmlspecialchars($row['numProgr'] ?? '') . "</td>";
              
              // Format contract date
              $date = !empty($row['data']) ? date('d/m/Y', strtotime($row['data'])) : '-';
              echo "<td>" . htmlspecialchars($date) . "</td>";
              
              // Combine Name and Surname
              $clientName = ($row['nome'] ?? '') . " " . ($row['cognome'] ?? '');
              echo "<td>" . htmlspecialchars($clientName) . "</td>";
              
              // Format currency
              $amountValue = $row['importo'] ?? 0;
              $amount = number_format((float)$amountValue, 2, ',', '.');
              echo "<td><strong>€ " . htmlspecialchars($amount) . "</strong></td>";
              
              // Action buttons
              echo "<td>
                      <button class='btn-edit'>View Details</button>
                      <button class='btn-delete' onclick='deleteContract(" . ($row['numProgr'] ?? 0) . ")'>Cancel</button>
                    </td>";
              echo "</tr>";
            }
          } else {
            echo "<tr><td colspan='5' style='text-align: center; padding: 20px;'>No contracts found in the database.</td></tr>";
          }
          $conn->close();
        }
        ?>
      </tbody>
    </table>
  </div>
</main>

<script>
  async function deleteContract(id) {
    if (!confirm(`Are you sure you want to cancel contract #${id}? This will free up the associated umbrellas.`)) {
      return;
    }

    try {
      const response = await fetch(`../php/delete_contract.php?id=${id}`);
      const result = await response.json();

      if (result.success) {
        alert(result.message);
        location.reload();
      } else {
        alert("ERROR: " + result.message);
      }
    } catch (e) {
      alert("Technical error during contract cancellation");
      console.error(e);
    }
  }
</script>

<?php include 'components/footer.php'; ?>