<?php
session_start();
try {
  $pdo = new PDO("sqlite:database.db");
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("DATABASE CONNECTION ERROR!" . $e->getMessage());
}

$message = "";
$link = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nick = isset($_POST['nick']) ? trim($_POST['nick']) : '';
  $password = isset($_POST['password']) ? trim($_POST['password']) : '';
  // MATCH CHECKING QUERY (only nick for now, because of hashed password)
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE nick = :nick");
  if ($stmt->execute([':nick' => $nick])) {
    if ($stmt->fetchColumn() > 0) {
      // SESSION FILLING QUERY (only nick for now, because of hashed password)
      $stmt = $pdo->prepare("SELECT * FROM users WHERE nick = :nick");
      $stmt->execute([':nick' => $nick]);
      $dati = $stmt->fetch(PDO::FETCH_NUM);
      if (password_verify($password, $dati[4])) {   // Now check whether the user-inserted password matches with the hashed one from the database
        $_SESSION["curuser"]=$dati[2];     // Session filling
        $message = "<p class='mess' style='color: rgba(0,255,255,1);'>YOU'RE IN!</p>
        <p class='mess' style='color: rgba(0,255,255,1);'>You're logged in as <strong>".$_SESSION["curuser"]."</strong>.</p>";
        $link = "<a class='bot' href='usareat.php'>ENTER</a>";
      } else {
        $message = "<p class='mess' style='color: red;'>ACCESS DENIED: WRONG PASSWORD!</p>";
      }
    } else {
      $message = "<p class='mess' style='color: red;'>ACCESS DENIED: WRONG USERNAME!</p>";
    }
  } else {
    $message = "<p class='mess' style='color: red;'>ERROR: it wasn't possible to access.</p>";
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
    <?php echo $message; ?>
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
