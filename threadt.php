<?php
session_start();
$_SESSION['curboard'] = $_SESSION['curboard'];
$_SESSION['curthread'] = $_GET['curthread'];

try {
  $pdo = new PDO("sqlite:database.db");
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("ERRORE! NON E' STATO POSSIBILE CONNETTERSI AL DATABASE." . $e->getMessage());
}

// POST LIST QUERY
$postquer = $pdo->prepare("SELECT * FROM posts WHERE boardname = :boardname AND threadtitle = :threadtitle ORDER BY rowid ASC");
$postquer->execute([':boardname' => $_SESSION['curboard'], ':threadtitle' => $_SESSION['curthread']]);
$postarr = $postquer->fetchAll(PDO::FETCH_ASSOC);

// POST CREATION QUERY
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nick = isset($_POST['nick']) ? trim($_POST['nick']) : '';
  $picture = isset($_POST['picture']) ? trim($_POST['picture']) : '';
  $message = isset($_POST['message']) ? trim($_POST['message']) : '';
  $stmt = $pdo->prepare("INSERT INTO posts (boardname, threadtitle, nick, message, picture) VALUES (:boardname, :threadtitle, :nick, :message, :picture)");
  $stmt->bindParam(':boardname', $_SESSION['curboard'], PDO::PARAM_STR);
  $stmt->bindParam(':threadtitle', $_SESSION['curthread'], PDO::PARAM_STR);
  $stmt->bindParam(':nick', $_SESSION['curuser'], PDO::PARAM_STR);
  $stmt->bindParam(':message', $message, PDO::PARAM_STR);
  $stmt->bindParam(':picture', $picture, PDO::PARAM_STR);
  if ($stmt->execute()) {
    $messaggio = "<p class='mess' style='color: rgba(0,255,255,1);'>NEW POST SUBMITTED!</p>";
  } else {
    $messaggio = "<p class='mess' style='color: red;'>ERROR: SOMETHING WENT WRONG</p>";
  }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<?php echo '<title>Snowbox - '.$_SESSION['curthread'].'</title>'; ?>
<link rel="stylesheet" href="snowstyle.css">
</head>
<body>

<div class="sez">
  <?php echo '<h1>'.$_SESSION['curthread'].'</h1>'; ?>
  <?php
    foreach ($postarr as $i) {
      if (isset($i["nick"])) {
        $author = $i["nick"];
        $avauthor = "/pics/default.png";   // CAMBIARE CON AVATAR DELL'UTENTE
      } else {
        $author = "Snowflake";
        $avauthor = "/pics/default.png";
      }
      echo '<div class="sez">
              <h2>'.$author.'</h2>
              <p>'.$i["picture"].'</p>
              <p>'.$i["message"].'</p>
            </div>';
    }
  ?>
  <form action="" method="POST" enctype="multipart/form-data">
    <h2>Make a new post</h2>
    <div>
      <label>Load a picture?</label>
      <input type="text" name="picture">
    </div>
    <div>
      <label>Your message:</label>
      <input type="text" name="message" required>
    </div>
    <button type="submit">SUBMIT</button>
    <?php echo $messaggio; ?>
  </form>
  <?php echo '<a class="bot" href="boardt.php?curboard='.$_SESSION['curboard'].'">BACK</a>' ?>
</div>

<?php   // CODICE DI CONTROLLO SESSIONE, ELIMINARE UNA VOLTA FINITO
foreach ($_SESSION as $i) {
  echo '<p>'.$i.'</p>';
}
?>

</body>
</html>
