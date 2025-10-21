<?php
session_start();

// إنشاء رمز CSRF إذا لم يكن موجودًا
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$conn = new mysqli('localhost', 'user', '8C]Cl)r[DA1W3vay', 'test', 3309);
if ($conn->connect_error) {
    die("Échec de la connexion: " . $conn->connect_error);
}

if (!isset($_GET['id'])) {
    die("Identifiant utilisateur manquant.");
}

$id = intval($_GET['id']);

// التحقق من صحة CSRF عند الإرسال
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token invalide.");
    }

    $firstName = trim($conn->real_escape_string($_POST['first_name']));
    $lastName = trim($conn->real_escape_string($_POST['last_name']));
    $email = trim($conn->real_escape_string($_POST['email']));
    $password = $_POST['password'];

    if (empty($firstName) || empty($lastName) || empty($email)) {
        die("Tous les champs sauf le mot de passe sont obligatoires.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Adresse email invalide.");
    }

    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET first_name=?, last_name=?, email=?, password=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $firstName, $lastName, $email, $hashedPassword, $id);
    } else {
        $sql = "UPDATE users SET first_name=?, last_name=?, email=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $firstName, $lastName, $email, $id);
    }

    if ($stmt->execute()) {
        $_SESSION['message'] = "Utilisateur modifié avec succès !";
        header("Location: index.php");
        exit;
    } else {
        echo "Erreur lors de la mise à jour : " . $stmt->error;
    }
} else {
    $sql = "SELECT first_name, last_name, email FROM users WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        die("Utilisateur non trouvé.");
    }

    $user = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Modifier utilisateur</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <div class="form-container">
    <h2>Modifier l'utilisateur</h2>
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>" />
      
      <label for="first_name">Prénom</label>
      <input type="text" name="first_name" id="first_name" required value="<?= htmlspecialchars($user['first_name']) ?>" />

      <label for="last_name">Nom</label>
      <input type="text" name="last_name" id="last_name" required value="<?= htmlspecialchars($user['last_name']) ?>" />

      <label for="email">Email</label>
      <input type="email" name="email" id="email" required value="<?= htmlspecialchars($user['email']) ?>" />

      <label for="password">Mot de passe (laissez vide pour ne pas changer)</label>
      <input type="password" name="password" id="password" placeholder="Nouveau mot de passe" />

      <button type="submit">💾 Enregistrer les modifications</button>
    </form>
    <br>
    <a href="index.php" class="btn">🔙 Retour à la liste</a>
  </div>
</body>
</html>
