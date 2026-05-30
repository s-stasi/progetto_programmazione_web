<?php include 'components/header.php'; ?>
<?php include 'components/sidebar.php'; ?>

<main class="page-body">
  <div class="table-header">
    <h2 class="ricerca-titolo">Mappa Spiaggia</h2>
  </div>

  <div class="legend">
    <div class="item"><span class="badge free"></span> Disponibile</div>
    <div class="item"><span class="badge reserved"></span> Occupato</div>
    <div class="item"><span class="badge disable"></span> Disabili</div>
  </div>

  <div class="sea">MARE</div>
  <div id="grid"></div>
</main>

<?php include 'popup.php'; ?>

<script src="javascript/index.js"></script>

<?php include 'components/footer.php'; ?>