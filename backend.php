<?php
header('Content-Type: application/json; charset=utf-8');

// Configurazione database
$host = 'localhost';
$dbname = 'comune_segnalazioni';
$username = 'root'; // Cambia con il tuo utente
$password = '';     // Cambia con la tua password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die(json_encode(['success' => false, 'message' => 'Errore connessione DB: ' . $e->getMessage()]));
}

// Verifica se il form è stato inviato
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'Metodo non consentito']));
}

// Dati del form
$titolo = trim($_POST['titolo'] ?? '');
$descrizione = trim($_POST['descrizione'] ?? '');
$luogo = trim($_POST['luogo'] ?? '');
$tipo = $_POST['tipo'] ?? '';
$nome_utente = trim($_POST['nome'] ?? '');
$email_utente = trim($_POST['email'] ?? '');

// Validazione base
if (empty($titolo) || empty($descrizione) || empty($luogo) || empty($tipo)) {
    die(json_encode(['success' => false, 'message' => 'Campi obbligatori mancanti']));
}

// Gestione file (foto/video)
$media_path = null;
if (!empty($_FILES['media']['name'])) {
    $file = $_FILES['media'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/webm', 'video/ogg'];

    if (!in_array($file['type'], $allowed_types)) {
        die(json_encode(['success' => false, 'message' => 'Tipo di file non supportato']));
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        die(json_encode(['success' => false, 'message' => 'Errore durante l\'upload del file']));
    }

    // Crea nome univoco per il file
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('segnalazione_') . '.' . $extension;
    $upload_dir = __DIR__ . '/upload/';
    $target_path = $upload_dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target_path)) {
        die(json_encode(['success' => false, 'message' => 'Impossibile salvare il file']));
    }

    $media_path = 'upload/' . $filename;
}

// Inserimento nel database
$stmt = $pdo->prepare("
    INSERT INTO segnalazioni (
        titolo, descrizione, luogo, tipo, media_path, nome_utente, email_utente, 
        latitudine, longitudine, stato
    ) VALUES (
        :titolo, :descrizione, :luogo, :tipo, :media_path, :nome_utente, :email_utente, 
        :latitudine, :longitudine, 'in_attesa'
    )
");

$stmt->execute([
    ':titolo' => $titolo,
    ':descrizione' => $descrizione,
    ':luogo' => $luogo,
    ':tipo' => $tipo,
    ':media_path' => $media_path,
    ':nome_utente' => $nome_utente,
    ':email_utente' => $email_utente,
    ':latitudine' => null, // Puoi aggiungere lat/long se li raccogli dal JS
    ':longitudine' => null
]);

$segnalazione_id = $pdo->lastInsertId();

// Risposta JSON al frontend
echo json_encode([
    'success' => true,
    'message' => 'Segnalazione inviata con successo!',
    'id' => $segnalazione_id,
    'media_url' => $media_path ? $media_path : null
]);
