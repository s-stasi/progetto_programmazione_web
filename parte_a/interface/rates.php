<?php include 'components/header.php'; ?>
<?php include 'components/sidebar.php'; ?>

<link rel="stylesheet" href="../css/card.css">

<main class="page-body">
  <div class="table-header">
    <h2 class="ricerca-titolo">Listino Tariffe </h2>
  </div>

  <div class="cards-container">
    <?php
    $tipologie = ['Base', 'VIP', 'Gazebo', 'Disabile'];
    foreach ($tipologie as $tipo) {
        $labelVisualizzazione = strtoupper($tipo);
        
        echo "
        <div class='tariffa-card' data-tipo='$tipo'>
          
          <div class='ios-header-card'>
            <h2 class='txt-oro-main'>$labelVisualizzazione</h2>
          </div>

          <div class='card-body-ios'>
            <div class='ios-row-container-card'>
              
              <div class='ios-date-info-card'>
                <span class='txt-grigio-medium-label'>PERIODO D'INTERESSE</span>
                <div class='card-date-render-wrapper'>
                  <span class='card-date-render'>Giorno: " . date('d/m/Y') . "</span>
                </div>
              </div>

              <div class='ios-price-box-card'>
                <span class='txt-grigio-medium-label'>PREVENTIVO TOTALE</span>
                <div class='prezzo-valore txt-oro-main'>
                  €<span class='prezzo-render'>0.00</span>
                </div>
              </div>

            </div>

            <div class='card-receipt'>
              </div>

          </div>

        </div>";
    }
    ?>
  </div>
</main>

<script src="javascript/rates.js"></script>
<script src="components/javascript/sidebar.js"></script>

<?php include 'components/footer.php'; ?>