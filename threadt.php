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

// USERS QUERY (FOR AVATARS)
$avaquer = $pdo->prepare("SELECT * FROM users");
$avaquer->execute();
$avarr = $avaquer->fetchAll(PDO::FETCH_ASSOC);

// POST LIST QUERY
$postquer = $pdo->prepare("SELECT * FROM posts WHERE boardname = :boardname AND threadtitle = :threadtitle ORDER BY rowid ASC");
$postquer->execute([':boardname' => $_SESSION['curboard'], ':threadtitle' => $_SESSION['curthread']]);
$postarr = $postquer->fetchAll(PDO::FETCH_ASSOC);

// POST CREATION QUERY
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nick = isset($_POST['nick']) ? trim($_POST['nick']) : '';
  $picture = '/pics/'.basename($_FILES['picture']['tmp_name']);   // nome del file caricato, modificato in modo da puntare alla cartella "pics"
  $message = isset($_POST['message']) ? trim($_POST['message']) : '';
  $stmt = $pdo->prepare("INSERT INTO posts (boardname, threadtitle, nick, message, picture, date) VALUES (:boardname, :threadtitle, :nick, :message, :picture, :date)");
  $stmt->bindParam(':boardname', $_SESSION['curboard'], PDO::PARAM_STR);
  $stmt->bindParam(':threadtitle', $_SESSION['curthread'], PDO::PARAM_STR);
  $stmt->bindParam(':nick', $_SESSION['curuser'], PDO::PARAM_STR);
  $stmt->bindParam(':message', $message, PDO::PARAM_STR);
  $stmt->bindParam(':picture', $picture, PDO::PARAM_STR);
  $stmt->bindParam(':date', date('Y-m-d H:i:s'), PDO::PARAM_STR);
  if ($stmt->execute()) {
    $messaggio = "<p class='mess' style='color: rgba(0,255,255,1);'>NEW POST SUBMITTED!</p>";
  } else {
    $messaggio = "<p class='mess' style='color: red;'>ERROR: SOMETHING WENT WRONG</p>";
  }
  move_uploaded_file($_FILES['picture']['tmp_name'], __DIR__.'/pics/'.basename($_FILES['picture']['tmp_name']));
  // RICARICA PAGINA CORRETTAMENTE
  header("Location: " . $_SERVER['REQUEST_URI']);
  exit;
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
      if (isset($i["nick"])) {   // se l'utente ha un nickname
        $author = $i["nick"];   // mostra il nickname
        $avauthor = null;
        foreach ($avarr as $j) {   // mostra l'avatar associato
          if ($j["nick"] === $author) {
            $avauthor = $j["avatar"];
            break;
          }
        }
      } else {   // altrimenti mostra nick/avatar default
        $author = "Snowflake";
        $avauthor = "/pics/default.png";
      }
      if ($i["picture"] === "/pics/") {   // imposta immagine vuota "nopic.png" se non è stata postata alcuna immagine
        $postpic = "/pics/nopic.png";
      } else {
        $postpic = $i["picture"];
      }
      $processedmessage = str_replace("[quote start:]", "<br><span style='color: rgba(255,255,255,1);'>quote: </span><em><span style='color: rgba(0,128,255,1);'>", $i["message"]);   // in the message, replace every instance of [AQ:] with <span>
      $processedmessage = str_replace("[:quote end]", "</span></em><br>", $processedmessage);   // in the message, replace every instance of [:QZ] with </span>
      $argname="'txtarid'";   #(defined here in order to avoid problems with ''s and ""s)
      echo '<div class="sez">
              <h2><img style="display:inline; max-width: 96px;" src='.$avauthor.'>'.$author.'</h2>
              <hr>
              <img class="postedpicture" src='.$postpic.'>
              <p>'.$processedmessage.'</p>
              <button onclick="quote('.$argname.')" style="font-size: 12px;">Quote selected</button>
              <hr>
              <p>'.$i["date"].'</p>
            </div>';
    }
  ?>
  <form action="" method="POST" enctype="multipart/form-data">
    <h2>Make a new post</h2>
    <div>
      <label>Load a picture?</label>
      <input type="file" name="picture" accept="image/*">
    </div>
    <div>
      <label>Your message:</label>
      <textarea id="txtarid" name="message" rows="10" cols="100" required></textarea>
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

<script>
function quote(target) {
  const sel = window.getSelection().toString();   // sel = selected string
  const pastarea = document.getElementById(target);   // pastarea = element with target id
  const curcont = pastarea.value;   // curcont = current content of the element
  pastarea.value = curcont.substring(0, pastarea.selectionStart) + "[quote start:]" + sel + "[:quote end]" + curcont.substring(pastarea.selectionEnd);
}
</script>

</body>
</html>
