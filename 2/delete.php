<?php
session_start();

$conn = new mysqli('localhost', 'user', '8C]Cl)r[DA1W3vay', 'test', 3309);
if ($conn->connect_error) {
    die("Échec de la connexion: " . $conn->connect_error);
}

// التحقق من وجود كل من المعرف و رمز CSRF ومطابقته مع الجلسة
if (isset($_GET['id'], $_GET['csrf']) && $_GET['csrf'] === $_SESSION['csrf_token']) {
    $id = intval($_GET['id']);

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Utilisateur supprimé avec succès !";
        $stmt->close();
        $conn->close();
        header("Location: index.php");
        exit;
    } else {
        echo "Erreur lors de la suppression : " . $stmt->error;
    }
} else {
    // حالة فشل التحقق من CSRF أو نقص المعرف
    die("CSRF token invalide ou ID manquant.");
}
?>
