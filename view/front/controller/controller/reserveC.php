<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/reserve.php';

class ReservationC {
    // Add a reservation
    public function ajouterReservation(Reservation $reservation) {
        $sql = "INSERT INTO reservation (id_event, id_user, nombre_places, categorie, mode_paiement)
                VALUES (:id_event, :id_user, :nombre_places, :categorie, :mode_paiement)";
        $db = getDB();
        try {
            $query = $db->prepare($sql);
            $params = [
                'id_event'       => $reservation->getIdEvent(),
                'id_user'        => $reservation->getIdUser(),
                'nombre_places'  => $reservation->getNombrePlaces(),
                'categorie'      => $reservation->getCategorie(),
                'mode_paiement'  => $reservation->getModePaiement()
            ];
            $query->execute($params);
            return true;
        } catch (Exception $e) {
            error_log("Error adding reservation: " . $e->getMessage() . " | SQL: $sql | Params: " . json_encode($params));
            return false;
        }
    }

    // Fetch all reservations with user and event details (sorted DESC)
    public function afficherReservations() {
        $sql = "SELECT r.*, u.nom, u.prenom, u.telephone, e.prix, e.titre AS event_title, e.date, e.lieu, e.artiste, e.description, (r.nombre_places * e.prix) AS total
                FROM reservation r 
                JOIN users u ON r.id_user = u.id_user
                JOIN evenement e ON r.id_event = e.id_event
                ORDER BY r.id_reservation DESC";
        $db = getDB();
        try {
            return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['error'] = "Error fetching reservations: " . $e->getMessage();
            error_log("Error fetching reservations: " . $e->getMessage() . " | SQL: $sql");
            return [];
        }
    }

    // Fetch a reservation by id_reservation
    public function getReservationById($id_reservation) {
        $sql = "SELECT r.*, u.nom, u.prenom, u.telephone, e.prix, e.titre AS event_title, e.date, e.lieu, e.artiste, e.description, (r.nombre_places * e.prix) AS total
                FROM reservation r 
                JOIN users u ON r.id_user = u.id_user 
                JOIN evenement e ON r.id_event = e.id_event 
                WHERE r.id_reservation = :id_reservation";
        $db = getDB();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id_reservation' => $id_reservation]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result ? [$result] : [];
        } catch (Exception $e) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['error'] = "Error fetching reservation: " . $e->getMessage();
            error_log("Error fetching reservation: " . $e->getMessage() . " | SQL: $sql | id_reservation: $id_reservation");
            return [];
        }
    }

    // Fetch reservations by id_user
    public function getReservationsByUserId($id_user) {
        $sql = "SELECT r.*, u.nom, u.prenom, u.telephone, e.prix, e.titre AS event_title, e.date, e.lieu, e.artiste, e.description, (r.nombre_places * e.prix) AS total
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
            $_SESSION['error'] = "Error fetching user reservations: " . $e->getMessage();
            error_log("Error fetching user reservations: " . $e->getMessage() . " | SQL: $sql | id_user: $id_user");
            return [];
        }
    }

    // Fetch reservations by id_event with joins on evenement and users (sorted DESC)
    public function afficherReservationsParEvenement($idEvent) {
        $sql = "SELECT r.*, e.titre, e.date, e.prix, e.lieu, e.artiste, e.description, (r.nombre_places * e.prix) AS total, u.nom, u.prenom, u.telephone 
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
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['error'] = "Error fetching event reservations: " . $e->getMessage();
            error_log("Error fetching event reservations: " . $e->getMessage() . " | SQL: $sql | idEvent: $idEvent");
            return [];
        }
    }

    // Delete a reservation
    public function supprimerReservation($idReservation) {
        $sql = "DELETE FROM reservation WHERE id_reservation = :id_reservation";
        $db = getDB();
        try {
            $query = $db->prepare($sql);
            $query->bindValue(':id_reservation', $idReservation, PDO::PARAM_INT);
            $query->execute();
            return true;
        } catch (Exception $e) {
            error_log("Error deleting reservation: " . $e->getMessage() . " | SQL: $sql | id_reservation: $idReservation");
            return false;
        }
    }

    // Update a reservation
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
            $params = [
                'id_event'         => $reservation->getIdEvent(),
                'id_user'          => $reservation->getIdUser(),
                'nombre_places'    => $reservation->getNombrePlaces(),
                'categorie'        => $reservation->getCategorie(),
                'mode_paiement'    => $reservation->getModePaiement(),
                'id_reservation'   => $reservation->getIdReservation()
            ];
            $query->execute($params);
            return true;
        } catch (Exception $e) {
            error_log("Error updating reservation: " . $e->getMessage() . " | SQL: $sql | Params: " . json_encode($params));
            return false;
        }
    }
}
?>