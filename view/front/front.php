<?php
require_once __DIR__.'/../../config/db.php';
require_once __DIR__.'/../../controller/DemandeSponsoringController.php';
require_once __DIR__.'/../../controller/SponsorController.php';

$controller = new DemandeSponsoringController();
$sponsorController = new SponsorController();

// Ajout du bouton de navigation vers offre.php
echo '<div style="position: fixed; top: 20px; left: 20px; z-index: 1000;">
        <a href="../back/offre.php" class="nav-button" id="offre-nav-btn" style="display: flex; align-items: center; gap: 8px; 
           background: linear-gradient(135deg, #6a0dad 0%, #4b0082 100%); color: white; 
           padding: 10px 16px; border-radius: 50px; text-decoration: none; font-weight: 600;
           font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; box-shadow: 0 4px 10px rgba(0,0,0,0.2);
           transition: all 0.3s ease;">
           <i class="fas fa-list-alt"></i> Gérer les Demandes de Sponsoring
        </a>
      </div>
      <script>
        document.addEventListener("DOMContentLoaded", function() {
            const navBtn = document.getElementById("offre-nav-btn");
            navBtn.addEventListener("mouseover", function() {
                this.style.transform = "translateY(-3px)";
                this.style.boxShadow = "0 6px 15px rgba(106, 13, 173, 0.4)";
            });
            navBtn.addEventListener("mouseout", function() {
                this.style.transform = "translateY(0)";
                this.style.boxShadow = "0 4px 10px rgba(0,0,0,0.2)";
            });
        });
      </script>';

// Traitement de la suppression
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    $id = $_POST['id'];
    if ($sponsorController->deleteFront($id)) {
        header('Location: front.php?delete_success=1');
        exit();
    } else {
        header('Location: front.php?delete_error=1');
        exit();
    }
}

if (isset($_GET['success']) && $_GET['success'] == 1) {
    echo '<div style="position: fixed; top: 20px; right: 20px; background: #4CAF50; color: white; padding: 15px; border-radius: 5px; z-index: 1000; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
            <i class="fas fa-check-circle"></i> Sponsor ajouté avec succès!
          </div>';
}

if (isset($_GET['update_success']) && $_GET['update_success'] == 1) {
    echo '<div style="position: fixed; top: 20px; right: 20px; background: #4CAF50; color: white; padding: 15px; border-radius: 5px; z-index: 1000; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
            <i class="fas fa-check-circle"></i> Sponsor modifié avec succès!
          </div>';
}

if (isset($_GET['delete_success']) && $_GET['delete_success'] == 1) {
    echo '<div style="position: fixed; top: 20px; right: 20px; background: #4CAF50; color: white; padding: 15px; border-radius: 5px; z-index: 1000; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
            <i class="fas fa-check-circle"></i> Sponsor supprimé avec succès!
          </div>';
}

if (isset($_GET['delete_error']) && $_GET['delete_error'] == 1) {
    echo '<div style="position: fixed; top: 20px; right: 20px; background: #dc3545; color: white; padding: 15px; border-radius: 5px; z-index: 1000; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
            <i class="fas fa-exclamation-circle"></i> Erreur lors de la suppression du sponsor.
          </div>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $result = $controller->add(
            $_POST['id_sponsor'],
            $_POST['id_organisateur'],
            $_POST['montant'],
            $_POST['idevent']
        );

        if ($result) {
            echo '<div style="color: green;">Demande enregistrée avec succès.</div>';
        } else {
            echo '<div style="color: red;">Erreur lors de l\'enregistrement de la demande.</div>';
        }
    } catch (Exception $e) {
        echo '<div style="color: red;">Erreur: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande de Sponsoring - Aroura event</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        :root {
            --primary-color: #6a0dad; /* Violet principal */
            --secondary-color: #d4af37; /* Doré */
            --light-purple: #9c73b8;
            --dark-purple: #4b0082;
            --light-gold: #f0e6d2;
            --white: #ffffff;
            --light-gray: #f5f5f5;
            --dark-gray: #333333;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--light-gray);
            color: var(--dark-gray);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            padding: 40px 20px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--dark-purple) 100%);
            color: white;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://images.unsplash.com/photo-1511578314322-379afb476865?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') center/cover;
            opacity: 0.2;
            z-index: 0;
        }
        
        .header-content {
            position: relative;
            z-index: 1;
        }
        
        .header h1 {
            color: var(--secondary-color);
            font-size: 2.8rem;
            margin-bottom: 15px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
        }
        
        .header p {
            color: var(--light-gold);
            font-size: 1.2rem;
            max-width: 800px;
            margin: 0 auto 25px;
        }
        
        .stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .stat-item {
            background: rgba(255,255,255,0.1);
            padding: 15px 25px;
            border-radius: 8px;
            backdrop-filter: blur(5px);
            text-align: center;
            min-width: 150px;
        }
        
        .stat-number {
            font-size: 2.2rem;
            font-weight: bold;
            color: var(--secondary-color);
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 12px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        
        .btn i {
            margin-right: 10px;
        }
        
        .btn-purple {
            background: linear-gradient(to right, var(--primary-color), var(--dark-purple));
            color: white;
            border: none;
        }
        
        .btn-purple:hover {
            background: linear-gradient(to right, var(--dark-purple), var(--primary-color));
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(106, 13, 173, 0.3);
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--secondary-color);
            color: var(--secondary-color);
        }
        
        .btn-outline:hover {
            background: var(--secondary-color);
            color: var(--dark-purple);
        }
        
        /* Sponsorship Packages */
        .packages-section {
            margin-bottom: 50px;
        }
        
        .section-title {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 30px;
            position: relative;
            font-size: 2rem;
        }
        
        .section-title:after {
            content: "";
            display: block;
            width: 100px;
            height: 3px;
            background: var(--secondary-color);
            margin: 15px auto 0;
        }
        
        .packages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .package-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            border-top: 5px solid var(--secondary-color);
            position: relative;
        }
        
        .package-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(106, 13, 173, 0.2);
        }
        
        .popular-badge {
            position: absolute;
            top: -10px;
            right: 20px;
            background: var(--secondary-color);
            color: var(--dark-purple);
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.8rem;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        }
        
        .package-header {
            background: linear-gradient(to right, var(--primary-color), var(--light-purple));
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .package-name {
            margin: 0;
            font-size: 1.5rem;
        }
        
        .package-price {
            font-size: 2rem;
            margin: 10px 0;
            font-weight: bold;
            color: var(--secondary-color);
        }
        
        .package-price small {
            font-size: 1rem;
            opacity: 0.8;
        }
        
        .package-body {
            padding: 25px;
        }
        
        .package-features {
            list-style: none;
            padding: 0;
            margin: 0 0 25px;
        }
        
        .package-features li {
            padding: 8px 0;
            border-bottom: 1px dashed #eee;
            display: flex;
            align-items: center;
        }
        
        .package-features li:last-child {
            border-bottom: none;
        }
        
        .package-features i {
            margin-right: 10px;
            color: var(--secondary-color);
        }
        
        .package-actions {
            text-align: center;
        }
        
        /* Request Form */
        .request-section {
            background: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 50px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: inherit;
            font-size: 1rem;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(106, 13, 173, 0.2);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .form-actions {
            text-align: center;
            margin-top: 30px;
        }
        
        /* Benefits Section */
        .benefits-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--dark-purple) 100%);
            color: white;
            padding: 50px 0;
            border-radius: 10px;
            margin-bottom: 50px;
        }
        
        .benefits-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 30px;
        }
        
        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        
        .benefit-card {
            text-align: center;
            padding: 20px;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            transition: all 0.3s;
        }
        
        .benefit-card:hover {
            transform: translateY(-5px);
            background: rgba(255,255,255,0.15);
        }
        
        .benefit-icon {
            font-size: 3rem;
            color: var(--secondary-color);
            margin-bottom: 20px;
        }
        
        .benefit-card h3 {
            margin-top: 0;
        }
        
        /* Testimonials */
        .testimonials-section {
            margin-bottom: 50px;
        }
        
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .testimonial-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        
        .testimonial-card:before {
            content: "";
            font-family: Georgia, serif;
            font-size: 4rem;
            color: var(--light-purple);
            opacity: 0.3;
            position: absolute;
            top: 10px;
            left: 10px;
        }
        
        .testimonial-content {
            font-style: italic;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
        }
        
        .testimonial-author img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
        }
        
        .author-info h4 {
            margin: 0;
            color: var(--primary-color);
        }
        
        .author-info p {
            margin: 5px 0 0;
            font-size: 0.9rem;
            color: var(--dark-gray);
        }
        
        /* Dashboard */
        .dashboard-section {
            background: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 50px;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .dashboard-title {
            color: var(--primary-color);
            margin: 0;
        }
        
        .dashboard-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid var(--light-purple);
            padding-bottom: 10px;
        }

        .tab-button {
            padding: 12px 25px;
            border: none;
            background: none;
            color: var(--dark-gray);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tab-button i {
            font-size: 18px;
        }

        .tab-button:after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--primary-color);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .tab-button:hover {
            color: var(--primary-color);
        }

        .tab-button.active {
            color: var(--primary-color);
        }

        .tab-button.active:after {
            transform: scaleX(1);
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.5s ease;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card i {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .stat-card h4 {
            color: var(--dark-gray);
            margin: 10px 0;
            font-size: 1.2rem;
        }

        .stat-card .value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        .messages-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-top: 20px;
        }

        .message-item {
            padding: 20px;
            border-bottom: 1px solid var(--light-gray);
            display: flex;
            align-items: flex-start;
            gap: 15px;
        }

        .message-item:last-child {
            border-bottom: none;
        }

        .message-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--light-purple);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .message-content {
            flex: 1;
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .message-sender {
            font-weight: 600;
            color: var(--dark-gray);
        }

        .message-date {
            color: #666;
            font-size: 0.9rem;
        }

        .message-text {
            color: var(--dark-gray);
            line-height: 1.5;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--dark-gray);
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--light-purple);
            margin-bottom: 20px;
        }

        .empty-state h3 {
            margin: 10px 0;
            font-size: 1.5rem;
        }

        .empty-state p {
            color: #666;
            margin-bottom: 20px;
        }
        
        /* Messagerie */
        .messages-container {
            display: flex;
            gap: 20px;
        }
        
        .conversations-list {
            width: 300px;
            background: var(--light-gray);
            border-radius: 10px;
            overflow: hidden;
        }
        
        .conversation-header {
            background: var(--primary-color);
            color: white;
            padding: 15px;
            font-weight: bold;
        }
        
        .conversation-item {
            padding: 15px;
            border-bottom: 1px solid #ddd;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .conversation-item:hover {
            background: white;
        }
        
        .conversation-item.active {
            background: white;
            border-left: 3px solid var(--primary-color);
        }
        
        .conversation-name {
            margin: 0 0 5px;
            color: var(--primary-color);
        }
        
        .conversation-preview {
            font-size: 0.9rem;
            color: var(--dark-gray);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .message-area {
            flex: 1;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .message-header {
            background: var(--primary-color);
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .message-list {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            max-height: 400px;
        }
        
        .message {
            margin-bottom: 15px;
            max-width: 70%;
        }
        
        .message.received {
            align-self: flex-start;
        }
        
        .message.sent {
            align-self: flex-end;
        }
        
        .message-content {
            padding: 10px 15px;
            border-radius: 15px;
            position: relative;
        }
        
        .message.received .message-content {
            background: var(--light-gray);
            color: var(--dark-gray);
        }
        
        .message.sent .message-content {
            background: var(--primary-color);
            color: white;
        }
        
        .message-time {
            font-size: 0.7rem;
            color: var(--dark-gray);
            margin-top: 5px;
            text-align: right;
        }
        
        .message-input-area {
            padding: 15px;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
        }
        
        .message-input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 20px;
            resize: none;
        }
        
        .send-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .send-btn:hover {
            background: var(--dark-purple);
        }
        
        /* ROI Calculator */
        .calculator-section {
            background: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 50px;
        }
        
        .calculator-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .calculator-results {
            background: var(--light-gray);
            border-radius: 10px;
            padding: 30px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .result-item {
            margin-bottom: 20px;
        }
        
        .result-label {
            font-size: 0.9rem;
            color: var(--dark-gray);
            margin-bottom: 5px;
        }
        
        .result-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .result-value.positive {
            color: #27ae60;
        }
        
        .result-value.negative {
            color: #e74c3c;
        }
        
        /* Media Coverage */
        .media-section {
            margin-bottom: 50px;
        }
        
        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .media-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .media-image {
            height: 150px;
            background-size: cover;
            background-position: center;
        }
        
        .media-content {
            padding: 15px;
        }
        
        .media-title {
            margin: 0 0 10px;
            color: var(--primary-color);
        }
        
        .media-date {
            font-size: 0.8rem;
            color: var(--dark-gray);
            margin-bottom: 10px;
        }
        
        .media-excerpt {
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .media-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: bold;
            font-size: 0.9rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .package-card {
                margin-bottom: 30px;
            }
            
            .calculator-grid {
                grid-template-columns: 1fr;
            }
            
            .messages-container {
                flex-direction: column;
            }
            
            .conversations-list {
                width: 100%;
            }
        }
        
        .sponsorships-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .sponsorship-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(106, 13, 173, 0.1);
            padding: 25px;
            transition: all 0.3s ease;
            border: 1px solid rgba(106, 13, 173, 0.05);
            position: relative;
            overflow: hidden;
        }

        .sponsorship-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(106, 13, 173, 0.2);
        }
        
        .sponsorship-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
        }

        .sponsorship-header {
            border-bottom: 2px solid rgba(106, 13, 173, 0.1);
            margin-bottom: 20px;
            padding-bottom: 15px;
            position: relative;
        }

        .sponsorship-header h4 {
            margin: 0;
            color: var(--dark-purple);
            font-size: 1.4rem;
            font-weight: 700;
        }
        
        .sponsor-photo-container {
            text-align: center;
            margin-bottom: 15px;
            border-radius: 10px;
            overflow: hidden;
            max-height: 200px;
        }
        
        .sponsor-photo {
            max-width: 100%;
            max-height: 200px;
            object-fit: contain;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        
        .sponsorship-card:hover .sponsor-photo {
            transform: scale(1.05);
        }

        .sponsorship-details p {
            margin: 12px 0;
            color: var(--dark-gray);
            font-size: 1.05rem;
            display: flex;
            align-items: center;
        }

        .sponsorship-details strong {
            color: var(--primary-color);
            display: inline-block;
            width: 100px;
        }
        
        .sponsorship-details p i {
            margin-right: 10px;
            color: var(--secondary-color);
            font-size: 1.1rem;
        }

        .sponsor-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
            border-top: 1px solid rgba(106, 13, 173, 0.1);
            padding-top: 15px;
        }
        
        .action-btn {
            background: none;
            border: none;
            padding: 8px 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 50px;
            margin-left: 10px;
        }

        .action-btn.edit {
            color: var(--primary-color);
            background-color: rgba(106, 13, 173, 0.1);
        }

        .action-btn.delete {
            color: #dc3545;
            background-color: rgba(220, 53, 69, 0.1);
        }

        .action-btn:hover {
            transform: translateY(-3px);
        }
        
        .action-btn.edit:hover {
            background-color: rgba(106, 13, 173, 0.2);
        }
        
        .action-btn.delete:hover {
            background-color: rgba(220, 53, 69, 0.2);
        }
        
        .tab-content#sponsorships h3 {
            color: var(--primary-color);
            font-size: 1.8rem;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid var(--secondary-color);
            display: inline-block;
        }
        
        .no-sponsors-message {
            text-align: center;
            padding: 40px 20px;
            background-color: rgba(106, 13, 173, 0.05);
            border-radius: 10px;
            color: var(--dark-gray);
            font-size: 1.1rem;
        }
        
        .no-sponsors-message i {
            display: block;
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 15px;
            opacity: 0.5;
        }

        /* Confirmation Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            border-radius: 15px;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 0;
            overflow: hidden;
            animation: modalFadeIn 0.3s ease;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-header {
            background: var(--primary-color);
            color: white;
            padding: 20px;
            position: relative;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.3rem;
        }

        .modal-body {
            padding: 25px;
            font-size: 1.1rem;
            color: var(--dark-gray);
        }

        .modal-footer {
            padding: 15px 25px 25px;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }

        .modal-btn {
            padding: 10px 20px;
            border-radius: 50px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .modal-btn-cancel {
            background-color: #f1f1f1;
            color: var(--dark-gray);
        }

        .modal-btn-delete {
            background-color: #dc3545;
            color: white;
        }

        .modal-btn:hover {
            transform: translateY(-3px);
        }

        .modal-btn-cancel:hover {
            background-color: #e5e5e5;
        }

        .modal-btn-delete:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <h1><i class="fas fa-crown"></i> Devenez Sponsor de Aroura event</h1>
                <p>Associez votre marque à un événement prestigieux et bénéficiez d'une visibilité exceptionnelle auprès d'un public ciblé et influent.</p>
                <a href="#request-form" class="btn btn-outline">Devenir Sponsor</a>
                
                <div class="stats">
                    <div class="stat-item">
                        <div class="stat-number">250+</div>
                        <div class="stat-label">Sponsors Satisfaits</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">15M€</div>
                        <div class="stat-label">ROI Généré</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">95%</div>
                        <div class="stat-label">Taux de Renouvellement</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="packages-section">
            <h2 class="section-title">Nos Offres de Sponsoring Exclusives</h2>
            <p style="text-align: center; max-width: 800px; margin: 0 auto 30px;">Choisissez le package qui correspond à vos objectifs marketing et bénéficiez d'avantages sur mesure pour maximiser votre retour sur investissement.</p>
            
            <div class="packages-grid">
                <!-- Package 1 -->
                <div class="package-card">
                    <div class="popular-badge">Le Plus Populaire</div>
                    <div class="package-header">
                        <h3 class="package-name">Platine</h3>
                        <div class="package-price">15 000 € <small>/événement</small></div>
                        <p>Visibilité maximale et exclusivité</p>
                    </div>
                    <div class="package-body">
                        <ul class="package-features">
                            <li><i class="fas fa-check"></i> Logo en position premium sur tous supports</li>
                            <li><i class="fas fa-check"></i> Stand VIP à l'entrée principale (20m²)</li>
                            <li><i class="fas fa-check"></i> Keynote de 30 minutes devant l'audience</li>
                            <li><i class="fas fa-check"></i> Mention dans tous les communiqués presse</li>
                            <li><i class="fas fa-check"></i> 10 invitations VIP avec accès backstage</li>
                            <li><i class="fas fa-check"></i> Interview exclusive dans notre magazine</li>
                            <li><i class="fas fa-check"></i> Reportage vidéo dédié sur nos réseaux</li>
                        </ul>
                        <div class="package-actions">
                            <a href="#request-form" class="btn btn-purple" data-price="15000" data-package="Platine">Choisir cette offre</a>
                        </div>
                    </div>
                </div>
                
                <!-- Package 2 -->
                <div class="package-card">
                    <div class="package-header">
                        <h3 class="package-name">Or</h3>
                        <div class="package-price">8 000 € <small>/événement</small></div>
                        <p>Visibilité élevée et impact fort</p>
                    </div>
                    <div class="package-body">
                        <ul class="package-features">
                            <li><i class="fas fa-check"></i> Logo sur supports digitaux et imprimés</li>
                            <li><i class="fas fa-check"></i> Stand standard (10m²) en zone stratégique</li>
                            <li><i class="fas fa-check"></i> Session de networking premium</li>
                            <li><i class="fas fa-check"></i> Mention dans les communiqués presse</li>
                            <li><i class="fas fa-check"></i> 5 invitations VIP</li>
                            <li><i class="fas fa-check"></i> Article sponsorisé sur notre blog</li>
                        </ul>
                        <div class="package-actions">
                            <a href="#request-form" class="btn btn-purple" data-price="8000" data-package="Or">Choisir cette offre</a>
                        </div>
                    </div>
                </div>
                
                <!-- Package 3 -->
                <div class="package-card">
                    <div class="package-header">
                        <h3 class="package-name">Argent</h3>
                        <div class="package-price">5 000 € <small>/événement</small></div>
                        <p>Visibilité ciblée et rentable</p>
                    </div>
                    <div class="package-body">
                        <ul class="package-features">
                            <li><i class="fas fa-check"></i> Logo sur supports imprimés</li>
                            <li><i class="fas fa-check"></i> Espace partagé en zone exposition</li>
                            <li><i class="fas fa-check"></i> Mention sur le site web et app mobile</li>
                            <li><i class="fas fa-check"></i> 2 invitations VIP</li>
                            <li><i class="fas fa-check"></i> Post sponsorisé sur les réseaux sociaux</li>
                        </ul>
                        <div class="package-actions">
                            <a href="#request-form" class="btn btn-purple" data-price="5000" data-package="Argent">Choisir cette offre</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <p>Vous souhaitez un package personnalisé adapté à vos besoins spécifiques ?</p>
                <a href="#request-form" class="btn">Demander une offre sur mesure</a>
            </div>
        </div>
        
        <div class="benefits-section">
            <div class="benefits-container">
                <h2 class="section-title" style="color: white;">Pourquoi Devenir Sponsor ?</h2>
                <p style="text-align: center; max-width: 800px; margin: 0 auto 30px; opacity: 0.9;">Découvrez les avantages exclusifs que nous offrons à nos partenaires sponsors pour maximiser leur retour sur investissement.</p>
                
                <div class="benefits-grid">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h3>Visibilité Exceptionnelle</h3>
                        <p>Exposition à plus de 50 000 visiteurs qualifiés et 500 000 impressions digitales</p>
                    </div>
                    
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-network-wired"></i>
                        </div>
                        <h3>Réseautage Premium</h3>
                        <p>Accès exclusif à nos événements VIP et rencontres avec les décideurs clés</p>
                    </div>
                    
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3>ROI Garanti</h3>
                        <p>Nos sponsors voient en moyenne une augmentation de 35% de leurs leads qualifiés</p>
                    </div>
                    
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-medal"></i>
                        </div>
                        <h3>Image Prestigieuse</h3>
                        <p>Association avec un événement d'élite reconnu dans toute l'Europe</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ROI Calculator -->
        <?php include 'sponsor-dashboard.php'; ?>
        
        <!-- Media Coverage -->
        <div class="media-section">
            <h2 class="section-title">Couverture Média de Nos Événements</h2>
            <p style="text-align: center; max-width: 800px; margin: 0 auto 30px;">Découvrez comment nos sponsors bénéficient d'une exposition médiatique exceptionnelle.</p>
            
            <div class="media-grid">
                <div class="media-card">
                    <div class="media-image" style="background-image: url('https://images.unsplash.com/photo-1492684223066-81342ee5ff30?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');"></div>
                    <div class="media-content">
                        <h3 class="media-title">Aroura event fait sensation</h3>
                        <div class="media-date">15 Mars 2023 - Le Monde</div>
                        <p class="media-excerpt">"L'édition 2023 a rassemblé l'élite internationale avec des partenaires prestigieux comme LVMH et Rolex..."</p>
                        <a href="#" class="media-link">Lire l'article →</a>
                    </div>
                </div>
                
                <div class="media-card">
                    <div class="media-image" style="background-image: url('https://images.unsplash.com/photo-1505373877841-8d25f7d46678?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');"></div>
                    <div class="media-content">
                        <h3 class="media-title">Les Sponsors à l'honneur</h3>
                        <div class="media-date">22 Février 2023 - Forbes</div>
                        <p class="media-excerpt">"Comment les partenariats avec Aroura event boostent les marques premium..."</p>
                        <a href="#" class="media-link">Lire l'article →</a>
                    </div>
                </div>
                
                <div class="media-card">
                    <div class="media-image" style="background-image: url('https://images.unsplash.com/photo-1511578314322-379afb476865?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');"></div>
                    <div class="media-content">
                        <h3 class="media-title">Retour sur l'Édition 2023</h3>
                        <div class="media-date">10 Janvier 2023 - Vogue Business</div>
                        <p class="media-excerpt">"Les chiffres records de cette année confirment le statut incontournable de cet événement..."</p>
                        <a href="#" class="media-link">Lire l'article →</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="testimonials-section">
            <h2 class="section-title">Ils Nous Ont Fait Confiance</h2>
            <p style="text-align: center; max-width: 800px; margin: 0 auto 30px;">Découvrez ce que nos sponsors actuels et passés disent de leur expérience.</p>
            
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>Notre partenariat avec Aroura event a dépassé toutes nos attentes. La qualité des contacts établis et la visibilité obtenue ont eu un impact direct sur notre chiffre d'affaires ce trimestre.</p>
                    </div>
                    <div class="testimonial-author">
                        <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Jean Martin">
                        <div class="author-info">
                            <h4>Jean Martin</h4>
                            <p>Directeur Marketing, Luxury Brand</p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>En tant que nouveau venu sur le marché, Aroura event nous a offert une crédibilité immédiate et des opportunités que nous n'aurions jamais pu obtenir autrement.</p>
                    </div>
                    <div class="testimonial-author">
                        <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Sophie Lambert">
                        <div class="author-info">
                            <h4>Sophie Lambert</h4>
                            <p>CEO, Tech Startup</p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>Nous sponsorisons depuis 5 ans et renouvelons chaque année. Le ROI est tangible et l'équipe est toujours à l'écoute pour créer des opportunités sur mesure.</p>
                    </div>
                    <div class="testimonial-author">
                        <img src="https://randomuser.me/api/portraits/men/67.jpg" alt="Pierre Dubois">
                        <div class="author-info">
                            <h4>Pierre Dubois</h4>
                            <p>Directeur Commercial, Banque Privée</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Dashboard Section -->
        <div class="dashboard-section" id="dashboard">
            <div class="dashboard-header">
                <h2 class="dashboard-title">Mon Espace Sponsor</h2>
                <div>
                    <span>Connecté en tant que: <strong>Luxury Brand</strong></span>
                </div>
            </div>
            
            <div class="dashboard-tabs">
                <button class="tab-button active" onclick="showTab('sponsorships')">
                    <i class="fas fa-handshake"></i> Mes Sponsorships
                </button>
                <button class="tab-button" onclick="showTab('messages')">
                    <i class="fas fa-envelope"></i> Messages
                </button>
                <button class="tab-button" onclick="showTab('stats')">
                    <i class="fas fa-chart-bar"></i> Statistiques
                </button>
            </div>
            
            <div class="tab-content active" id="sponsorships">
                <h3>Mes Sponsorships</h3>
                <div class="sponsorships-grid">
                    <?php
                    $sponsors = $sponsorController->getSponsors();
                    if (!empty($sponsors)) {
                        foreach ($sponsors as $sponsor) {
                            echo '<div class="sponsorship-card">';
                            echo '<div class="sponsorship-header">';
                            
                            // Add sponsor photo if available
                            if (!empty($sponsor['photo'])) {
                                echo '<div class="sponsor-photo-container">';
                                echo '<img src="../../' . htmlspecialchars($sponsor['photo']) . '" alt="' . htmlspecialchars($sponsor['entreprise']) . '" class="sponsor-photo">';
                                echo '</div>';
                            }
                            
                            echo '<h4>' . htmlspecialchars($sponsor['entreprise']) . '</h4>';
                            echo '</div>';
                            echo '<div class="sponsorship-details">';
                            echo '<p><i class="fas fa-envelope"></i><strong>Email:</strong> ' . htmlspecialchars($sponsor['mail']) . '</p>';
                            echo '<p><i class="fas fa-phone"></i><strong>Téléphone:</strong> ' . htmlspecialchars($sponsor['telephone']) . '</p>';
                            echo '</div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="no-sponsors-message">';
                        echo '<i class="fas fa-handshake"></i>';
                        echo 'Aucun sponsorship trouvé. Commencez à ajouter des sponsors pour les voir apparaître ici.';
                        echo '</div>';
                    }
                    ?>
                </div>
                <div style="text-align: center; margin-top: 50px;">
                    <a href="add_sponsor_front.php" class="btn btn-purple"><i class="fas fa-plus"></i> vous pouvez devenir un sponsor?</a>
                </div>
            </div>
            
            <div class="tab-content" id="messages">
                <h3><i class="fas fa-envelope"></i> Messagerie Sponsors</h3>
                <div class="messages-container">
                    <?php 
                    require_once __DIR__ . '/../../Controller/MessengerController.php';
                    $messengerController = new MessengerController();
                    
                    // Static user ID as requested
                    $user_id = 456;
                    
                    // Get all conversations for the user
                    $conversations = $messengerController->getConversationsForUser($user_id);
                    
                    // Get unread count
                    $unreadCount = $messengerController->getUnreadCount($user_id);
                    ?>
                    
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <div class="card shadow-sm border-0">
                                <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center py-3">
                                    <h5 class="mb-0 font-weight-bold"><i class="fas fa-comment-dots mr-2"></i> Vos messages</h5>
                                    <a href="/chedliss/view/front/messenger.php" class="btn btn-light btn-sm rounded-pill px-3 d-flex align-items-center">
                                        <i class="fas fa-comment-dots mr-2"></i> Messagerie complète
                                        <?php if ($unreadCount > 0): ?>
                                            <span class="badge badge-danger ml-2 rounded-circle"><?php echo $unreadCount; ?></span>
                                        <?php endif; ?>
                                    </a>
                                </div>
                                <div class="card-body p-0">
                                    <?php if (empty($conversations)): ?>
                                        <div class="text-center py-5">
                                            <div class="mb-4">
                                                <i class="fas fa-comments fa-4x text-muted opacity-50"></i>
                                            </div>
                                            <h5 class="text-muted">Aucune conversation récente</h5>
                                            <p class="text-muted small mb-4">Commencez à communiquer avec vos sponsors</p>
                                            <a href="/chedliss/view/front/messenger.php" class="btn btn-primary px-4 rounded-pill">
                                                <i class="fas fa-plus-circle mr-2"></i> Démarrer une nouvelle conversation
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <div class="message-list">
                                            <?php foreach (array_slice($conversations, 0, 3) as $conversation): ?>
                                                <a href="/chedliss/view/front/messenger.php?sponsor=<?php echo $conversation['id_sponsor']; ?>" class="message-item p-3 d-flex align-items-center border-bottom transition">
                                                    <div class="avatar-wrapper mr-3">
                                                        <div class="avatar-circle bg-primary text-white d-flex align-items-center justify-content-center">
                                                            <i class="fas fa-building"></i>
                                                        </div>
                                                    </div>
                                                    <div class="message-content flex-grow-1">
                                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                                            <h6 class="mb-0 font-weight-bold"><?php echo htmlspecialchars($conversation['nom_sponsor']); ?></h6>
                                                            <small class="text-muted">
                                                                <?php 
                                                                $date = new DateTime($conversation['last_message_date']);
                                                                echo $date->format('d/m/Y'); 
                                                                ?>
                                                            </small>
                                                        </div>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <p class="mb-0 text-muted small text-truncate"><?php echo htmlspecialchars($conversation['entreprise']); ?></p>
                                                            <?php if ($conversation['unread_count'] > 0): ?>
                                                                <span class="badge badge-primary badge-pill ml-2">
                                                                    <?php echo $conversation['unread_count']; ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php if (count($conversations) > 3): ?>
                                            <div class="text-center py-3 border-top">
                                                <a href="/chedliss/view/front/messenger.php" class="text-primary font-weight-bold">
                                                    <i class="fas fa-chevron-right mr-1"></i> Voir toutes les conversations
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="tab-content" id="stats">
                <h3><i class="fas fa-chart-bar"></i> Statistiques de Performance</h3>
                <div class="stats-grid">
                    <div class="stat-card">
                        <i class="fas fa-eye"></i>
                        <h4>Vues du Profil</h4>
                        <div class="value">1,234</div>
                        <p class="trend positive"><i class="fas fa-arrow-up"></i> +12% ce mois</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-handshake"></i>
                        <h4>Sponsorships Actifs</h4>
                        <div class="value">3</div>
                        <p class="trend">En cours</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-chart-line"></i>
                        <h4>Taux d'Engagement</h4>
                        <div class="value">8.5%</div>
                        <p class="trend positive"><i class="fas fa-arrow-up"></i> +2.3%</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-calendar-check"></i>
                        <h4>Événements Sponsorisés</h4>
                        <div class="value">5</div>
                        <p class="trend">Cette année</p>
                    </div>
                </div>

                <div class="stats-chart-container" style="margin-top: 40px;">
                    <h4><i class="fas fa-chart-area"></i> Évolution des Interactions</h4>
                    <div class="empty-state">
                        <i class="fas fa-chart-bar"></i>
                        <h3>Statistiques en cours de collecte</h3>
                        <p>Les données détaillées seront disponibles prochainement.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="request-section" id="request-form">
            <h2 class="section-title">Demande de Sponsoring</h2>
            <p style="text-align: center; max-width: 800px; margin: 0 auto 30px;">Remplissez ce formulaire pour devenir sponsor et notre équipe vous contactera dans les plus brefs délais pour finaliser votre partenariat.</p>
            
            <div class="form-container">
            <form method="POST" id="demandeForm">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger" style="color: red; margin-bottom: 15px;">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <label>ID Sponsor</label>
                    <input type="text" name="id_sponsor" id="id_sponsor" class="form-control">
                </div>
                <div class="form-group">
                    <label>ID Organisateur</label>
                    <input type="text" name="id_organisateur" id="id_organisateur" class="form-control">
                </div>
                <div class="form-group">
                    <label>Montant</label>
                    <input type="text" name="montant" id="montant" class="form-control">
                </div>
                <div class="form-group">
                    <label>ID Événement</label>
                    <input type="text" name="idevent" id="idevent" class="form-control">
                </div>

                <div class="form-buttons">
                    <button type="submit" class="btn btn-edit">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                    <a href="front.php" class="btn btn-delete">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
        </div>
    </div>
    <script>
    // Fonction pour valider un champ
    function validateField(field, rules) {
        const value = field.value.trim();
        let error = null;

        // Vérification si le champ est requis
        if (rules.required && !value) {
            error = rules.requiredMessage || "Ce champ est requis";
        } 
        // Vérification de format avec regex
        else if (value && rules.pattern && !rules.pattern.test(value)) {
            error = rules.patternMessage || "Format invalide";
        }
        // Vérification de valeur minimale pour les nombres
        else if (value && rules.min !== undefined && parseFloat(value) < rules.min) {
            error = rules.minMessage || `La valeur doit être au moins ${rules.min}`;
        }

        // Récupérer ou créer le conteneur d'erreur
        let errorContainer = field.nextElementSibling;
        if (!errorContainer || !errorContainer.classList.contains('validation-error')) {
            errorContainer = document.createElement('div');
            errorContainer.className = 'validation-error';
            errorContainer.style.color = '#dc3545';
            errorContainer.style.fontSize = '0.85rem';
            errorContainer.style.marginTop = '5px';
            errorContainer.style.transition = 'all 0.3s ease';
            field.parentNode.insertBefore(errorContainer, field.nextSibling);
        }

        // Ajouter ou supprimer le message d'erreur
        if (error) {
            errorContainer.textContent = error;
            errorContainer.style.opacity = '1';
            errorContainer.style.height = 'auto';
            field.style.borderColor = '#dc3545';
            field.style.backgroundColor = '#fff8f8';
            return false;
        } else {
            errorContainer.style.opacity = '0';
            errorContainer.style.height = '0';
            field.style.borderColor = '#28a745';
            field.style.backgroundColor = '#f8fff8';
            return true;
        }
    }

    // Définir les règles de validation pour chaque champ
    const validationRules = {
        id_sponsor: {
            required: true,
            requiredMessage: "L'ID du sponsor est requis",
            pattern: /^[0-9]+$/,
            patternMessage: "L'ID du sponsor doit être un nombre entier valide"
        },
        id_organisateur: {
            required: true,
            requiredMessage: "L'ID de l'organisateur est requis",
            pattern: /^[0-9]+$/,
            patternMessage: "L'ID de l'organisateur doit être un nombre entier valide"
        },
        montant: {
            required: true,
            requiredMessage: "Le montant est requis",
            min: 0.01,
            minMessage: "Le montant doit être supérieur à 0"
        },
        idevent: {
            required: true,
            requiredMessage: "L'ID de l'événement est requis",
            pattern: /^[0-9]+$/,
            patternMessage: "L'ID de l'événement doit être un nombre entier valide"
        }
    };

    // Ajouter une classe CSS pour le style des champs du formulaire
    const style = document.createElement('style');
    style.textContent = `
        .form-control.validated {
            transition: all 0.3s ease;
        }
        .form-group {
            position: relative;
        }
        .validation-error {
            overflow: hidden;
        }
        .form-control:focus {
            box-shadow: none;
            outline: none;
        }
    `;
    document.head.appendChild(style);

    // Initialiser la validation en temps réel
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('demandeForm');
        const fields = form.querySelectorAll('input');
        
        // Configurer la validation en temps réel pour chaque champ
        fields.forEach(field => {
            const rules = validationRules[field.id];
            if (rules) {
                field.classList.add('validated');
                
                // Validation à la perte de focus (meilleure expérience utilisateur)
                field.addEventListener('blur', function() {
                    validateField(field, rules);
                });
                
                // Validation pendant la saisie après le premier blur
                field.addEventListener('input', function() {
                    if (field.dataset.touched === 'true') {
                        validateField(field, rules);
                    }
                });
                
                // Marquer le champ comme touché au blur
                field.addEventListener('blur', function() {
                    field.dataset.touched = 'true';
                });
            }
        });
        
        // Validation du formulaire à la soumission
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            let formIsValid = true;
            
            // Valider tous les champs
            fields.forEach(field => {
                const rules = validationRules[field.id];
                if (rules) {
                    const isFieldValid = validateField(field, rules);
                    formIsValid = formIsValid && isFieldValid;
                }
            });
            
            // Soumettre le formulaire si tout est valide
            if (formIsValid) {
                this.submit();
            } else {
                // Faire défiler jusqu'au premier champ invalide
                const firstInvalidField = form.querySelector('input[style*="border-color: rgb(220, 53, 69)"]');
                if (firstInvalidField) {
                    firstInvalidField.focus();
                    firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    });
    </script>

    <script>
        const form = document.getElementById('sponsorshipRequestForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                alert('Votre demande de sponsoring a été envoyée avec succès! Nous vous contacterons dans les plus brefs délais.');
                form.reset();
                
                // Scroll to dashboard
                document.querySelector('#dashboard').scrollIntoView({
                    behavior: 'smooth'
                });
                
                // Update the requests tab
                const requestsTab = document.querySelector('[data-tab="my-requests"]');
                if (requestsTab) {
                    requestsTab.click();
                }
            });
        }

        // Smooth scroll for package links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // ROI Calculator
        const investmentSlider = document.getElementById('investment');
        const investmentValue = document.getElementById('investmentValue');
        
        investmentSlider.addEventListener('input', function() {
            investmentValue.textContent = new Intl.NumberFormat('fr-FR').format(this.value) + ' €';
            
            // Update other calculated values (simplified for demo)
            // In a real app, this would be more sophisticated calculations
        });
        
        // Tab functionality
        const tabs = document.querySelectorAll('.tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs and contents
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked tab and corresponding content
                this.classList.add('active');
                const tabId = this.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });
        
        // Conversation selection
        const conversationItems = document.querySelectorAll('.conversation-item');
        conversationItems.forEach(item => {
            item.addEventListener('click', function() {
                conversationItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');
                
                // In a real app, this would load the conversation messages
            });
        });
        
        // Investment slider initial value
        investmentValue.textContent = new Intl.NumberFormat('fr-FR').format(investmentSlider.value) + ' €';
    </script>
    <script>
    function confirmDelete(id) {
        // Show the modal
        const modal = document.getElementById('confirmationModal');
        modal.style.display = 'flex';
        
        // Set up the event listeners
        document.getElementById('cancelDelete').onclick = function() {
            modal.style.display = 'none';
        };
        
        document.getElementById('confirmDelete').onclick = function() {
            // Create the form for submission
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';

            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete';

            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = id;

            form.appendChild(actionInput);
            form.appendChild(idInput);
            document.body.appendChild(form);
            form.submit();
        };
        
        // Close when clicking outside the modal
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        };
    }
    </script>

    <script>
        // Add this code to handle auto-filling the form when package is selected
        document.addEventListener('DOMContentLoaded', function() {
            const packageButtons = document.querySelectorAll('.btn[data-price]');
            
            packageButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    const price = this.getAttribute('data-price');
                    const packageName = this.getAttribute('data-package');
                    
                    // Set the amount in the form
                    document.getElementById('montant').value = price;
                    
                    // Scroll to the form
                    document.querySelector('#request-form').scrollIntoView({
                        behavior: 'smooth'
                    });
                    
                    // Optional: Add a visual indication of selected package
                    const formTitle = document.querySelector('#request-form .section-title');
                    if (formTitle) {
                        formTitle.innerHTML = `Demande de Sponsoring - Package ${packageName}`;
                    }
                });
            });
        });
    </script>

    <script src="js/dashboard.js"></script>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Festival Sponsoring. Tous droits réservés.</p>
        </div>
    </footer>
    
    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Confirmation de suppression</h3>
            </div>
            <div class="modal-body">
                Êtes-vous sûr de vouloir supprimer ce sponsor ? Cette action est irréversible.
            </div>
            <div class="modal-footer">
                <button id="cancelDelete" class="modal-btn modal-btn-cancel">Annuler</button>
                <button id="confirmDelete" class="modal-btn modal-btn-delete">Supprimer</button>
            </div>
        </div>
    </div>
</body>
</html>