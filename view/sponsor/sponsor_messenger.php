<?php
require_once __DIR__ . '/../../Controller/MessengerController.php';
require_once __DIR__ . '/../../Model/Sponsor.php';

// Get the sponsor ID from session (assuming it's stored there)
// For this example, let's assume we're using a specific sponsor
session_start();
if (!isset($_SESSION['sponsor_id'])) {
    // Redirect to login if not logged in
    header("Location: login.php");
    exit;
}

$sponsor_id = $_SESSION['sponsor_id'];

$messengerController = new MessengerController();

// Handle message sending
if (isset($_POST['send_message']) && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    
    if (!empty($message)) {
        // Using static user ID 456 as requested
        $messengerController->sendMessage($sponsor_id, 456, 'sponsor', $message);
    }
    
    // Redirect to prevent form resubmission
    header("Location: sponsor_messenger.php");
    exit;
}

// Get conversations for this sponsor
$conversations = $messengerController->getConversationsForSponsor($sponsor_id);

// Mark messages as read when viewing
$messengerController->markConversationAsRead($sponsor_id, 456);

// Get messages between sponsor and user
$messages = $messengerController->getConversation($sponsor_id, 456);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messagerie Sponsor</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #e74c3c;  /* Red for sponsor interface */
            --secondary-color: #3498db;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
        }
        
        .messenger-container {
            display: flex;
            flex-direction: column;
            width: 100%;
            height: 90vh;
            margin: 20px auto;
            max-width: 1200px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        
        .chat-header {
            padding: 15px 20px;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--secondary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 18px;
        }
        
        .user-info {
            flex: 1;
        }
        
        .user-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .user-status {
            font-size: 12px;
            opacity: 0.9;
        }
        
        .messages-container {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            background-color: #f5f7fa;
        }
        
        .message {
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 18px;
            margin-bottom: 15px;
            position: relative;
            line-height: 1.5;
        }
        
        .message.sent {
            align-self: flex-end;
            background-color: var(--primary-color);
            color: white;
            border-bottom-right-radius: 5px;
        }
        
        .message.received {
            align-self: flex-start;
            background-color: white;
            border-bottom-left-radius: 5px;
        }
        
        .message-time {
            font-size: 11px;
            opacity: 0.8;
            margin-top: 5px;
            text-align: right;
        }
        
        .message-form {
            padding: 15px;
            background-color: #fff;
            border-top: 1px solid #eaeaea;
            display: flex;
            align-items: center;
        }
        
        .message-input {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 25px;
            outline: none;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        
        .message-input:focus {
            border-color: var(--primary-color);
        }
        
        .send-button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            margin-left: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
        }
        
        .send-button:hover {
            background-color: #c0392b;
        }
        
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #888;
            text-align: center;
            padding: 20px;
        }
        
        .empty-state i {
            font-size: 50px;
            margin-bottom: 15px;
            color: #ddd;
        }
        
        .empty-state p {
            max-width: 300px;
            line-height: 1.6;
        }
        
        .navbar {
            background-color: var(--dark-color);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }
        
        .navbar-brand {
            font-size: 20px;
            font-weight: bold;
        }
        
        .navbar-back {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .navbar-back i {
            margin-right: 10px;
        }
        
        .notification-badge {
            position: relative;
            display: inline-block;
        }
        
        .unread-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: var(--secondary-color);
            color: white;
            border-radius: 50%;
            padding: 3px 6px;
            font-size: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="/chedliss/view/sponsor/sponsor_dashboard.php" class="navbar-back">
            <i class="fas fa-arrow-left"></i> Retour au tableau de bord
        </a>
        <div class="navbar-brand">Messagerie Sponsoring</div>
        <div class="notification-badge">
            <i class="fas fa-bell"></i>
            <?php if ($conversations[0]['unread_count'] > 0): ?>
                <span class="unread-count"><?php echo $conversations[0]['unread_count']; ?></span>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="messenger-container">
        <div class="chat-header">
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="user-info">
                <div class="user-name">Utilisateur #456</div>
                <div class="user-status">
                    <?php 
                    if (!empty($messages)) {
                        $lastMessage = end($messages);
                        $date = new DateTime($lastMessage->getDateSent());
                        echo "Dernier message: " . $date->format('H:i - d/m/Y');
                    } else {
                        echo "Aucune activité récente";
                    }
                    ?>
                </div>
            </div>
        </div>
        
        <div class="messages-container">
            <?php if (empty($messages)): ?>
                <div class="empty-state">
                    <i class="fas fa-comment-dots"></i>
                    <p>Aucun message pour le moment. Envoyez un message pour démarrer la conversation.</p>
                </div>
            <?php else: ?>
                <?php foreach ($messages as $message): ?>
                    <div class="message <?php echo $message->getIdSender() == $sponsor_id ? 'sent' : 'received'; ?>">
                        <?php echo nl2br(htmlspecialchars($message->getMessageContent())); ?>
                        <div class="message-time">
                            <?php 
                            $date = new DateTime($message->getDateSent());
                            echo $date->format('H:i - d/m/Y'); 
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <form class="message-form" method="post" action="sponsor_messenger.php">
            <input type="text" name="message" class="message-input" placeholder="Tapez votre message ici..." autocomplete="off" required>
            <button type="submit" name="send_message" class="send-button">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </div>
    
    <script>
        // Scroll to bottom of messages container on page load
        document.addEventListener('DOMContentLoaded', function() {
            const messagesContainer = document.querySelector('.messages-container');
            if (messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        });
    </script>
</body>
</html> 