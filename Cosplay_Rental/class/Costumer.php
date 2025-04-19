<?php
require_once 'config/db.php';

class Customer {
    private $db;

    public function __construct() {
        $this->db = (new Database())->conn;
    }

    public function getAllCustomers($search = '') {
        $sql = "SELECT * FROM customers";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " WHERE name LIKE :search OR email LIKE :search OR phone LIKE :search";
            $params = ['search' => "%$search%"];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getCustomerById($id) {
        $stmt = $this->db->prepare("SELECT * FROM customers WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createCustomer($data) {
        $stmt = $this->db->prepare("INSERT INTO customers (name, email, phone, address) 
                                   VALUES (?, ?, ?, ?)");
        return $stmt->execute([
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['address']
        ]);
    }

    public function updateCustomer($id, $data) {
        $stmt = $this->db->prepare("UPDATE customers SET 
                                  name = ?, email = ?, phone = ?, address = ? 
                                  WHERE id = ?");
        return $stmt->execute([
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['address'],
            $id
        ]);
    }

    public function deleteCustomer($id) {
        // Check for active rentals first
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM rentals 
                                   WHERE customer_id = ? AND status != 'returned'");
        $stmt->execute([$id]);
        $activeRentals = $stmt->fetchColumn();
        
        if ($activeRentals > 0) {
            throw new Exception("Cannot delete customer with active rentals");
        }
        
        $stmt = $this->db->prepare("DELETE FROM customers WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>