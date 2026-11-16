<?php
session_start();
try {
  $pdo = new PDO("sqlite:database.db");
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("DATABASE CONNECTION ERROR!" . $e->getMessage());
}

$message = "";     // HTML pieces to fill afterwards
$link = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {   // Note: files are loaded into $_FILES, not $_POST
  $nick = isset($_POST['nick']) ? trim($_POST['nick']) : '';
  $avatar = '/pics/'.basename($_FILES['avatar']['tmp_name']);   // loaded file name, edited in order to point at the "pics" directory
  $password = isset($_POST['password']) ? trim($_POST['password']) : '';
  // DOUBLE-COPIES CHECK
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE nick = :nick OR avatar = :avatar");     // SAFE INSERTION QUERY instead of values let's use replacements (":"))
  $stmt->execute([':nick' => $nick, ':avatar' => $avatar]);
  if ($stmt->fetchColumn() > 0) {
    $message = "<p style='color: red;'>USER ALREADY PRESENT: CHOOSE ANOTHER NICKNAME AND/OR AVATAR.";
  } else {
    $stmt = $pdo->prepare("INSERT INTO users (nick, avatar, password) VALUES (:nick, :avatar, :password)");     // SAFE INSERTION QUERY instead of values let's use replacements (":"))
    $stmt->bindParam(':nick', $nick, PDO::PARAM_STR);     // qui si definiscono i segnaposto
    $stmt->bindParam(':avatar', $avatar, PDO::PARAM_STR);
    $stmt->bindParam(':password', password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR);      // SAFE PASSWORD INSERTION: instead of simply "$password", let's use "password_hash($password, PASSWORD_DEFAULT)"; the login form must also be changed
    if ($stmt->execute()) {     // Defines HTML parts to visualize depending on the query outcome
      $message = "<p class='mess' style='color: rgba(0,255,255,1);'>NEW USER CREATED: YOU'VE BEEN SIGNED UP!</p>";
      $link = "<a class='bot' href='login.php'>LOGIN</a>";
    } else {
      $message = "<p class='mess' style='color: red;'>ERROR: SOMETHING WENT WRONG</p>";
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
    <?php echo $message; ?>
    <?php echo $link; ?>
  </form>

  <a class="bot" href="index.php">BACK</a>
</div>

<?php   // SESSION CONTROL CODE, DELETE ONCE FINISHED
foreach ($_SESSION as $i) {
  echo '<p>'.$i.'</p>';
}
?>

</body>
</html>
