<?php
require_once __DIR__ . '/../Model/Messenger.php';
require_once __DIR__ . '/../Model/Sponsor.php';
require_once __DIR__ . '/../config/db.php';

class MessengerController {
    private $conn;
    
    public function __construct() {
        $this->conn = Database::getConnection();
    }
    
    public function sendMessage($id_sender, $id_receiver, $sender_type, $message_content) {
        $messenger = new Messenger();
        $messenger->setIdSender($id_sender);
        $messenger->setIdReceiver($id_receiver);
        $messenger->setSenderType($sender_type);
        $messenger->setMessageContent($message_content);
        
        return $messenger->sendMessage($this->conn);
    }
    
    public function getConversation($user_id, $sponsor_id) {
        return Messenger::getConversation($this->conn, $user_id, $sponsor_id);
    }
    
    public function getConversationsForUser($user_id) {
        $query = "SELECT DISTINCT s.id_sponsor, s.nom_sponsor, s.entreprise, 
                 (SELECT COUNT(*) FROM messages WHERE id_receiver = ? AND id_sender = s.id_sponsor AND is_read = 0) as unread_count,
                 (SELECT date_sent FROM messages 
                  WHERE (id_sender = ? AND id_receiver = s.id_sponsor) 
                  OR (id_sender = s.id_sponsor AND id_receiver = ?) 
                  ORDER BY date_sent DESC LIMIT 1) as last_message_date
                 FROM sponsor s
                 INNER JOIN messages m ON (m.id_sender = s.id_sponsor AND m.id_receiver = ?) 
                                        OR (m.id_sender = ? AND m.id_receiver = s.id_sponsor)
                 ORDER BY last_message_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $user_id, PDO::PARAM_INT);
        $stmt->bindParam(3, $user_id, PDO::PARAM_INT);
        $stmt->bindParam(4, $user_id, PDO::PARAM_INT);
        $stmt->bindParam(5, $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getConversationsForSponsor($sponsor_id) {
        $query = "SELECT DISTINCT 456 as user_id, 'User' as user_name, 
                 (SELECT COUNT(*) FROM messages WHERE id_receiver = ? AND id_sender = 456 AND is_read = 0) as unread_count,
                 (SELECT date_sent FROM messages 
                  WHERE (id_sender = ? AND id_receiver = 456) 
                  OR (id_sender = 456 AND id_receiver = ?) 
                  ORDER BY date_sent DESC LIMIT 1) as last_message_date
                 FROM messages m 
                 WHERE (m.id_sender = ? AND m.id_receiver = 456) 
                 OR (m.id_sender = 456 AND m.id_receiver = ?)
                 ORDER BY last_message_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $sponsor_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $sponsor_id, PDO::PARAM_INT);
        $stmt->bindParam(3, $sponsor_id, PDO::PARAM_INT);
        $stmt->bindParam(4, $sponsor_id, PDO::PARAM_INT);
        $stmt->bindParam(5, $sponsor_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function markMessageAsRead($message_id) {
        return Messenger::markAsRead($this->conn, $message_id);
    }
    
    public function markConversationAsRead($user_id, $sponsor_id) {
        $query = "UPDATE messages 
                 SET is_read = 1 
                 WHERE id_receiver = ? AND id_sender = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $sponsor_id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    public function getUnreadCount($user_id) {
        return Messenger::getUnreadCount($this->conn, $user_id);
    }
} 