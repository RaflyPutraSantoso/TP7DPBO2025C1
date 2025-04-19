<div class="page-header">
    <h1><i class="fas fa-users"></i> Customer Management</h1>
    <div class="actions">
        <a href="?page=customers&action=create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Customer
        </a>
    </div>
</div>

<div class="search-bar">
    <form method="GET">
        <input type="hidden" name="page" value="customers">
        <div class="input-group">
            <input type="text" name="search" placeholder="Search customers..." 
                   value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-search"></i> Search
            </button>
            <?php if (!empty($_GET['search'])): ?>
                <a href="?page=customers" class="btn btn-outline">
                    <i class="fas fa-times"></i> Clear
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php if (empty($customers = $customer->getAllCustomers($_GET['search'] ?? ''))): ?>
    <div class="empty-state">
        <i class="fas fa-users fa-4x"></i>
        <h3>No customers found</h3>
        <p>Add your first customer to get started</p>
        <a href="?page=customers&action=create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Customer
        </a>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['id']) ?></td>
                        <td><?= htmlspecialchars($c['name']) ?></td>
                        <td><?= htmlspecialchars($c['email']) ?></td>
                        <td><?= htmlspecialchars($c['phone']) ?></td>
                        <td class="actions">
                            <a href="?page=customers&action=edit&id=<?= $c['id'] ?>" 
                               class="btn btn-sm btn-secondary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="?page=customers&delete_customer=<?= $c['id'] ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Are you sure you want to delete this customer?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php if (isset($_GET['action']) && in_array($_GET['action'], ['create', 'edit'])): ?>
    <div class="modal active">
        <div class="modal-content">
            <div class="modal-header">
                <h2>
                    <i class="fas fa-user"></i> 
                    <?= ucfirst($_GET['action']) ?> Customer
                </h2>
                <a href="?page=customers" class="close-btn">&times;</a>
            </div>
            <form method="POST">
                <?php if ($_GET['action'] == 'edit'): ?>
                    <input type="hidden" name="id" value="<?= $_GET['id'] ?>">
                    <?php $customerData = $customer->getCustomerById($_GET['id']); ?>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" 
                           value="<?= htmlspecialchars($customerData['name'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" 
                           value="<?= htmlspecialchars($customerData['email'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" 
                           value="<?= htmlspecialchars($customerData['phone'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" required><?= htmlspecialchars($customerData['address'] ?? '') ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="<?= $_GET['action'] ?>_customer" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save
                    </button>
                    <a href="?page=customers" class="btn btn-outline">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>