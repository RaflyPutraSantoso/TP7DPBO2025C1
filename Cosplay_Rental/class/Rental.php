<?php
require_once 'config/db.php';
require_once 'class/Costume.php';
require_once 'class/Costumer.php';

class Rental {
    private $db;

    public function __construct() {
        $this->db = (new Database())->conn;
    }

    public function getAllRentals($search = '') {
        $sql = "SELECT r.*, 
                c.name AS costume_name, c.image_path AS costume_image,
                c.price AS costume_price,
                cu.name AS customer_name, cu.email AS customer_email
                FROM rentals r
                JOIN costumes c ON r.costume_id = c.id
                JOIN customers cu ON r.customer_id = cu.id";
        
        $params = [];
        if (!empty($search)) {
            $sql .= " WHERE c.name LIKE :search OR cu.name LIKE :search";
            $params = ['search' => "%$search%"];
        }
        
        $sql .= " ORDER BY r.rental_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getRentalById($id) {
        $stmt = $this->db->prepare("SELECT r.*, 
                                   c.name AS costume_name, c.price AS costume_price,
                                   cu.name AS customer_name
                                   FROM rentals r
                                   JOIN costumes c ON r.costume_id = c.id
                                   JOIN customers cu ON r.customer_id = cu.id
                                   WHERE r.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createRental($data) {
        // Check costume availability
        $costume = (new Costume())->getCostumeById($data['costume_id']);
        if ($costume['stock'] < 1) {
            throw new Exception("Costume is out of stock");
        }
        
        // Calculate rental days and total price
        $rentalDate = new DateTime($data['rental_date']);
        $returnDate = new DateTime($data['return_date']);
        $days = $returnDate->diff($rentalDate)->days;
        $totalPrice = $costume['price'] * $days;
        
        // Create rental record
        $stmt = $this->db->prepare("INSERT INTO rentals 
                                   (costume_id, customer_id, rental_date, return_date, total_price, notes)
                                   VALUES (?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([
            $data['costume_id'],
            $data['customer_id'],
            $data['rental_date'],
            $data['return_date'],
            $totalPrice,
            $data['notes'] ?? null
        ]);
        
        if ($result) {
            // Update costume stock
            (new Costume())->updateStock($data['costume_id'], $costume['stock'] - 1);
        }
        
        return $result;
    }

    public function returnRental($id) {
        $rental = $this->getRentalById($id);
        if (!$rental) {
            throw new Exception("Rental not found");
        }
        
        if ($rental['status'] === 'returned') {
            throw new Exception("This rental is already returned");
        }
        
        // Update rental record
        $stmt = $this->db->prepare("UPDATE rentals SET 
                                   actual_return_date = CURDATE(), 
                                   status = 'returned'
                                   WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        if ($result) {
            // Update costume stock
            $costume = (new Costume())->getCostumeById($rental['costume_id']);
            (new Costume())->updateStock($rental['costume_id'], $costume['stock'] + 1);
        }
        
        return $result;
    }

    public function deleteRental($id) {
        $rental = $this->getRentalById($id);
        if (!$rental) {
            throw new Exception("Rental not found");
        }
        
        // If not returned, update costume stock
        if ($rental['status'] !== 'returned') {
            $costume = (new Costume())->getCostumeById($rental['costume_id']);
            (new Costume())->updateStock($rental['costume_id'], $costume['stock'] + 1);
        }
        
        $stmt = $this->db->prepare("DELETE FROM rentals WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>