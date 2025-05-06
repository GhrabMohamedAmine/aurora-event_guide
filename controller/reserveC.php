<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/reserve.php';

class ReservationC {
    // Ajouter une réservation
    public function ajouterReservation(Reservation $reservation) {
        $sql = "INSERT INTO reservation (id_event, id_user, nombre_places, categorie, mode_paiement)
                VALUES (:id_event, :id_user, :nombre_places, :categorie, :mode_paiement)";
        $db = getDB();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_event'       => $reservation->getIdEvent(),
                'id_user'        => $reservation->getIdUser(),
                'nombre_places'  => $reservation->getNombrePlaces(),
                'categorie'      => $reservation->getCategorie(),
                'mode_paiement'  => $reservation->getModePaiement()
            ]);
            return true;
        } catch (Exception $e) {
            error_log("Error adding reservation: " . $e->getMessage());
            return false;
        }
    }

    // Récupérer toutes les réservations avec les détails de l'utilisateur et le total (tri DESC)
    public function afficherReservations() {
        $sql = "SELECT r.*, u.nom, u.prenom, u.telephone, e.prix, e.titre AS event_title, (r.nombre_places * e.prix) AS total
                FROM reservation r 
                JOIN users u ON r.id_user = u.id_user
                JOIN evenement e ON r.id_event = e.id_event
                ORDER BY r.id_reservation DESC";
        $db = getDB();
        try {
            return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // Récupérer une réservation par id_reservation
    public function getReservationById($id_reservation) {
        $sql = "SELECT r.*, u.nom, u.prenom, u.telephone, e.prix, e.titre AS event_title, (r.nombre_places * e.prix) AS total
                FROM reservation r 
                JOIN users u ON r.id_user = u.id_user 
                JOIN evenement e ON r.id_event = e.id_event 
                WHERE r.id_reservation = :id_reservation";
        $db = getDB();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id_reservation' => $id_reservation]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            // Return an array containing the result (or an empty array if no result)
            return $result ? [$result] : [];
        } catch (Exception $e) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['error'] = "Erreur lors de la récupération de la réservation : " . $e->getMessage();
            return [];
        }
    }

    // Récupérer les réservations par id_user
    public function getReservationsByUserId($id_user) {
        $sql = "SELECT r.*, u.nom, u.prenom, u.telephone, e.prix, e.titre AS event_title, (r.nombre_places * e.prix) AS total
                FROM reservation r 
                JOIN users u ON r.id_user = u.id_user 
                JOIN evenement e ON r.id_event = e.id_event 
                WHERE r.id_user = :id_user
                ORDER BY r.id_reservation DESC";
        $db = getDB();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id_user' => $id_user]);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['error'] = "Erreur lors de la récupération des réservations : " . $e->getMessage();
            return [];
        }
    }

    // Récupérer les réservations par id_event avec jointure sur evenement et users (tri DESC)
    public function afficherReservationsParEvenement($idEvent) {
        $sql = "SELECT r.*, e.titre, e.date, e.prix, (r.nombre_places * e.prix) AS total, u.nom, u.prenom, u.telephone 
                FROM reservation r 
                INNER JOIN evenement e ON r.id_event = e.id_event
                INNER JOIN users u ON r.id_user = u.id_user
                WHERE r.id_event = :idEvent
                ORDER BY r.id_reservation DESC";
        $db = getDB();
        try {
            $query = $db->prepare($sql);
            $query->bindValue(':idEvent', $idEvent, PDO::PARAM_INT);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // Supprimer une réservation
    public function supprimerReservation($idReservation) {
        $sql = "DELETE FROM reservation WHERE id_reservation = :id_reservation";
        $db = getDB();
        try {
            $query = $db->prepare($sql);
            $query->bindValue(':id_reservation', $idReservation, PDO::PARAM_INT);
            $query->execute();
            return true;
        } catch (Exception $e) {
            error_log("Error deleting reservation: " . $e->getMessage());
            return false;
        }
    }

    // Modifier une réservation
    public function modifierReservation(Reservation $reservation) {
        $sql = "UPDATE reservation SET 
                    id_event = :id_event,
                    id_user = :id_user,
                    nombre_places = :nombre_places,
                    categorie = :categorie,
                    mode_paiement = :mode_paiement
                WHERE id_reservation = :id_reservation";
        $db = getDB();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_event'         => $reservation->getIdEvent(),
                'id_user'          => $reservation->getIdUser(),
                'nombre_places'    => $reservation->getNombrePlaces(),
                'categorie'        => $reservation->getCategorie(),
                'mode_paiement'    => $reservation->getModePaiement(),
                'id_reservation'   => $reservation->getIdReservation()
            ]);
            return true;
        } catch (Exception $e) {
            error_log("Error updating reservation: " . $e->getMessage());
            return false;
        }
    }
}
?>