<?php
session_start();
if (isset($_GET['curuser'])) {   //se "curuser" nell'URL vale qualcosa...
  $_SESSION['curuser'] = null;
  $welcome = "You're not logged in yet.";
} else {
  $welcome = "You're currently logged in as <strong>".$_SESSION['curuser']."</strong>.";
  $useraccess = "<a class='butt' href='usareat.php'>Your dashboard</a>";
}
$_SESSION['curboard'] = null;
$_SESSION['curthread'] = null;

try {
  $pdo = new PDO("sqlite:database.db");
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("ERRORE! NON E' STATO POSSIBILE CONNETTERSI AL DATABASE." . $e->getMessage());
}

$boardquer = $pdo->query("SELECT name FROM boards");
$boardarr = $boardquer->fetchAll(PDO::FETCH_COLUMN);
$boardescquer = $pdo->query("SELECT description FROM boards");
$boardescarr = $boardescquer->fetchAll(PDO::FETCH_COLUMN);

?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Snowbox</title>
<link rel="stylesheet" href="snowstyle.css">
</head>
<body>

<div class="sez">
  <h1>SNOWBOX</h1>

  <p>Welcome to Snowbox.</p>
  <div class="oriz">
    <a class="butt" href="signup.php">SignUp</a>
    <a class="butt" href="index.php?curuser=none">LogOut</a>
    <a class="butt" href="login.php">LogIn</a>
  </div>
  <?php echo $welcome; ?>
  <?php echo $useraccess; ?>
  <hr>
  <?php
    $j=-1;
    foreach ($boardarr as $i) {
      $j=$j+1;
      echo '<a class="butt" href="boardt.php?curboard='.$i.'"><strong>'.$i.'</strong><br><em>'.$boardescarr[$j].'</em></a>';
    }
  ?>

</div>

<?php   // CODICE DI CONTROLLO SESSIONE, ELIMINARE UNA VOLTA FINITO
foreach ($_SESSION as $i) {
  echo '<p>'.$i.'</p>';
}
?>

</body>
</html>
