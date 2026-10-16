<?php
session_start();
$_SESSION['curboard'] = $_GET['curboard'];
$_SESSION['curthread'] = null;

try {
  $pdo = new PDO("sqlite:database.db");
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("ERRORE! NON E' STATO POSSIBILE CONNETTERSI AL DATABASE." . $e->getMessage());
}

// THREAD LIST QUERY
$threadquer = $pdo->prepare("SELECT * FROM threads WHERE boardname = :boardname ORDER BY rowid DESC");
$threadquer->execute([':boardname' => $_SESSION['curboard']]);
$threadarr = $threadquer->fetchAll(PDO::FETCH_ASSOC);

// THREAD CREATION QUERY
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $threadtitle = isset($_POST['threadtitle']) ? trim($_POST['threadtitle']) : '';
  // CHECK IF ALREADY PRESENT
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM threads WHERE boardname = :boardname AND threadtitle = :threadtitle");
  $stmt->execute([':boardname' => $_SESSION['curboard'], ':threadtitle' => $threadtitle]);
  if ($stmt->fetchColumn() > 0) {
    $messaggio = "<p style='color: red;'>THREAD ALREADY PRESENT; CHOOSE ANOTHER TITLE";
  } else {   // CREATE THREAD
    $stmt = $pdo->prepare("INSERT INTO threads (boardname, threadtitle, date) VALUES (:boardname, :threadtitle, :date)");
    $stmt->bindParam(':boardname', $_SESSION['curboard'], PDO::PARAM_STR);
    $stmt->bindParam(':threadtitle', $threadtitle, PDO::PARAM_STR);
    $stmt->bindParam(':date', date('Y-m-d H:i:s'), PDO::PARAM_STR);
    if ($stmt->execute()) {
      $messaggio = "<p class='mess' style='color: rgba(0,255,255,1);'>NEW THREAD CREATED!</p>";
    } else {
      $messaggio = "<p class='mess' style='color: red;'>ERROR: SOMETHING WENT WRONG</p>";
    }
  }
  // RICARICA PAGINA CORRETTAMENTE
  header("Location: " . $_SERVER['REQUEST_URI']);
  exit;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<?php echo '<title>Snowbox - '.$_SESSION['curboard'].'</title>'; ?>
<link rel="stylesheet" href="snowstyle.css">
</head>
<body>

<div class="sez">
  <?php echo '<h1>'.$_SESSION['curboard'].'</h1>'; ?>
  <?php
    foreach ($threadarr as $i) {
      echo '<a class="butt" href="threadt.php?curthread='.$i["threadtitle"].'"><strong>'.$i["threadtitle"].'</strong><br>'.$i["date"].'</a>';
    }
  ?>
  <form action="" method="POST" enctype="multipart/form-data">
    <h2>Make a new thread</h2>
    <div>
      <label>Thread title:</label>
      <input type="text" name="threadtitle" required>
    </div>
    <button type="submit">SUBMIT</button>
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
