<?php
session_start();
if (isset($_GET['curuser'])) {   //se "curuser" nell'URL vale qualcosa...
  $_SESSION['curuser'] = null;
  $welcome = "You're not logged in yet.<br>You can still post and make threads, but all your posts will have the default nickname and avatar 'Snowflake'.";
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

$boardquer = $pdo->query("SELECT boardname FROM boards ORDER BY rowid ASC");
$boardarr = $boardquer->fetchAll(PDO::FETCH_COLUMN);
$boardescquer = $pdo->query("SELECT description FROM boards");
$boardescarr = $boardescquer->fetchAll(PDO::FETCH_COLUMN);

?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Snowbox</title>
<link rel="icon" href="/pics/favicon.png">
<link rel="stylesheet" href="snowstyle.css">
</head>
<body>

<div class="sez">
  <h1>SNOWBOX</h1>
  <img style="display:block; margin: auto auto;" src="/pics/default.png">
  <audio src="/media/music.mp3" autoplay></audio>
  <h2 style="text-align: center;">Experimental internet forum/imageboard</h2>
  <div class="sez">
    <div class="oriz" style="justify-content: space-around;">
      <a class="butt" href="login.php">LogIn</a>
      <a class="butt" href="signup.php">SignUp</a>
      <a class="butt" href="index.php?curuser=none">LogOut</a>
    </div>
    <?php echo '<p style="text-align: center;">'.$welcome.'</p>'; ?>
    <?php echo $useraccess; ?>
  </div>
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
