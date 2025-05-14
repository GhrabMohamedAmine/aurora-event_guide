<?php
require_once '../../config.php';
require_once '../../controller/user_controller.php';

if (isset($_POST['id'])) {
    $db = getDB();
    $userController = new UserController($db);
    
    $userId = $_POST['id'];
    $result = $userController->deleteUser($userId);
    
    if ($result['success']) {
        header('Location: user_back.php?message=User deleted successfully&type=success');
    } else {
        header('Location: user_back.php?message=' . urlencode($result['message']) . '&type=error');
    }
} else {
    header('Location: user_back.php?message=No user ID provided&type=error');
}
exit();
?>