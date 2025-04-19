<?php
require_once 'config/db.php';

class Costume {
    private $db;

    public function __construct() {
        $this->db = (new Database())->conn;
    }

    public function getAllCostumes($search = '') {
        $sql = "SELECT * FROM costumes";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " WHERE name LIKE :search OR series LIKE :search";
            $params = ['search' => "%$search%"];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getCostumeById($id) {
        $stmt = $this->db->prepare("SELECT * FROM costumes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createCostume($data, $image) {
        $imagePath = $this->uploadImage($image);
        $stmt = $this->db->prepare("INSERT INTO costumes (name, series, size, price, stock, image_path, description) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['name'],
            $data['series'],
            $data['size'],
            $data['price'],
            $data['stock'],
            $imagePath,
            $data['description']
        ]);
    }

    public function updateCostume($id, $data, $image = null) {
        $costume = $this->getCostumeById($id);
        $imagePath = $costume['image_path'];
        
        if ($image && $image['error'] == UPLOAD_ERR_OK) {
            if ($imagePath && file_exists($imagePath)) {
                unlink($imagePath);
            }
            $imagePath = $this->uploadImage($image);
        }

        $stmt = $this->db->prepare("UPDATE costumes SET 
                                  name = ?, series = ?, size = ?, price = ?, 
                                  stock = ?, image_path = ?, description = ? 
                                  WHERE id = ?");
        return $stmt->execute([
            $data['name'],
            $data['series'],
            $data['size'],
            $data['price'],
            $data['stock'],
            $imagePath,
            $data['description'],
            $id
        ]);
    }

    public function deleteCostume($id) {
        $costume = $this->getCostumeById($id);
        if ($costume['image_path'] && file_exists($costume['image_path'])) {
            unlink($costume['image_path']);
        }
        
        $stmt = $this->db->prepare("DELETE FROM costumes WHERE id = ?");
        return $stmt->execute([$id]);
    }

    private function uploadImage($image) {
        $targetDir = "uploads/costumes/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $fileExt = pathinfo($image["name"], PATHINFO_EXTENSION);
        $fileName = uniqid() . '.' . $fileExt;
        $targetFile = $targetDir . $fileName;
        
        // Check if image file is a actual image
        $check = getimagesize($image["tmp_name"]);
        if ($check === false) {
            throw new Exception("File is not an image.");
        }
        
        // Check file size (max 2MB)
        if ($image["size"] > 2000000) {
            throw new Exception("Sorry, your file is too large. Max 2MB allowed.");
        }
        
        // Allow certain file formats
        $allowedExts = ["jpg", "jpeg", "png", "gif"];
        if (!in_array(strtolower($fileExt), $allowedExts)) {
            throw new Exception("Sorry, only JPG, JPEG, PNG & GIF files are allowed.");
        }
        
        if (move_uploaded_file($image["tmp_name"], $targetFile)) {
            return $targetFile;
        }
        throw new Exception("Sorry, there was an error uploading your file.");
    }

    public function updateStock($id, $stock) {
        $stmt = $this->db->prepare("UPDATE costumes SET stock = ? WHERE id = ?");
        return $stmt->execute([$stock, $id]);
    }
}
?>