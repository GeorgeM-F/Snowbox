<?php
session_start();
try {
  $pdo = new PDO("sqlite:database.db");
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("ERRORE! NON E' STATO POSSIBILE CONNETTERSI AL DATABASE." . $e->getMessage());
}

$messaggio = "";

// DATA LIST QUERY
$datquer = $pdo->prepare("SELECT * FROM users WHERE nick = :nick");     // Fa una query di tutte le prove presenti nella tabella...
$datquer->execute([':nick' => $_SESSION["curuser"]]);
$datarr = $datquer->fetchAll(PDO::FETCH_ASSOC);     // ...e le salva come array.

// DATA EDIT QUERY (ADD LATER)

?>



<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Snowbox - User Area</title>
<link rel="stylesheet" href="snowstyle.css">
</head>
<body>

<div class="sez">
  <h1>YOUR AREA</h1>
  <?php echo "<p>Your nickname: <strong>".$datarr[0]["nick"]."</strong></p>"; ?>
  <form action="" method="POST" enctype="multipart/form-data">
    <label>Change nickname:</label>
    <input type="text" name="nick" required>
    <button type="submit">OK</button>
    <?php echo $messaggio; ?>
  </form>
  <?php echo "<p>Your avatar: <strong>".$datarr[0]["avatar"]."</strong></p>"; ?>
    <form action="" method="POST" enctype="multipart/form-data">
    <label>Change avatar:</label>
    <input type="file" name="avatar" accept="image/*" required>
    <button type="submit">OK</button>
    <?php echo $messaggio; ?>
  </form>
  <?php echo "<p>Your password: <strong>".$datarr[0]["password"]."</strong></p>"; ?>
  <form action="" method="POST" enctype="multipart/form-data">
    <label>Change password:</label>
    <input type="text" name="nick" required>
    <button type="submit">OK</button>
    <?php echo $messaggio; ?>
  </form>

  <a class="bot" href="index.php">BACK</a>
</div>

<?php   // CODICE DI CONTROLLO SESSIONE, ELIMINARE UNA VOLTA FINITO
foreach ($_SESSION as $i) {
  echo '<p>'.$i.'</p>';
}
?>

</body>
</html>
