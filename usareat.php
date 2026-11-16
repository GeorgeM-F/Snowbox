<?php
session_start();
try {
  $pdo = new PDO("sqlite:database.db");
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("ERRORE! NON E' STATO POSSIBILE CONNETTERSI AL DATABASE." . $e->getMessage());
}

$message0 = "";
$message1 = "";
$message2 = "";

// DATA LIST QUERY
$datquer = $pdo->prepare("SELECT * FROM users WHERE nick = :nick");
$datquer->execute([':nick' => $_SESSION["curuser"]]);
$datarr = $datquer->fetchAll(PDO::FETCH_ASSOC);

// DATA EDIT QUERIES (ONE FOR EACH FORM)
$curuser=$_SESSION["curuser"];

//if ($_SERVER["REQUEST_METHOD"] == "POST" and isset($_POST['nick'])) {
//  $nick = isset($_POST['nick']) ? trim($_POST['nick']) : '';
//  // CONTROLLO EVENTUALI DOPPIONI
//  $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE nick = :nick");     // QUERY (PER L'INSERIMENTO DEI DATI IN MODO SICURO: invece dei valori usiamo dei segnaposto (":"))
//  $stmt->execute([':nick' => $nick]);
//  if ($stmt->fetchColumn() > 0) {
//    0 = "<p style='color: red;'>NICKNAME ALREADY USED: CHOOSE ANOTHER ONE.";
//  } else {
//    $stmt = $pdo->prepare("UPDATE users SET nick = :nick WHERE nick = :curuser");     // QUERY (PER L'INSERIMENTO DEI DATI IN MODO SICURO: invece dei valori usiamo dei segnaposto (":"))
//    $stmt->bindParam(':nick', $nick, PDO::PARAM_STR);
//    $stmt->bindParam(':curuser', $curuser, PDO::PARAM_STR);
//    if ($stmt->execute()) {     // Definisce le parti di HTML da visualizzare a seconda dell'esito
//      $message0 = "<p class='mess' style='color: rgba(0,255,255,1);'>NICKNAME CHANGED!</p>";
//    } else {
//      $message0 = "<p class='mess' style='color: red;'>ERROR: SOMETHING WENT WRONG</p>";
//    }
//  }
//}

if ($_SERVER["REQUEST_METHOD"] == "POST" and isset($_FILES['avatar'])) {
  $avatar = '/pics/'.basename($_FILES['avatar']['tmp_name']);   // loaded file name, modified in order to point at the "pics" directory
  // DOUBLE-RECORD CHECK
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE avatar = :avatar");     // SAFE INSERTION QUERY
  $stmt->execute([':avatar' => $avatar]);
  if ($stmt->fetchColumn() > 0) {
    $message1 = "<p style='color: red;'>AVATAR ALREADY USED: CHOOSE ANOTHER ONE.";
  } else {
    $stmt = $pdo->prepare("UPDATE users SET avatar = :avatar WHERE nick = :curuser");     // SAFE INSERTION QUERY
    $stmt->bindParam(':avatar', $avatar, PDO::PARAM_STR);
    $stmt->bindParam(':curuser', $curuser, PDO::PARAM_STR);
    if ($stmt->execute()) {
      $message1 = "<p class='mess' style='color: rgba(0,255,255,1);'>AVATAR CHANGED!</p>";
    } else {
      $message1 = "<p class='mess' style='color: red;'>ERROR: SOMETHING WENT WRONG</p>";
    }
    move_uploaded_file($_FILES['avatar']['tmp_name'], __DIR__.'/pics/'.basename($_FILES['avatar']['tmp_name']));
  }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" and isset($_POST['password'])) {
  $password = isset($_POST['password']) ? trim($_POST['password']) : '';
  $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE nick = :curuser");
  $stmt->bindParam(':password', password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR);
  $stmt->bindParam(':curuser', $curuser, PDO::PARAM_STR);
  if ($stmt->execute()) {
    $message2 = "<p class='mess' style='color: rgba(0,255,255,1);'>PASSWORD CHANGED!</p>";
  } else {
    $message2 = "<p class='mess' style='color: red;'>ERROR: SOMETHING WENT WRONG</p>";
  }
}
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
  <hr>
  <?php echo "<p>Your nickname: <strong>".$datarr[0]["nick"]."</strong></p>"; ?>
<!--  <form action="" method="POST" enctype="multipart/form-data">
    <label>Change nickname:</label>
    <input type="text" name="nick" required>
    <button type="submit">OK</button>
    <?php echo $message0; ?>
  </form>-->
  <hr>
  <?php $avatar=$datarr[0]['avatar']; echo '<p>Your avatar: <img style="display:inline; max-width: 96px;" src='.$avatar.'></p>'; ?>
    <form action="" method="POST" enctype="multipart/form-data">
    <label>Change avatar:</label>
    <input type="file" name="avatar" accept="image/*" required>
    <button type="submit">OK</button>
    <?php echo $message1; ?>
  </form>
  <hr>
  <form action="" method="POST" enctype="multipart/form-data">
    <label>Change password:</label>
    <input type="text" name="password" required>
    <button type="submit">OK</button>
    <?php echo $message2; ?>
  </form>
  <hr>

  <a class="bot" href="index.php">BACK</a>
</div>

<?php   // CODICE DI CONTROLLO SESSIONE, ELIMINARE UNA VOLTA FINITO
foreach ($_SESSION as $i) {
  echo '<p>'.$i.'</p>';
}
?>

</body>
</html>
