<?php
// Script d'installation de PHPMailer

// Créer le répertoire vendor/phpmailer s'il n'existe pas
$vendorDir = __DIR__ . '/vendor';
$phpmailerDir = $vendorDir . '/phpmailer/phpmailer/src';

if (!file_exists($vendorDir)) {
    mkdir($vendorDir, 0777, true);
    echo "Répertoire vendor créé.\n";
}

if (!file_exists($phpmailerDir)) {
    mkdir($phpmailerDir, 0777, true);
    echo "Répertoire pour PHPMailer créé.\n";
}

// URLs des fichiers PHPMailer à télécharger
$files = [
    'Exception.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/Exception.php',
    'PHPMailer.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/PHPMailer.php',
    'SMTP.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/SMTP.php'
];

// Télécharger les fichiers
foreach ($files as $filename => $url) {
    $fileContent = file_get_contents($url);
    
    if ($fileContent === false) {
        echo "Erreur lors du téléchargement de {$filename}.\n";
        continue;
    }
    
    $filePath = $phpmailerDir . '/' . $filename;
    $result = file_put_contents($filePath, $fileContent);
    
    if ($result === false) {
        echo "Erreur lors de l'écriture du fichier {$filename}.\n";
    } else {
        echo "{$filename} a été téléchargé avec succès.\n";
    }
}

echo "\nInstallation terminée. PHPMailer a été configuré avec succès.\n";
echo "Vous pouvez maintenant utiliser la fonctionnalité de réinitialisation de mot de passe.\n";
?> 