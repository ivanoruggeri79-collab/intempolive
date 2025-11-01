<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    die("Accesso negato");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Metodo non consentito");
}

$id = intval($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($id <= 0 || !in_array($action, ['approve', 'reject'])) {
    die("Parametri non validi");
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=comune_segnalazioni;charset=utf8mb4", 'Paesio_user', 'Moruccetto0007');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $new_status = $action === 'approve' ? 'approvata' : 'rifiutata';
    $stmt = $pdo->prepare("UPDATE segnalazioni SET stato = :stato, data_approvazione = NOW() WHERE id = :id");
    $stmt->execute([
        ':stato' => $new_status,
        ':id' => $id
    ]);
// Recupera i dati dell'utente e della segnalazione
$stmt = $pdo->prepare("
    SELECT s.titolo, s.luogo, u.email, u.nome_completo, u.email_notifiche
    FROM segnalazioni s
    LEFT JOIN utenti u ON s.utente_id = u.id
    WHERE s.id = :id
");
$stmt->execute([':id' => $id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

// Pubblica su Facebook Page (solo se approvata)
if ($data && $new_status === 'approvata') {
    // Messaggio da pubblicare
    $message = "
        ?? Nuova segnalazione approvata!

        Titolo: {$data['titolo']}
        Luogo: {$data['luogo']}
        Descrizione: {$data['descrizione']}

        Segnalata da {$data['nome_completo']}

        Vedi di pi�: http://localhost/Paesio/social.html
    ";

    // Logga il messaggio (per test)
    error_log("Pubblicazione su Facebook: " . $message);

    // Qui puoi integrare un servizio come Zapier o Make per pubblicare davvero su Facebook
    // Per ora, il messaggio viene loggato nei file di errore di PHP
}

// Invia email se l'utente ha attivato le notifiche
if ($data && $data['email'] && $data['email_notifiche'] === 'si') {
    $to = $data['email'];
    $subject = "🎉 La tua segnalazione è stata approvata!";
    $message = "
        Ciao {$data['nome_completo']},

        La tua segnalazione '{$data['titolo']}' a '{$data['luogo']}' è stata approvata dalla comunità!

        Grazie per aver contribuito a migliorare il tuo comune.

        Visita il nostro feed sociale per vederla: http://localhost/Paesio/social.html

        Saluti,
        Team Paesio
    ";
    $headers = "From: no-reply@paesio.it\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    mail($to, $subject, $message, $headers);
}
    // Redirect alla dashboard con messaggio
    $message = $action === 'approve' ? 'Segnalazione approvata!' : 'Segnalazione rifiutata!';
    header("Location: index.php?msg=" . urlencode($message));
    exit;

} catch (PDOException $e) {
    die("Errore: " . $e->getMessage());
}