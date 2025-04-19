<?php
session_start();
require_once 'class/Costume.php';
require_once 'class/Costumer.php';
require_once 'class/Rental.php';

// Initialize classes
$costume = new Costume();
$customer = new Customer();
$rental = new Rental();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Handle Costume CRUD
        if (isset($_POST['create_costume'])) {
            $costume->createCostume($_POST, $_FILES['image']);
            $_SESSION['message'] = "Costume added successfully!";
            header("Location: ?page=costumes");
            exit();
        }
        
        if (isset($_POST['edit_costume'])) {
            $costume->updateCostume($_POST['id'], $_POST, $_FILES['image']);
            $_SESSION['message'] = "Costume updated successfully!";
            header("Location: ?page=costumes");
            exit();
        }
        
        // Handle Customer CRUD
        if (isset($_POST['create_customer'])) {
            $customer->createCustomer($_POST);
            $_SESSION['message'] = "Customer added successfully!";
            header("Location: ?page=customers");
            exit();
        }
        
        if (isset($_POST['edit_customer'])) {
            $customer->updateCustomer($_POST['id'], $_POST);
            $_SESSION['message'] = "Customer updated successfully!";
            header("Location: ?page=customers");
            exit();
        }
        
        // Handle Rental CRUD
        if (isset($_POST['create_rental'])) {
            $rental->createRental($_POST);
            $_SESSION['message'] = "Rental created successfully!";
            header("Location: ?page=rentals");
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
}

// Handle GET actions
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Delete Costume
        if (isset($_GET['delete_costume'])) {
            $costume->deleteCostume($_GET['delete_costume']);
            $_SESSION['message'] = "Costume deleted successfully!";
            header("Location: ?page=costumes");
            exit();
        }
        
        // Delete Customer
        if (isset($_GET['delete_customer'])) {
            $customer->deleteCustomer($_GET['delete_customer']);
            $_SESSION['message'] = "Customer deleted successfully!";
            header("Location: ?page=customers");
            exit();
        }
        
        // Return Rental
        if (isset($_GET['return_rental'])) {
            $rental->returnRental($_GET['return_rental']);
            $_SESSION['message'] = "Costume returned successfully!";
            header("Location: ?page=rentals");
            exit();
        }
        
        // Delete Rental
        if (isset($_GET['delete_rental'])) {
            $rental->deleteRental($_GET['delete_rental']);
            $_SESSION['message'] = "Rental deleted successfully!";
            header("Location: ?page=rentals");
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
}

// Display messages
function displayMessages() {
    if (isset($_SESSION['message'])) {
        echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['message']) . '</div>';
        unset($_SESSION['message']);
    }
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
        unset($_SESSION['error']);
    }
}

// Include header
include 'view/header.php';
?>

<main class="container">
    <?php displayMessages(); ?>
    
    <?php
    $validPages = ['costumes', 'customers', 'rentals'];
    $page = isset($_GET['page']) && in_array($_GET['page'], $validPages) ? $_GET['page'] : 'costumes';
    
    switch ($page) {
        case 'costumes':
            include 'view/costumes.php';
            break;
        case 'customers':
            include 'view/costumers.php';
            break;
        case 'rentals':
            include 'view/rentals.php';
            break;
    }
    ?>
</main>

<?php
// Include footer
include 'view/footer.php';
?>