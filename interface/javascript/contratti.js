async function deleteContract(id) {
  if (!confirm(`Sei sicuro di voler cancellare il contratto #${id}? Questo libererà gli ombrelloni associati.`)) {
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
    alert("Errore tecnico durante la cancellazione del contratto");
    console.error(e);
  }
}