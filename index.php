<?php
session_start();

// Password hardcoded (per demo — sostituisci con sistema reale!)
$admin_password = 'miasuperpassword'; // 👈 CAMBIA QUESTA!

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
        if ($_POST['password'] === $admin_password) {
            $_SESSION['logged_in'] = true;
            header('Location: index.php');
            exit;
        } else {
            $error = "Password errata!";
        }
    }

    // Mostra form login
    ?>
    <!DOCTYPE html>
    <html lang="it">
    <head>
        <meta charset="UTF-8">
        <title>Login Amministratore</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
            .login-box { max-width: 400px; margin: 100px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
            input[type="password"] { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; }
            button { width: 100%; padding: 12px; background: #2c3e50; color: white; border: none; border-radius: 5px; cursor: pointer; }
            button:hover { background: #1a252f; }
            .error { color: red; text-align: center; margin-bottom: 15px; }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h2>🔒 Accesso Amministratore</h2>
            <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
            <form method="POST">
                <input type="password" name="password" placeholder="Inserisci password" required>
                <button type="submit">Accedi</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Se loggato, mostra dashboard
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Pannello Amministratore - Segnala il tuo Comune</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        header {
            background: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .nav {
            background: #34495e;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .nav a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
        }
        .nav a:hover {
            background: #2c3e50;
        }
        .logout {
            background: #e74c3c;
        }
        .logout:hover {
            background: #c0392b;
        }
        .stats {
            display: flex;
            gap: 20px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        .stat-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            min-width: 200px;
            text-align: center;
        }
        .stat-box h3 {
            margin: 0 0 10px 0;
            color: #2c3e50;
        }
        .stat-box .number {
            font-size: 2em;
            font-weight: bold;
            color: #3498db;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #3498db;
            color: white;
        }
        tr:hover {
            background: #f1f1f1;
        }
        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
        }
        .status-in-attesa {
            background: #f39c12;
            color: white;
        }
        .status-approvata {
            background: #2ecc71;
            color: white;
        }
        .status-rifiutata {
            background: #e74c3c;
            color: white;
        }
        .actions {
            display: flex;
            gap: 5px;
        }
        .btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
        }
        .btn-approve {
            background: #2ecc71;
            color: white;
        }
        .btn-reject {
            background: #e74c3c;
            color: white;
        }
        .btn-view {
            background: #3498db;
            color: white;
        }
        .media-preview {
            max-width: 100px;
            max-height: 60px;
            object-fit: cover;
            border-radius: 5px;
            display: block;
        }
        .filter-form {
            margin: 20px 0;
            padding: 15px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .filter-form select, .filter-form input {
            padding: 8px;
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .filter-form button {
            padding: 8px 15px;
            background: #2c3e50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .filter-form button:hover {
            background: #1a252f;
        }
        footer {
            text-align: center;
            padding: 20px;
            background: #2c3e50;
            color: white;
            margin-top: 30px;
            border-radius: 0 0 10px 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>📋 Pannello Amministratore</h1>
            <p>Gestisci le segnalazioni dei cittadini</p>
        </header>

        <div class="nav">
            <span>Bentornato, Amministratore!</span>
            <a href="index.php" class="logout">Logout</a>
        </div>

        <!-- Statistiche -->
        <div class="stats">
            <?php
            try {
                $pdo = new PDO("mysql:host=localhost;dbname=comune_segnalazioni;charset=utf8mb4", 'root', '');
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $stmt = $pdo->query("SELECT COUNT(*) as total FROM segnalazioni");
                $total = $stmt->fetch()['total'];

                $stmt = $pdo->query("SELECT COUNT(*) as pending FROM segnalazioni WHERE stato = 'in_attesa'");
                $pending = $stmt->fetch()['pending'];

                $stmt = $pdo->query("SELECT COUNT(*) as approved FROM segnalazioni WHERE stato = 'approvata'");
                $approved = $stmt->fetch()['approved'];

                $stmt = $pdo->query("SELECT COUNT(*) as rejected FROM segnalazioni WHERE stato = 'rifiutata'");
                $rejected = $stmt->fetch()['rejected'];
            } catch (PDOException $e) {
                die("Errore DB: " . $e->getMessage());
            }
            ?>
            <div class="stat-box">
                <h3>Totale Segnalazioni</h3>
                <div class="number"><?php echo $total; ?></div>
            </div>
            <div class="stat-box">
                <h3>In Attesa</h3>
                <div class="number"><?php echo $pending; ?></div>
            </div>
            <div class="stat-box">
                <h3>Approvate</h3>
                <div class="number"><?php echo $approved; ?></div>
            </div>
            <div class="stat-box">
                <h3>Rifiutate</h3>
                <div class="number"><?php echo $rejected; ?></div>
            </div>
        </div>

        <!-- Filtro -->
        <form method="GET" class="filter-form">
            <label>Filtra per stato:</label>
            <select name="stato">
                <option value="">Tutte</option>
                <option value="in_attesa" <?php if ($_GET['stato'] ?? '' == 'in_attesa') echo 'selected'; ?>>In Attesa</option>
                <option value="approvata" <?php if ($_GET['stato'] ?? '' == 'approvata') echo 'selected'; ?>>Approvate</option>
                <option value="rifiutata" <?php if ($_GET['stato'] ?? '' == 'rifiutata') echo 'selected'; ?>>Rifiutate</option>
            </select>
            <input type="text" name="cerca" placeholder="Cerca per titolo o luogo..." value="<?php echo htmlspecialchars($_GET['cerca'] ?? ''); ?>">
            <button type="submit">Filtra</button>
        </form>

        <!-- Tabella segnalazioni -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titolo</th>
                    <th>Luogo</th>
                    <th>Tipo</th>
                    <th>Data</th>
                    <th>Media</th>
                    <th>Stato</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $where = [];
                $params = [];

                if (!empty($_GET['stato'])) {
                    $where[] = "stato = :stato";
                    $params[':stato'] = $_GET['stato'];
                }

                if (!empty($_GET['cerca'])) {
                    $where[] = "(titolo LIKE :cerca OR luogo LIKE :cerca)";
                    $params[':cerca'] = '%' . $_GET['cerca'] . '%';
                }

                $sql = "SELECT * FROM segnalazioni";
                if (!empty($where)) {
                    $sql .= " WHERE " . implode(' AND ', $where);
                }
                $sql .= " ORDER BY data_invio DESC";

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $media_html = '';
                    if ($row['media_path']) {
                        if (strpos($row['media_path'], '.mp4') !== false || strpos($row['media_path'], '.webm') !== false) {
                            $media_html = '<video controls width="80"><source src="' . $row['media_path'] . '" type="video/mp4"></video>';
                        } else {
                            $media_html = '<img src="' . $row['media_path'] . '" class="media-preview" alt="Media">';
                        }
                    }

                    $status_class = 'status-' . $row['stato'];
                    ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['titolo']); ?></td>
                        <td><?php echo htmlspecialchars($row['luogo']); ?></td>
                        <td><?php echo ucfirst($row['tipo']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($row['data_invio'])); ?></td>
                        <td><?php echo $media_html; ?></td>
                        <td><span class="status <?php echo $status_class; ?>"><?php echo ucfirst($row['stato']); ?></span></td>
                        <td class="actions">
                            <form method="POST" action="approve.php" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="action" value="approve" class="btn btn-approve">✅ Approva</button>
                                <button type="submit" name="action" value="reject" class="btn btn-reject">❌ Rifiuta</button>
                            </form>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>

        <footer>
            <p>© 2025 Segnala il tuo Comune — Pannello Amministratore</p>
        </footer>
    </div>
</body>
</html>
