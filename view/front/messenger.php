<?php
require_once __DIR__ . '/../../Controller/MessengerController.php';
require_once __DIR__ . '/../../Controller/SponsorController.php';

// Set the static user ID
$user_id = 456;

$messengerController = new MessengerController();
$sponsorController = new SponsorController();

// Handle message sending
if (isset($_POST['send_message']) && isset($_POST['sponsor_id']) && isset($_POST['message'])) {
    $sponsor_id = intval($_POST['sponsor_id']);
    $message = trim($_POST['message']);
    
    if (!empty($message)) {
        $messengerController->sendMessage($user_id, $sponsor_id, 'user', $message);
    }
    
    // Redirect to prevent form resubmission
    header("Location: messenger.php?sponsor=" . $sponsor_id);
    exit;
}

// Get all conversations for the user
$conversations = $messengerController->getConversationsForUser($user_id);

// Get active conversation
$active_sponsor_id = isset($_GET['sponsor']) ? intval($_GET['sponsor']) : (count($conversations) > 0 ? $conversations[0]['id_sponsor'] : null);

// Mark messages as read if viewing a conversation
if ($active_sponsor_id) {
    $messengerController->markConversationAsRead($user_id, $active_sponsor_id);
}

// Get messages for active conversation
$messages = $active_sponsor_id ? $messengerController->getConversation($user_id, $active_sponsor_id) : [];

// Get active sponsor details
$active_sponsor = null;
if ($active_sponsor_id) {
    // Assuming you have a method to get sponsor details by ID
    $active_sponsor = $sponsorController->getSponsorById($active_sponsor_id);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messagerie - Sponsoring</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #6a0dad; /* Purple to match site theme */
            --secondary-color: #d4af37; /* Gold */
            --dark-color: #4b0082; /* Dark purple */
            --light-color: #f5f7fa;
            --success-color: #28a745;
            --warning-color: #f39c12;
            --danger-color: #dc3545;
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
            width: 100%;
            height: 90vh;
            margin: 20px auto;
            max-width: 1200px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .conversation-list {
            width: 30%;
            background-color: #fff;
            border-right: 1px solid rgba(0,0,0,0.1);
            overflow-y: auto;
        }
        
        .conversation-header {
            padding: 20px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--dark-color) 100%);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .conversation-item {
            padding: 15px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #333;
            border-left: 3px solid transparent;
        }
        
        .conversation-item:hover {
            background-color: rgba(106, 13, 173, 0.05);
            border-left-color: var(--primary-color);
        }
        
        .conversation-item.active {
            background-color: rgba(106, 13, 173, 0.1);
            border-left: 3px solid var(--primary-color);
        }
        
        .conversation-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 20px;
        }
        
        .conversation-info {
            flex: 1;
        }
        
        .conversation-name {
            font-weight: 600;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }
        
        .conversation-preview {
            font-size: 13px;
            color: #777;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 150px;
        }
        
        .unread-badge {
            background-color: var(--danger-color);
            color: white;
            border-radius: 50%;
            padding: 3px 8px;
            font-size: 12px;
            margin-left: 10px;
        }
        
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background-color: #f5f7fa;
        }
        
        .chat-header {
            padding: 15px 20px;
            background-color: #fff;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .chat-header .conversation-avatar {
            width: 40px;
            height: 40px;
            font-size: 18px;
        }
        
        .chat-header .conversation-info {
            margin-left: 15px;
        }
        
        .messages-container {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            background-image: linear-gradient(rgba(255,255,255,0.8), rgba(255,255,255,0.8)), 
                              url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect width="100" height="100" fill="white"/><path d="M0,0L100,100" stroke="rgba(106,13,173,0.05)" stroke-width="1"/><path d="M100,0L0,100" stroke="rgba(106,13,173,0.05)" stroke-width="1"/></svg>');
            background-size: 20px 20px;
        }
        
        .message {
            max-width: 70%;
            padding: 12px 18px;
            border-radius: 18px;
            margin-bottom: 15px;
            position: relative;
            line-height: 1.5;
            animation: fadeIn 0.3s ease;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        
        .message.sent {
            align-self: flex-end;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--dark-color) 100%);
            color: white;
            border-bottom-right-radius: 5px;
        }
        
        .message.received {
            align-self: flex-start;
            background-color: white;
            border-bottom-left-radius: 5px;
            border-left: 3px solid var(--secondary-color);
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
            border-top: 1px solid rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            box-shadow: 0 -2px 4px rgba(0,0,0,0.05);
        }
        
        .message-input {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 25px;
            outline: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .message-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(106, 13, 173, 0.1);
        }
        
        .send-button {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--dark-color) 100%);
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
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .send-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #777;
            text-align: center;
            padding: 20px;
        }
        
        .empty-state i {
            font-size: 60px;
            margin-bottom: 20px;
            color: rgba(106, 13, 173, 0.2);
        }
        
        .empty-state p {
            max-width: 300px;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--dark-color) 100%);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        .navbar-brand {
            font-size: 20px;
            font-weight: bold;
            color: var(--secondary-color);
        }
        
        .navbar-back {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 8px 15px;
            border-radius: 50px;
            transition: all 0.3s ease;
            background-color: rgba(255,255,255,0.1);
        }
        
        .navbar-back:hover {
            background-color: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }
        
        .navbar-back i {
            margin-right: 10px;
        }
        
        @media (max-width: 768px) {
            .messenger-container {
                flex-direction: column;
                height: auto;
                margin: 0;
                border-radius: 0;
            }
            
            .conversation-list {
                width: 100%;
                max-height: 30vh;
            }
            
            .chat-area {
                height: 70vh;
            }
        }
        
        .sponsor-list {
            margin-top: 15px;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .sponsor-item {
            display: flex;
            align-items: center;
            padding: 12px;
            background-color: white;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            cursor: pointer;
            transition: all 0.3s ease;
            transition: all 0.2s ease;
        }
        
        .sponsor-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .sponsor-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        
        .sponsor-info {
            flex: 1;
        }
        
        .sponsor-name {
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .sponsor-company {
            font-size: 12px;
            color: #888;
        }
        
        .btn-start-chat {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .btn-start-chat:hover {
            background-color: #2980b9;
        }
        
        .new-conversation-btn {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 8px 15px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
        }
        
        .new-conversation-btn i {
            margin-right: 8px;
        }
        
        .new-conversation-btn:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }
        
        /* Sponsor modal styles */
        .sponsor-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }
        
        .sponsor-modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            width: 90%;
            max-width: 500px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .sponsor-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
        }
        
        .sponsor-modal-header h3 {
            margin: 0;
            color: var(--dark-color);
        }
        
        .close-modal {
            font-size: 24px;
            cursor: pointer;
            color: #888;
        }
        
        .close-modal:hover {
            color: var(--dark-color);
        }
        
        .sponsor-modal-body {
            padding: 20px;
            max-height: 60vh;
            overflow-y: auto;
        }
        
        .all-sponsors-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .sponsor-item.existing {
            opacity: 0.7;
            cursor: not-allowed;
            background-color: #f9f9f9;
        }
        
        .exists-badge {
            background-color: #ddd;
            color: #666;
            padding: 5px 10px;
            border-radius: 50px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="/chedliss/view/front/front.php" class="navbar-back">
            <i class="fas fa-arrow-left"></i> Retour au tableau de bord
        </a>
        <div class="navbar-brand">Messagerie Sponsoring</div>
        <div></div>
    </div>
    
    <div class="messenger-container">
        <div class="conversation-list">
            <div class="conversation-header">
                <h2><i class="fas fa-comments"></i> Conversations</h2>
                <button id="newConversationBtn" class="new-conversation-btn">
                    <i class="fas fa-plus-circle"></i> Nouvelle conversation
                </button>
            </div>
            
            <?php if (empty($conversations)): ?>
                <div class="empty-state">
                    <i class="fas fa-comment-slash"></i>
                    <p>Aucune conversation pour le moment</p>
                    
                    <?php 
                    // Get available sponsors
                    $available_sponsors = $sponsorController->getAllSponsors();
                    if (!empty($available_sponsors)) {
                    ?>
                        <div class="mt-4">
                            <h5>Démarrer une conversation avec un sponsor:</h5>
                            <div class="sponsor-list">
                                <?php foreach ($available_sponsors as $sponsor): ?>
                                <div class="sponsor-item" onclick="startConversation(<?php echo $sponsor->getIdSponsor(); ?>)">
                                    <div class="sponsor-avatar">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div class="sponsor-info">
                                        <div class="sponsor-name"><?php echo htmlspecialchars($sponsor->getNomSponsor() ? $sponsor->getNomSponsor() : $sponsor->getEntreprise()); ?></div>
                                        <div class="sponsor-company"><?php echo htmlspecialchars($sponsor->getEntreprise()); ?></div>
                                    </div>
                                    <button class="btn-start-chat">
                                        <i class="fas fa-comment"></i>
                                    </button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php else: ?>
                <?php foreach ($conversations as $conversation): ?>
                    <div class="conversation-item <?php echo $conversation['id_sponsor'] == $active_sponsor_id ? 'active' : ''; ?>" 
                         onclick="window.location.href='messenger.php?sponsor=<?php echo $conversation['id_sponsor']; ?>'">
                        <div class="conversation-avatar">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="conversation-info">
                            <div class="conversation-name">
                                <?php echo htmlspecialchars($conversation['nom_sponsor']); ?>
                                <?php if ($conversation['unread_count'] > 0): ?>
                                    <span class="unread-badge"><?php echo $conversation['unread_count']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="conversation-preview"><?php echo htmlspecialchars($conversation['entreprise']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="chat-area">
            <?php if ($active_sponsor_id && $active_sponsor): ?>
                <div class="chat-header">
                    <div class="conversation-avatar">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="conversation-info">
                        <div class="conversation-name"><?php echo htmlspecialchars($active_sponsor->getNomSponsor()); ?></div>
                        <div class="conversation-preview"><?php echo htmlspecialchars($active_sponsor->getEntreprise()); ?></div>
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
                            <div class="message <?php echo $message->getIdSender() == $user_id ? 'sent' : 'received'; ?>">
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
                
                <form class="message-form" method="post" action="messenger.php">
                    <input type="hidden" name="sponsor_id" value="<?php echo $active_sponsor_id; ?>">
                    <input type="text" name="message" class="message-input" placeholder="Tapez votre message ici..." autocomplete="off" required>
                    <button type="submit" name="send_message" class="send-button">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-comments"></i>
                    <p>Sélectionnez une conversation ou commencez une nouvelle conversation avec un sponsor.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Add Sponsor Selection Modal -->
    <div id="sponsorModal" class="sponsor-modal">
        <div class="sponsor-modal-content">
            <div class="sponsor-modal-header">
                <h3>Sélectionner un sponsor</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="sponsor-modal-body">
                <?php 
                // Get all sponsors
                $all_sponsors = $sponsorController->getAllSponsors();
                if (!empty($all_sponsors)): 
                ?>
                    <div class="all-sponsors-list">
                        <?php foreach ($all_sponsors as $sponsor): 
                            // Check if conversation already exists with this sponsor
                            $exists = false;
                            if (!empty($conversations)) {
                                foreach ($conversations as $conv) {
                                    if ($conv['id_sponsor'] == $sponsor->getIdSponsor()) {
                                        $exists = true;
                                        break;
                                    }
                                }
                            }
                        ?>
                            <div class="sponsor-item <?php echo $exists ? 'existing' : ''; ?>" 
                                 <?php if (!$exists): ?>onclick="startConversation(<?php echo $sponsor->getIdSponsor(); ?>)"<?php endif; ?>>
                                <div class="sponsor-avatar">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="sponsor-info">
                                    <div class="sponsor-name"><?php echo htmlspecialchars($sponsor->getNomSponsor() ? $sponsor->getNomSponsor() : $sponsor->getEntreprise()); ?></div>
                                    <div class="sponsor-company"><?php echo htmlspecialchars($sponsor->getEntreprise()); ?></div>
                                </div>
                                <?php if ($exists): ?>
                                    <div class="sponsor-status">
                                        <span class="exists-badge">Déjà connecté</span>
                                    </div>
                                <?php else: ?>
                                    <button class="btn-start-chat">
                                        <i class="fas fa-comment"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-center">Aucun sponsor disponible</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Scroll to bottom of messages container on page load
        document.addEventListener('DOMContentLoaded', function() {
            const messagesContainer = document.querySelector('.messages-container');
            if (messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
            
            // Modal functionality
            const modal = document.getElementById('sponsorModal');
            const btn = document.getElementById('newConversationBtn');
            const closeBtn = document.querySelector('.close-modal');
            
            btn.onclick = function() {
                modal.style.display = "block";
            }
            
            closeBtn.onclick = function() {
                modal.style.display = "none";
            }
            
            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }
        });
        
        // Function to start a new conversation
        function startConversation(sponsorId) {
            // Create a form to send a first empty message (this will create the conversation)
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'messenger.php';
            form.style.display = 'none';
            
            const sponsorIdInput = document.createElement('input');
            sponsorIdInput.type = 'hidden';
            sponsorIdInput.name = 'sponsor_id';
            sponsorIdInput.value = sponsorId;
            
            const messageInput = document.createElement('input');
            messageInput.type = 'hidden';
            messageInput.name = 'message';
            messageInput.value = 'Bonjour, je souhaiterais discuter d\'une opportunité de sponsoring.';
            
            const submitInput = document.createElement('input');
            submitInput.type = 'hidden';
            submitInput.name = 'send_message';
            submitInput.value = '1';
            
            form.appendChild(sponsorIdInput);
            form.appendChild(messageInput);
            form.appendChild(submitInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html> 