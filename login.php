<?php
session_start();
try {
  $pdo = new PDO("sqlite:database.db");
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("DATABASE CONNECTION ERROR!" . $e->getMessage());
}

$messaggio = "";
$link = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nick = isset($_POST['nick']) ? trim($_POST['nick']) : '';
  $password = isset($_POST['password']) ? trim($_POST['password']) : '';
  // MATCH CHECKING QUERY
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE nick = :nick AND password = :password");
  if ($stmt->execute([':nick' => $nick, ':password' => $password])) {
    if ($stmt->fetchColumn() > 0) {
      // SESSION FILLING QUERY
      $stmt = $pdo->prepare("SELECT * FROM users WHERE nick = :nick AND password = :password");
      $stmt->execute([':nick' => $nick, ':password' => $password]);
      $dati = $stmt->fetch(PDO::FETCH_NUM);
      $_SESSION["curuser"]=$dati[2];     // Riempimento sessione
      $messaggio = "<p class='mess' style='color: rgba(0,255,255,1);'>YOU'RE IN!</p>
      <p class='mess' style='color: rgba(0,255,255,1);'>You're logged in as <strong>".$_SESSION["curuser"]."</strong>.</p>";
      $link = "<a class='bot' href='usareat.php'>ENTER</a>";
    } else {
      $messaggio = "<p class='mess' style='color: red;'>ACCESS DENIED: WRONG CREDENTIALS!</p>";
    }
  } else {
    $messaggio = "<p class='mess' style='color: red;'>ERRORE: non è stato possibile effettuare l'accesso.</p>";
  }
}

# Save cookie
if (isset($_POST['submit'])) {
  setcookie("curuser", $_SESSION['curuser'], time()+(86400*30),"/");
}
?>



<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Snowbox - Login</title>
<link rel="stylesheet" href="snowstyle.css">
</head>
<body>

<div class="sez">
  <h1>LOGIN</h1>
  <p>Insert your nickname and password:</p>

  <form action="" method="POST">
    <div>
      <label>Nickname:</label>
      <input type="text" name="nick" required>
    </div>
    <div>
      <label>Password:</label>
      <input type="password" name="password" required>
    </div>
    <button type="submit" name="submit">LOGIN</button>
    <?php echo $messaggio; ?>
    <?php echo $link; ?>
  </form>

  <a class="bot" href="index.php">BACK</a>
</div>

<?php   // SESSION CONTROL CODE, REMOVE ONCE DONE
foreach ($_SESSION as $i) {
  echo '<p>'.$i.'</p>';
}
?>

</body>
</html>
