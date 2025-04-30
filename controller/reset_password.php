<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/user_controller.php';

// Ajouter la bibliothèque PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Ensure proper content type for JSON responses
header('Content-Type: application/json');

// Error handling to catch PHP errors and convert them to JSON
function handleError($errno, $errstr, $errfile, $errline) {
    $response = [
        'success' => false,
        'message' => 'Erreur PHP: ' . $errstr,
        'error_details' => [
            'file' => $errfile,
            'line' => $errline,
            'type' => $errno
        ]
    ];
    echo json_encode($response);
    exit;
}

// Set custom error handler
set_error_handler('handleError');

// Capture fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $response = [
            'success' => false,
            'message' => 'Erreur fatale: ' . $error['message'],
            'error_details' => [
                'file' => $error['file'],
                'line' => $error['line'],
                'type' => $error['type']
            ]
        ];
        echo json_encode($response);
    }
});

try {
    // Check if PHPMailer files exist before requiring them
    $phpMailerFiles = [
        __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php',
        __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php',
        __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php'
    ];
    
    $missingFiles = [];
    foreach ($phpMailerFiles as $file) {
        if (!file_exists($file)) {
            $missingFiles[] = basename($file);
        }
    }
    
    if (!empty($missingFiles)) {
        throw new Exception('Fichiers PHPMailer manquants: ' . implode(', ', $missingFiles) . '. Veuillez exécuter install_phpmailer.php.');
    }
    
    // Require PHPMailer files
    require __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
    require __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';

    // Démarrer la session
    session_start();

    // Connexion à la base de données
    $db = config::getConnexion();
    $userController = new UserController($db);

    // Configuration de l'email
    $emailSender = 'tahahkiri69@gmail.com';
    $emailPassword = 'fztf sppx mbgo oors'; // Mot de passe d'application Gmail
    $emailName = 'Aurora Event';

    // Gérer les différentes actions
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        switch ($action) {
            case 'send_reset_code':
                sendResetCode();
                break;
            case 'verify_code':
                verifyCode();
                break;
            case 'resend_code':
                sendResetCode(true);
                break;
            case 'reset_password':
                resetPassword();
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Action non valide']);
                break;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Aucune action spécifiée']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// Fonction pour envoyer un code de réinitialisation
function sendResetCode($resend = false) {
    global $db, $emailSender, $emailPassword, $emailName;
    
    // Vérifier si l'email est fourni
    if (!isset($_POST['email']) || empty($_POST['email'])) {
        echo json_encode(['success' => false, 'message' => 'Adresse email requise']);
        return;
    }
    
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    // Vérifier si l'utilisateur existe
    $stmt = $db->prepare("SELECT id_user FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        echo json_encode(['success' => false, 'message' => 'Aucun compte associé à cette adresse email']);
        return;
    }
    
    // Générer un code de vérification à 6 chiffres
    $verificationCode = mt_rand(100000, 999999);
    
    // Stocker le code dans la session (dans une application réelle, vous voudriez le stocker dans la base de données)
    $_SESSION['reset_code'] = [
        'email' => $email,
        'code' => $verificationCode,
        'expires' => time() + 3600 // Expire dans 1 heure
    ];
    
    // Envoyer l'email avec PHPMailer
    $mail = new PHPMailer(true);
    
    try {
        // Configuration du serveur
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $emailSender;
        $mail->Password = $emailPassword;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Expéditeurs et destinataires
        $mail->setFrom($emailSender, $emailName);
        $mail->addAddress($email);
        
        // Contenu
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Réinitialisation de votre mot de passe Aurora Event';
        
        // Corps du message HTML
        $mail->Body = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 5px;">
            <div style="text-align: center; padding: 20px 0;">
                <h2 style="color: #6A1B9A; margin: 0;">Aurora Event</h2>
                <p style="color: #666;">Réinitialisation de votre mot de passe</p>
            </div>
            
            <div style="padding: 20px; background-color: #f9f9f9; border-radius: 5px;">
                <p>Bonjour,</p>
                <p>Nous avons reçu une demande de réinitialisation de mot de passe pour votre compte Aurora Event.</p>
                <p>Voici votre code de vérification :</p>
                
                <div style="text-align: center; margin: 30px 0;">
                    <div style="font-size: 24px; letter-spacing: 5px; padding: 15px; background-color: #f0f0f0; border-radius: 5px; display: inline-block; font-weight: bold; color: #333;">' . $verificationCode . '</div>
                </div>
                
                <p>Ce code est valable pendant 1 heure.</p>
                <p>Si vous n\'avez pas demandé de réinitialisation de mot de passe, vous pouvez ignorer cet email.</p>
            </div>
            
            <div style="text-align: center; padding: 20px 0; color: #888; font-size: 12px;">
                <p>© ' . date('Y') . ' Aurora Event. Tous droits réservés.</p>
                <p>Ceci est un email automatique, merci de ne pas y répondre.</p>
            </div>
        </div>';
        
        // Version texte alternative
        $mail->AltBody = 'Votre code de vérification pour réinitialiser votre mot de passe Aurora Event est : ' . $verificationCode . '. Ce code est valable pendant 1 heure.';
        
        $mail->send();
        
        echo json_encode(['success' => true, 'message' => $resend ? 'Code renvoyé avec succès' : 'Code envoyé avec succès']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => "Erreur lors de l'envoi de l'email : {$mail->ErrorInfo}"]);
    }
}

// Fonction pour vérifier le code
function verifyCode() {
    if (!isset($_POST['email']) || !isset($_POST['code']) || empty($_POST['email']) || empty($_POST['code'])) {
        echo json_encode(['success' => false, 'message' => 'Email et code requis']);
        return;
    }
    
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $code = $_POST['code'];
    
    // Vérifier si le code existe dans la session
    if (!isset($_SESSION['reset_code']) || $_SESSION['reset_code']['email'] !== $email) {
        echo json_encode(['success' => false, 'message' => 'Code de vérification invalide ou expiré']);
        return;
    }
    
    $resetCode = $_SESSION['reset_code'];
    
    // Vérifier si le code est expiré
    if (time() > $resetCode['expires']) {
        echo json_encode(['success' => false, 'message' => 'Code de vérification expiré']);
        return;
    }
    
    // Vérifier si le code est correct
    if ($resetCode['code'] !== (int)$code) {
        echo json_encode(['success' => false, 'message' => 'Code de vérification incorrect']);
        return;
    }
    
    // Le code est correct
    $_SESSION['reset_code']['verified'] = true;
    echo json_encode(['success' => true, 'message' => 'Code vérifié avec succès']);
}

// Fonction pour réinitialiser le mot de passe
function resetPassword() {
    global $db;
    
    if (!isset($_POST['email']) || !isset($_POST['password']) || empty($_POST['email']) || empty($_POST['password'])) {
        echo json_encode(['success' => false, 'message' => 'Email et nouveau mot de passe requis']);
        return;
    }
    
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    // Vérifier si le code a été vérifié
    if (!isset($_SESSION['reset_code']) || $_SESSION['reset_code']['email'] !== $email || !isset($_SESSION['reset_code']['verified']) || $_SESSION['reset_code']['verified'] !== true) {
        echo json_encode(['success' => false, 'message' => 'Vous devez vérifier votre code avant de réinitialiser votre mot de passe']);
        return;
    }
    
    // Hasher le nouveau mot de passe
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Mettre à jour le mot de passe dans la base de données
    try {
        $stmt = $db->prepare("UPDATE users SET mot_de_pass = :password WHERE email = :email");
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            // Nettoyer la session
            unset($_SESSION['reset_code']);
            
            echo json_encode(['success' => true, 'message' => 'Mot de passe réinitialisé avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour du mot de passe']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
    }
} 