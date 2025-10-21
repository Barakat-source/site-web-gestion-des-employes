<?php
session_start();

$conn = new mysqli('localhost', 'user', '8C]Cl)r[DA1W3vay', 'test', 3309);
if ($conn->connect_error) {
    die("Échec de la connexion: " . $conn->connect_error);
}

$firstName = $conn->real_escape_string($_POST['first_name']);
$lastName = $conn->real_escape_string($_POST['last_name']);
$email = $conn->real_escape_string($_POST['email']);
$password = $_POST['password'];

if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
    die("Tous les champs sont obligatoires.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Adresse email invalide.");
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (first_name, last_name, email, password) VALUES ('$firstName', '$lastName', '$email', '$hashedPassword')";

if ($conn->query($sql) === TRUE) {
    $_SESSION['message'] = "Utilisateur ajouté avec succès !";
    header("Location: index.php");
    exit;
} else {
    echo "Erreur: " . $conn->error;
}

$conn->close();
?>
