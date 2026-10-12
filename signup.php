<?php
session_start();
try {
  $pdo = new PDO("sqlite:database.db");
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("ERRORE! NON E' STATO POSSIBILE CONNETTERSI AL DATABASE." . $e->getMessage());
}

$messaggio = "";     // Parti di HTML da riempire e visualizzare successivamente
$link = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {   // Nota: i files vengono caricati in $_FILES, non in $_POST
  $nick = isset($_POST['nick']) ? trim($_POST['nick']) : '';
  $avatar = '/pics/'.basename($_FILES['avatar']['tmp_name']);   // nome del file caricato, modificato in modo da puntare alla cartella "pics"
  $password = isset($_POST['password']) ? trim($_POST['password']) : '';
  // CONTROLLO EVENTUALI DOPPIONI
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE nick = :nick OR avatar = :avatar OR password = :password");     // QUERY (PER L'INSERIMENTO DEI DATI IN MODO SICURO: invece dei valori usiamo dei segnaposto (":"))
  $stmt->execute([':nick' => $nick, ':avatar' => $avatar, ':password' => $password]);
  if ($stmt->fetchColumn() > 0) {
    $messaggio = "<p style='color: red;'>USER ALREADY PRESENT: CHOOSE ANOTHER NICKNAME AND/OR AVATAR AND/OR PASSWORD.";
  } else {
    $stmt = $pdo->prepare("INSERT INTO users (nick, avatar, password) VALUES (:nick, :avatar, :password)");     // QUERY (PER L'INSERIMENTO DEI DATI IN MODO SICURO: invece dei valori usiamo dei segnaposto (":"))
    $stmt->bindParam(':nick', $nick, PDO::PARAM_STR);     // qui si definiscono i segnaposto
    $stmt->bindParam(':avatar', $avatar, PDO::PARAM_STR);
    $stmt->bindParam(':password', $password, PDO::PARAM_STR);
    if ($stmt->execute()) {     // Definisce le parti di HTML da visualizzare a seconda dell'esito
      $messaggio = "<p class='mess' style='color: rgba(0,255,255,1);'>NEW USER CREATED: YOU'VE BEEN SIGNED UP!</p>";
      $link = "<a class='bot' href='login.php'>LOGIN</a>";
    } else {
      $messaggio = "<p class='mess' style='color: red;'>ERROR: SOMETHING WENT WRONG</p>";
    }
    move_uploaded_file($_FILES['avatar']['tmp_name'], __DIR__.'/pics/'.basename($_FILES['avatar']['tmp_name']));
  }
}
?>



<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Snowbox - Signup</title>
<link rel="stylesheet" href="snowstyle.css">
</head>
<body>

<div class="sez">
  <h1>SIGNUP</h1>
  <p>Choose your data:</p>

  <form action="" method="POST" enctype="multipart/form-data">
    <div>
      <label>Choose a nickname:</label>
      <input type="text" name="nick" required>
    </div>
    <div>
      <label>Choose an avatar:</label>
      <input type="file" name="avatar" accept="image/*" required>
    </div>
    <div>
      <label>Choose a password:</label>
      <input type="password" name="password" required>
    </div>
    <button type="submit">OK</button>
    <?php echo $messaggio; ?>
    <?php echo $link; ?>
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
