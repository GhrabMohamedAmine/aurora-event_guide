<?php
require_once __DIR__ . '/../config.php';

class Messenger {
    private $id_message;
    private $id_sender;
    private $id_receiver;
    private $sender_type; // 'user' or 'sponsor'
    private $message_content;
    private $date_sent;
    private $is_read;

    // Getters
    public function getIdMessage() { return $this->id_message; }
    public function getIdSender() { return $this->id_sender; }
    public function getIdReceiver() { return $this->id_receiver; }
    public function getSenderType() { return $this->sender_type; }
    public function getMessageContent() { return $this->message_content; }
    public function getDateSent() { return $this->date_sent; }
    public function getIsRead() { return $this->is_read; }

    // Setters
    public function setIdMessage($id) { $this->id_message = $id; }
    public function setIdSender($id) { $this->id_sender = $id; }
    public function setIdReceiver($id) { $this->id_receiver = $id; }
    public function setSenderType($type) { $this->sender_type = $type; }
    public function setMessageContent($content) { $this->message_content = $content; }
    public function setDateSent($date) { $this->date_sent = $date; }
    public function setIsRead($read) { $this->is_read = $read; }

    // Database operations
    public function sendMessage($conn) {
        $query = "INSERT INTO messages (id_sender, id_receiver, sender_type, message_content, date_sent, is_read) 
                  VALUES (?, ?, ?, ?, NOW(), 0)";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(1, $this->id_sender, PDO::PARAM_INT);
        $stmt->bindParam(2, $this->id_receiver, PDO::PARAM_INT);
        $stmt->bindParam(3, $this->sender_type, PDO::PARAM_STR);
        $stmt->bindParam(4, $this->message_content, PDO::PARAM_STR);
        
        return $stmt->execute();
    }

    public static function getConversation($conn, $user_id, $sponsor_id) {
        $query = "SELECT * FROM messages 
                  WHERE (id_sender = ? AND id_receiver = ?) 
                  OR (id_sender = ? AND id_receiver = ?) 
                  ORDER BY date_sent ASC";
                  
        $stmt = $conn->prepare($query);
        $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $sponsor_id, PDO::PARAM_INT);
        $stmt->bindParam(3, $sponsor_id, PDO::PARAM_INT);
        $stmt->bindParam(4, $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $messages = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $message = new Messenger();
            $message->setIdMessage($row['id_message']);
            $message->setIdSender($row['id_sender']);
            $message->setIdReceiver($row['id_receiver']);
            $message->setSenderType($row['sender_type']);
            $message->setMessageContent($row['message_content']);
            $message->setDateSent($row['date_sent']);
            $message->setIsRead($row['is_read']);
            
            $messages[] = $message;
        }
        
        return $messages;
    }

    public static function markAsRead($conn, $message_id) {
        $query = "UPDATE messages SET is_read = 1 WHERE id_message = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(1, $message_id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    public static function getUnreadCount($conn, $user_id) {
        $query = "SELECT COUNT(*) as count FROM messages WHERE id_receiver = ? AND is_read = 0";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['count'];
    }
} 