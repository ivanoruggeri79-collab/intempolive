<?php
header('Content-Type: application/json; charset=utf-8');

// Configurazione database — MODIFICA QUESTI VALORI CON I TUOI DATI ALTERVISTA!
$host = 'localhost';
$dbname = 'comune_segnalazioni';
$username = 'Paesio_user';
$password = 'Moruccetto0007';

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
$tipo_segnalazione = $_POST['tipo_segnalazione'] ?? 'problema_fisico';
$nome_impiegato = trim($_POST['nome_impiegato'] ?? '');
$ente_pubblico = trim($_POST['ente_pubblico'] ?? '');
$feedback = $_POST['feedback'] ?? '';
$commento = trim($_POST['commento'] ?? '');
$utente_id = intval($_POST['utente_id'] ?? 0);
$nome_utente = trim($_POST['nome'] ?? '');
$email_utente = trim($_POST['email'] ?? '');

// Se c'è un utente loggato, verifica che esista
if ($utente_id > 0) {
    $stmt = $pdo->prepare("SELECT nome_completo, email FROM utenti WHERE id = :id");
    $stmt->execute([':id' => $utente_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $nome_utente = $user['nome_completo'];
        $email_utente = $user['email'];
    } else {
        // Se l'utente non esiste, resetta utente_id a 0
        $utente_id = 0;
    }
}

// Validazione base
$tipo = $_POST['tipo'] ?? 'altro'; // default
if (empty($titolo) || empty($descrizione) || empty($luogo)) {
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
    $filename = uniqid('paesio_') . '.' . $extension;
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
        latitudine, longitudine, stato, utente_id,
        tipo_segnalazione, nome_impiegato, ente_pubblico, feedback, commento
    ) VALUES (
        :titolo, :descrizione, :luogo, :tipo, :media_path, :nome_utente, :email_utente, 
        :latitudine, :longitudine, 'in_attesa', :utente_id,
        :tipo_segnalazione, :nome_impiegato, :ente_pubblico, :feedback, :commento
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
    ':latitudine' => null,
    ':longitudine' => null,
    ':utente_id' => $utente_id ?: null, // 👈 Se 0, imposta a NULL
	':tipo_segnalazione' => $tipo_segnalazione,
    ':nome_impiegato' => $nome_impiegato,
    ':ente_pubblico' => $ente_pubblico,
    ':feedback' => $feedback,
    ':commento' => $commento
]);

$segnalazione_id = $pdo->lastInsertId();

// Risposta JSON al frontend
echo json_encode([
    'success' => true,
    'message' => 'Segnalazione inviata con successo!',
    'id' => $segnalazione_id,
    'media_url' => $media_path ? $media_path : null
]);