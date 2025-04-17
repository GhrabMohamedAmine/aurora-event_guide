<?php
require_once __DIR__.'/../../config/db.php';
require_once __DIR__.'/../../controller/DemandeSponsoringController.php';

$controller = new DemandeSponsoringController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $result = $controller->add(
            $_POST['id_sponsor'],
            $_POST['id_organisateur'],
            $_POST['montant'],
            $_POST['idevent']
        );
        
        if ($result) {
            header('Location: front.php');
            exit;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
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
            background-color: var(--secondary-color);
            color: var(--dark-purple);
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
            font-size: 1rem;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn:hover {
            background-color: #c19c00;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .btn-purple {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-purple:hover {
            background-color: var(--dark-purple);
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
        
        .tabs {
            display: flex;
            border-bottom: 1px solid #eee;
            margin-bottom: 30px;
        }
        
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .tab.active {
            border-bottom-color: var(--primary-color);
            color: var(--primary-color);
            font-weight: bold;
        }
        
        .tab:hover:not(.active) {
            border-bottom-color: var(--light-purple);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .sponsors-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sponsor-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
            transition: all 0.3s;
        }
        
        .sponsor-item:hover {
            background: var(--light-gray);
        }
        
        .sponsor-logo {
            width: 60px;
            height: 60px;
            object-fit: contain;
            margin-right: 20px;
            background: white;
            padding: 5px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .sponsor-info {
            flex: 1;
        }
        
        .sponsor-name {
            margin: 0 0 5px;
            color: var(--primary-color);
        }
        
        .sponsor-package {
            font-size: 0.9rem;
            color: var(--dark-gray);
        }
        
        .sponsor-actions {
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--dark-gray);
            transition: all 0.3s;
            padding: 5px;
        }
        
        .action-btn.edit:hover {
            color: var(--primary-color);
        }
        
        .action-btn.delete:hover {
            color: #e74c3c;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px;
            color: var(--dark-gray);
        }
        
        .empty-state i {
            font-size: 3rem;
            color: var(--light-purple);
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
                            <a href="#request-form" class="btn btn-purple">Choisir cette offre</a>
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
                            <a href="#request-form" class="btn btn-purple">Choisir cette offre</a>
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
                            <a href="#request-form" class="btn btn-purple">Choisir cette offre</a>
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
        <div class="calculator-section">
            <h2 class="section-title">Calculateur de ROI</h2>
            <p style="text-align: center; max-width: 800px; margin: 0 auto 30px;">Estimez le retour sur investissement que vous pourriez obtenir en devenant sponsor de notre événement.</p>
            
            <div class="calculator-grid">
                <div>
                    <div class="form-group">
                        <label for="investment">Montant investi (€)</label>
                        <input type="range" id="investment" min="5000" max="30000" step="1000" value="15000" class="form-control">
                        <div style="text-align: center; margin-top: 5px;" id="investmentValue">15 000 €</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="sector">Secteur d'activité</label>
                        <select id="sector" class="form-control">
                            <option value="luxe">Luxe & Premium</option>
                            <option value="tech">Technologie</option>
                            <option value="finance">Finance & Banque</option>
                            <option value="automobile">Automobile</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="objective">Objectif principal</label>
                        <select id="objective" class="form-control">
                            <option value="visibilite">Augmenter la visibilité</option>
                            <option value="leads">Générer des leads</option>
                            <option value="image">Améliorer l'image de marque</option>
                            <option value="reseau">Développer son réseau</option>
                        </select>
                    </div>
                </div>
                
                <div class="calculator-results">
                    <div class="result-item">
                        <div class="result-label">Contacts Qualifiés Estimés</div>
                        <div class="result-value">120-180</div>
                    </div>
                    
                    <div class="result-item">
                        <div class="result-label">Couverture Média Équivalente</div>
                        <div class="result-value">45 000 €</div>
                    </div>
                    
                    <div class="result-item">
                        <div class="result-label">ROI Estimé (12 mois)</div>
                        <div class="result-value positive">+215%</div>
                    </div>
                    
                    <button class="btn" style="align-self: center; margin-top: 20px;">Obtenir une Analyse Complète</button>
                </div>
            </div>
        </div>
        
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
            
            <div class="tabs">
                <div class="tab active" data-tab="my-sponsorships">Mes Sponsorships</div>
                <div class="tab" data-tab="my-requests">Mes Demandes</div>
                <div class="tab" data-tab="messages">Messagerie</div>
                <div class="tab" data-tab="stats">Statistiques</div>
            </div>
            
            <div class="tab-content active" id="my-sponsorships">
                <h3>Mes Sponsors Actifs</h3>
                <ul class="sponsors-list">
                    <li class="sponsor-item">
                        <img src="https://via.placeholder.com/60?text=LB" alt="Luxury Brand" class="sponsor-logo">
                        <div class="sponsor-info">
                            <h4 class="sponsor-name">Luxury Brand</h4>
                            <p class="sponsor-package">Package Platine - Édition 2023</p>
                        </div>
                        <div class="sponsor-actions">
                            <button class="action-btn edit" title="Modifier"><i class="fas fa-edit"></i></button>
                            <button class="action-btn delete" title="Supprimer"><i class="fas fa-trash"></i></button>
                        </div>
                    </li>
                    <li class="sponsor-item">
                        <img src="https://via.placeholder.com/60?text=TC" alt="Tech Corp" class="sponsor-logo">
                        <div class="sponsor-info">
                            <h4 class="sponsor-name">Tech Corp</h4>
                            <p class="sponsor-package">Package Or - Édition 2022</p>
                        </div>
                        <div class="sponsor-actions">
                            <button class="action-btn edit" title="Modifier"><i class="fas fa-edit"></i></button>
                            <button class="action-btn delete" title="Supprimer"><i class="fas fa-trash"></i></button>
                        </div>
                    </li>
                </ul>
                
                <div style="text-align: center; margin-top: 30px;">
                    <button class="btn btn-purple"><i class="fas fa-plus"></i> Ajouter un Nouveau Sponsor</button>
                </div>
            </div>
            
            <div class="tab-content" id="my-requests">
                <h3>Mes Demandes en Cours</h3>
                <ul class="sponsors-list">
                    <li class="sponsor-item">
                        <div class="sponsor-info">
                            <h4 class="sponsor-name">Demande #ER2024-056</h4>
                            <p class="sponsor-package">Package Platine - En attente de validation</p>
                            <p><small>Soumis le 15/04/2023</small></p>
                        </div>
                        <div class="sponsor-actions">
                            <button class="action-btn edit" title="Voir"><i class="fas fa-eye"></i></button>
                        </div>
                    </li>
                    <li class="sponsor-item">
                        <div class="sponsor-info">
                            <h4 class="sponsor-name">Demande #ER2023-128</h4>
                            <p class="sponsor-package">Package Personnalisé - Acceptée</p>
                            <p><small>Soumis le 22/11/2022</small></p>
                        </div>
                        <div class="sponsor-actions">
                            <button class="action-btn edit" title="Voir"><i class="fas fa-eye"></i></button>
                        </div>
                    </li>
                </ul>
                
                <div class="empty-state">
                    <i class="fas fa-paper-plane"></i>
                    <h3>Prêt à devenir sponsor ?</h3>
                    <p>Envoyez une nouvelle demande de sponsoring et rejoignez nos partenaires prestigieux.</p>
                    <a href="#request-form" class="btn btn-purple">Nouvelle Demande</a>
                </div>
            </div>
            
            <div class="tab-content" id="messages">
                <h3>Messagerie Sponsors</h3>
                
                <div class="messages-container">
                    <div class="conversations-list">
                        <div class="conversation-header">Conversations</div>
                        <div class="conversation-item active">
                            <h4 class="conversation-name">Équipe Événement Royal</h4>
                            <p class="conversation-preview">Bonjour, nous avons bien reçu votre demande...</p>
                        </div>
                        <div class="conversation-item">
                            <h4 class="conversation-name">Service Marketing</h4>
                            <p class="conversation-preview">Votre logo a été ajouté à nos supports...</p>
                        </div>
                        <div class="conversation-item">
                            <h4 class="conversation-name">Service Partenariats</h4>
                            <p class="conversation-preview">Nous préparons votre espace pour l'événement...</p>
                        </div>
                    </div>
                    
                    <div class="message-area">
                        <div class="message-header">
                            <h4>Équipe Événement Royal</h4>
                            <button class="btn" style="padding: 5px 10px; font-size: 0.8rem;">
                                <i class="fas fa-phone"></i> Appeler
                            </button>
                        </div>
                        
                        <div class="message-list">
                            <div class="message received">
                                <div class="message-content">
                                    Bonjour, nous avons bien reçu votre demande de sponsoring pour l'édition 2023. Nous sommes ravis de votre intérêt !
                                </div>
                                <div class="message-time">15/04/2023 10:23</div>
                            </div>
                            
                            <div class="message sent">
                                <div class="message-content">
                                    Merci pour votre réponse rapide. Pourriez-vous nous indiquer les prochaines étapes ?
                                </div>
                                <div class="message-time">15/04/2023 11:45</div>
                            </div>
                            
                            <div class="message received">
                                <div class="message-content">
                                    Bien sûr. Notre responsable partenariats vous contactera dans les 48h pour discuter des modalités et répondre à vos questions.
                                </div>
                                <div class="message-time">15/04/2023 14:12</div>
                            </div>
                        </div>
                        
                        <div class="message-input-area">
                            <textarea class="message-input" placeholder="Écrivez votre message..."></textarea>
                            <button class="send-btn"><i class="fas fa-paper-plane"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="tab-content" id="stats">
                <h3>Statistiques de Performance</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 30px;">
                    <div style="background: var(--light-gray); padding: 20px; border-radius: 10px;">
                        <h4 style="color: var(--primary-color); margin-top: 0;">Visibilité</h4>
                        <div style="height: 200px; background: white; display: flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                            [Graphique des impressions]
                        </div>
                        <p><strong>1.2M</strong> impressions totales</p>
                        <p><strong>45%</strong> au-dessus de la moyenne sectorielle</p>
                    </div>
                    
                    <div style="background: var(--light-gray); padding: 20px; border-radius: 10px;">
                        <h4 style="color: var(--primary-color); margin-top: 0;">Engagement</h4>
                        <div style="height: 200px; background: white; display: flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                            [Graphique des interactions]
                        </div>
                        <p><strong>3,450</strong> leads générés</p>
                        <p><strong>28%</strong> de taux de conversion</p>
                    </div>
                </div>
                
                <div style="background: var(--light-gray); padding: 20px; border-radius: 10px; margin-top: 30px;">
                    <h4 style="color: var(--primary-color); margin-top: 0;">ROI Estimé</h4>
                    <div style="display: flex; align-items: center; gap: 30px;">
                        <div style="flex: 1; height: 150px; background: white; display: flex; align-items: center; justify-content: center;">
                            [Graphique ROI]
                        </div>
                        <div style="flex: 1;">
                            <p><strong>215%</strong> de retour sur investissement</p>
                            <p><strong>6.2 mois</strong> pour atteindre le seuil de rentabilité</p>
                            <p><strong>45 000 €</strong> de valeur médiatique équivalente</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="request-section" id="request-form">
            <h2 class="section-title">Demande de Sponsoring</h2>
            <p style="text-align: center; max-width: 800px; margin: 0 auto 30px;">Remplissez ce formulaire pour devenir sponsor et notre équipe vous contactera dans les plus brefs délais pour finaliser votre partenariat.</p>
            
            <div class="form-container">
            <form method="POST" id="demandeForm" onsubmit="return validateForm(event)">
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
    function validateForm(event) {
        event.preventDefault();
        let errors = [];
        
        const id_sponsor = document.getElementById('id_sponsor').value.trim();
        const id_organisateur = document.getElementById('id_organisateur').value.trim();
        const montant = document.getElementById('montant').value.trim();
        const idevent = document.getElementById('idevent').value.trim();
        
        if (!id_sponsor) errors.push("L'ID du sponsor est requis");
        if (!id_organisateur) errors.push("L'ID de l'organisateur est requis");
        if (!montant) errors.push("Le montant est requis");
        if (!idevent) errors.push("L'ID de l'événement est requis");
        
        if (isNaN(montant)) {
            errors.push("Le montant doit être un nombre");
            return false;
        }
        
        if (errors.length > 0) {
            alert(errors.join('\n'));
            return false;
        }
        
        return true;
    }
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
</body>
</html>