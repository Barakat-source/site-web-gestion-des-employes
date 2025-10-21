<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


$conn = new mysqli('localhost', 'user', '8C]Cl)r[DA1W3vay', 'test', 3309);
if ($conn->connect_error) {
    die("Échec de la connexion: " . $conn->connect_error);
}

// قائمة القيم المسموح بها لفرز البيانات لتجنب هجمات SQL Injection
$allowedSorts = ['name_asc', 'date_asc', 'date_desc'];

// جلب طريقة الفرز من الرابط، القيمة الافتراضية: فرز حسب الاسم (A-Z)
$sort = isset($_GET['sort']) && in_array($_GET['sort'], $allowedSorts) ? $_GET['sort'] : 'name_asc';

switch ($sort) {
    case 'name_asc':
        $orderBy = "last_name ASC, first_name ASC";
        break;
    case 'date_asc':
        $orderBy = "created_at ASC";
        break;
    case 'date_desc':
        $orderBy = "created_at DESC";
        break;
    default:
        $orderBy = "last_name ASC, first_name ASC";
        break;
}

$sql = "SELECT * FROM users ORDER BY $orderBy";
$result = $conn->query($sql);
if(!$result) {
    die("Erreur lors de la récupération des utilisateurs : " . $conn->error);
}
$totalUsers = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Tableau de bord des utilisateurs</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    * {
      box-sizing: border-box;
      margin: 0; padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    body {
      background: linear-gradient(120deg, #4b6cb7, #182848);
      color: #fff;
      min-height: 100vh;
      padding: 40px 20px;
      transition: background-color 0.5s ease, color 0.5s ease;
    }
    body.dark-mode {
      background: #121212;
      color: #ddd;
    }
    .container {
      max-width: 1200px;
      margin: auto;
      background: #1e1e2f;
      padding: 30px;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
      animation: fadeIn 0.8s ease forwards;
      transition: background-color 0.5s ease, color 0.5s ease;
      position: relative;
    }
    body.dark-mode .container {
      background: #222;
      box-shadow: 0 10px 30px rgba(255,255,255,0.1);
    }
    @keyframes fadeIn {
      from {opacity: 0; transform: translateY(20px);}
      to {opacity: 1; transform: translateY(0);}
    }
    h1 {
      text-align: center;
      margin-bottom: 25px;
      font-size: 2.6rem;
      color: #61dafb;
    }
    body.dark-mode h1 {
      color: #90caf9;
    }
    .summary {
      text-align: center;
      font-size: 1.2rem;
      margin-bottom: 20px;
    }
    .actions {
      display: flex;
      justify-content: space-between;
      flex-wrap: wrap;
      margin-bottom: 20px;
      gap: 10px;
      align-items: center;
    }
    .actions input, .actions a, .actions button, .actions select {
      padding: 12px 20px;
      border-radius: 30px;
      border: none;
      font-size: 1rem;
      outline: none;
      box-shadow: 0 0 10px rgba(0, 198, 255, 0.7);
      transition: box-shadow 0.3s ease;
      color: #fff;
      background: linear-gradient(to right, #00c6ff, #0072ff);
      cursor: pointer;
      user-select: none;
    }
    .actions input {
      flex: 1;
      max-width: 350px;
      background: #2a2a3f;
      box-shadow: 0 0 12px rgba(101, 43, 255, 0.8);
    }
    .actions input:focus {
      box-shadow: 0 0 18px #00e5ff;
      background: #394263;
      color: #e0e0e0;
    }
    .actions a:hover, .actions button:hover, .actions select:hover {
      box-shadow: 0 0 20px #00aaff;
      transform: scale(1.05);
    }
    select.sort-select {
      max-width: 250px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 8px 16px rgba(0,0,0,0.3);
      transition: background-color 0.5s ease, color 0.5s ease;
    }
    body.dark-mode table {
      background: #333;
      color: #ddd;
      box-shadow: 0 8px 20px rgba(255,255,255,0.1);
    }
    thead {
      background: #374785;
    }
    body.dark-mode thead {
      background: #5c6bc0;
    }
    thead th {
      padding: 15px;
      text-align: left;
      font-weight: 600;
      font-size: 1.1rem;
      letter-spacing: 0.05em;
    }
    tbody tr:nth-child(even) {
      background: #2e2e40;
      transition: background-color 0.3s ease;
    }
    tbody tr:nth-child(odd) {
      background: #232332;
      transition: background-color 0.3s ease;
    }
    tbody tr:hover {
      background: #44445a;
      cursor: pointer;
      transform: scale(1.01);
      box-shadow: 0 4px 20px rgba(0,0,0,0.3);
      transition: all 0.3s ease;
    }
    body.dark-mode tbody tr:hover {
      background: #5a5e9e;
      box-shadow: 0 4px 20px rgba(101, 43, 255, 0.8);
    }
    td {
      padding: 15px;
      font-size: 1rem;
      color: inherit;
      user-select: text;
      transition: color 0.3s ease;
    }
    .btn-action {
      margin-right: 10px;
      border: none;
      border-radius: 20px;
      padding: 10px 16px;
      cursor: pointer;
      font-weight: 600;
      font-size: 0.9rem;
      user-select: none;
      position: relative;
      overflow: hidden;
      transition: all 0.3s ease;
      color: #fff;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    .edit-btn {
      background: linear-gradient(45deg, #4caf50, #388e3c);
      box-shadow: 0 6px 15px rgba(76,175,80,0.6);
    }
    .edit-btn:hover {
      background: linear-gradient(45deg, #388e3c, #2e7d32);
      box-shadow: 0 8px 20px rgba(46,125,50,0.8);
      transform: scale(1.1);
    }
    .delete-btn {
      background: linear-gradient(45deg, #f44336, #b71c1c);
      box-shadow: 0 6px 15px rgba(244,67,54,0.6);
    }
    .delete-btn:hover {
      background: linear-gradient(45deg, #b71c1c, #7f0000);
      box-shadow: 0 8px 20px rgba(183,28,28,0.8);
      transform: scale(1.1) rotate(-3deg);
    }
    .btn-action::before {
      content: "";
      position: absolute;
      top: 0;
      left: -75%;
      width: 50%;
      height: 100%;
      background: rgba(255,255,255,0.2);
      transform: skewX(-25deg);
      transition: left 0.7s ease;
      pointer-events: none;
    }
    .btn-action:hover::before {
      left: 125%;
    }
    #noResults {
      display: none;
      text-align: center;
      font-weight: bold;
      margin-top: 20px;
      color: #ff4d4d;
      font-size: 1.2rem;
    }
    body.dark-mode #noResults {
      color: #ff8080;
    }
    #backToTop {
      position: fixed;
      bottom: 40px;
      right: 40px;
      background: #374785;
      color: white;
      border-radius: 50%;
      padding: 12px 16px;
      border: none;
      font-size: 22px;
      display: none;
      cursor: pointer;
      box-shadow: 0 4px 15px rgba(101, 43, 255, 0.4);
      user-select: none;
      transition: background-color 0.3s ease;
      z-index: 1000;
    }
    #backToTop:hover {
      background: #5a6ddf;
    }
    select {
      background-color: #fff;
      color: #000;
      padding: 8px 12px;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 1rem;
      cursor: pointer;
    }
    select option {
      background-color: #fff;
      color: #000;
    }
    body.dark-mode select {
      background-color: #222;
      color: #eee;
      border: 1px solid #555;
    }
    body.dark-mode select option {
      background-color: #222;
      color: #eee;
    }

    /* رسالة التنبيه */
    .alert-message {
      position: fixed;
      top: 20px;
      left: 50%;
      transform: translateX(-50%);
      background-color: #00c853;
      color: white;
      padding: 15px 30px;
      border-radius: 8px;
      font-weight: bold;
      box-shadow: 0 4px 15px rgba(0,200,83,0.7);
      opacity: 0;
      pointer-events: none;
      animation: slideDownFadeIn 4s ease forwards;
      z-index: 1000;
    }
    .alert-message.error {
      background-color: #f44336;
      box-shadow: 0 4px 15px rgba(244, 67, 54, 0.7);
    }
    .alert-message.info {
      background-color: #2196f3;
      box-shadow: 0 4px 15px rgba(33, 150, 243, 0.7);
    }

    @keyframes slideDownFadeIn {
      0% {
        opacity: 0;
        transform: translate(-50%, -50px);
        pointer-events: none;
      }
      10%, 90% {
        opacity: 1;
        transform: translate(-50%, 0);
        pointer-events: auto;
      }
      100% {
        opacity: 0;
        transform: translate(-50%, -50px);
        pointer-events: none;
      }
    }
    .actions input, .actions a, .actions button, .actions select {
  padding: 12px 20px;
  border-radius: 30px;
  border: none;
  font-size: 0.85rem; /* <== الحجم الموحد */
  outline: none;
  box-shadow: 0 0 10px rgba(0, 198, 255, 0.7);
  transition: box-shadow 0.3s ease;
  color: #fff;
  background: linear-gradient(to right, #00c6ff, #0072ff);
  cursor: pointer;
  user-select: none;
}
    .actions a,
    .actions button,
    .actions select,
    .btn-action {
  font-family: 'Segoe UI', 'Roboto', sans-serif;
  font-size: 0.9rem;
  font-weight: 600;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  text-shadow: 1px 1px 2px rgba(0,0,0,0.15);
  color: #fff;
}

  </style>
</head>
<body>
<div class="container">
  <h1><i class="fas fa-users"></i> Utilisateurs</h1>
  <div class="summary">Nombre total : <strong><?= $totalUsers ?></strong></div>

  <div class="actions" style="align-items: center; gap: 15px;">
    <form method="get" id="sortForm" style="margin:0;">
      <select name="sort" class="sort-select" onchange="document.getElementById('sortForm').submit()">
        <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Trier par nom (A-Z)</option>
        <option value="date_desc" <?= $sort === 'date_desc' ? 'selected' : '' ?>>Trier par date d'ajout (plus récent)</option>
        <option value="date_asc" <?= $sort === 'date_asc' ? 'selected' : '' ?>>Trier par date d'ajout (plus ancien)</option>
      </select>
    </form>

    <input type="text" id="searchInput" placeholder="Recherche par nom ou email..." autocomplete="off" />
    <a href="index.html" class="btn"><i class="fas fa-user-plus"></i> Ajouter</a>
    <button id="toggleDarkMode" class="btn" title="Basculer le mode sombre"><i class="fas fa-adjust"></i></button>
  </div>

  <div id="noResults">Aucun utilisateur trouvé.</div>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Prénom</th>
        <th>Nom</th>
        <th>Email</th>
        <th>Date d'ajout</th>
        <th>Actions</th>
      </tr>
    </thead>
   <tbody id="userTableBody">
  <?php if ($totalUsers === 0): ?>
    <tr>
      <td colspan="6" style="text-align:center; padding: 20px; font-weight: bold; color: #ff8080;">
        Aucun utilisateur dans la liste.
      </td>
    </tr>
  <?php else: ?>
    <?php while($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['id']) ?></td>
        <td><?= htmlspecialchars($row['first_name']) ?></td>
        <td><?= htmlspecialchars($row['last_name']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= htmlspecialchars($row['created_at']) ?></td>
        <td>
          <button onclick="window.location.href='update.php?id=<?= urlencode($row['id']) ?>&csrf=<?= $_SESSION['csrf_token'] ?>'" class="btn-action edit-btn" title="Modifier">
           <i class="fas fa-pen"></i> Modifier
          </button>
          <button onclick="confirmDeletion(<?= htmlspecialchars(json_encode($row['id'])) ?>, <?= htmlspecialchars(json_encode($row['first_name'] . ' ' . $row['last_name'])) ?>)" class="btn-action delete-btn" title="Supprimer">
            <i class="fas fa-trash"></i> Supprimer
          </button>
        </td>
      </tr>
    <?php endwhile; ?>
  <?php endif; ?>
</tbody>

  </table>
</div>

<?php if (isset($_SESSION['message'])): ?>
  <div class="alert-message info" id="alertMessage">
    <?= htmlspecialchars($_SESSION['message']) ?>
  </div>
  <?php unset($_SESSION['message']); ?>
<?php endif; ?>

<button id="backToTop" title="Retour en haut"><i class="fas fa-arrow-up"></i></button>

<script>
  const searchInput = document.getElementById('searchInput');

  searchInput.addEventListener('input', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#userTableBody tr');
    let visibleCount = 0;
    rows.forEach(row => {
      let nom = row.cells[2].textContent.toLowerCase();
      let email = row.cells[3].textContent.toLowerCase();
      if (nom.includes(filter) || email.includes(filter)) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });
    document.getElementById('noResults').style.display = visibleCount === 0 ? 'block' : 'none';
  });

  function confirmDeletion(id, name) {
  if (confirm("Êtes-vous sûr de vouloir supprimer l'utilisateur " + name + " (ID: " + id + ") ?")) {
    window.location.href = "delete.php?id=" + encodeURIComponent(id) + "&csrf=<?= $_SESSION['csrf_token'] ?>";
  }
 }


  const backToTopBtn = document.getElementById('backToTop');
  window.addEventListener('scroll', () => {
    backToTopBtn.style.display = window.scrollY > 200 ? 'block' : 'none';
  });
  backToTopBtn.onclick = () => window.scrollTo({ top: 0, behavior: 'smooth' });

  const toggleDarkModeBtn = document.getElementById('toggleDarkMode');
  toggleDarkModeBtn.addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    toggleDarkModeBtn.innerHTML = document.body.classList.contains('dark-mode')
      ? '<i class="fas fa-sun"></i>'
      : '<i class="fas fa-adjust"></i>';
  });

  // تنشيط أنيميشن رسالة التنبيه
  window.addEventListener('DOMContentLoaded', () => {
    const alert = document.getElementById('alertMessage');
    if (alert) {
      alert.style.animation = 'slideDownFadeIn 4s ease forwards';
      setTimeout(() => {
        alert.style.display = 'none';
      }, 4000);
    }
  });
</script>
</body>
</html>

<?php
$conn->close();
?> 