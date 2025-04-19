<div class="page-header">
    <h1><i class="fas fa-clipboard-list"></i> Rental Management</h1>
    <div class="actions">
        <a href="?page=rentals&action=create" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Rental
        </a>
    </div>
</div>

<div class="search-bar">
    <form method="GET">
        <input type="hidden" name="page" value="rentals">
        <div class="input-group">
            <input type="text" name="search" placeholder="Search rentals..." 
                   value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-search"></i> Search
            </button>
            <?php if (!empty($_GET['search'])): ?>
                <a href="?page=rentals" class="btn btn-outline">
                    <i class="fas fa-times"></i> Clear
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php 
$rentals = $rental->getAllRentals($_GET['search'] ?? '');
if (empty($rentals)): ?>
    <div class="empty-state">
        <i class="fas fa-clipboard-list fa-4x"></i>
        <h3>No rentals found</h3>
        <p>Create your first rental to get started</p>
        <a href="?page=rentals&action=create" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Rental
        </a>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Costume</th>
                    <th>Customer</th>
                    <th>Rental Period</th>
                    <th>Status</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rentals as $r): 
                    $rentalDate = new DateTime($r['rental_date']);
                    $returnDate = new DateTime($r['return_date']);
                    $isOverdue = $r['status'] == 'rented' && $returnDate < new DateTime();
                ?>
                    <tr>
                        <td><?= $r['id'] ?></td>
                        <td class="costume-info">
                            <?php if ($r['costume_image']): ?>
                                <img src="<?= htmlspecialchars($r['costume_image']) ?>" 
                                     alt="<?= htmlspecialchars($r['costume_name']) ?>" 
                                     class="thumbnail">
                            <?php endif; ?>
                            <div>
                                <strong><?= htmlspecialchars($r['costume_name']) ?></strong>
                                <small>Rp <?= number_format($r['costume_price'], 0, ',', '.') ?>/day</small>
                            </div>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($r['customer_name']) ?></strong>
                            <small><?= htmlspecialchars($r['customer_email']) ?></small>
                        </td>
                        <td>
                            <div class="date-range">
                                <span><?= $rentalDate->format('d M Y') ?></span>
                                <i class="fas fa-arrow-right"></i>
                                <span><?= $returnDate->format('d M Y') ?></span>
                                <?php if ($r['actual_return_date']): ?>
                                    <div class="actual-return">
                                        <small>Returned: <?= date('d M Y', strtotime($r['actual_return_date'])) ?></small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge status-<?= $isOverdue ? 'overdue' : $r['status'] ?>">
                                <?= $isOverdue ? 'Overdue' : ucfirst($r['status']) ?>
                                <?php if ($isOverdue): ?>
                                    <i class="fas fa-exclamation-circle"></i>
                                <?php endif; ?>
                            </span>
                        </td>
                        <td>
                            Rp <?= number_format($r['total_price'], 0, ',', '.') ?>
                        </td>
                        <td class="actions">
                            <?php if ($r['status'] !== 'returned'): ?>
                                <a href="?page=rentals&return_rental=<?= $r['id'] ?>" 
                                   class="btn btn-sm btn-success"
                                   onclick="return confirm('Mark this rental as returned?')">
                                    <i class="fas fa-check"></i> Return
                                </a>
                            <?php endif; ?>
                            <a href="?page=rentals&delete_rental=<?= $r['id'] ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Are you sure you want to delete this rental?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php if (isset($_GET['action']) && $_GET['action'] == 'create'): ?>
    <div class="modal active">
        <div class="modal-content">
            <div class="modal-header">
                <h2>
                    <i class="fas fa-clipboard-list"></i> 
                    Create New Rental
                </h2>
                <a href="?page=rentals" class="close-btn">&times;</a>
            </div>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="costume_id">Costume *</label>
                        <select id="costume_id" name="costume_id" required class="select2">
                            <option value="">Select Costume</option>
                            <?php foreach ($costume->getAllCostumes() as $c): ?>
                                <?php if ($c['stock'] > 0): ?>
                                    <option value="<?= $c['id'] ?>" 
                                        data-price="<?= $c['price'] ?>"
                                        data-stock="<?= $c['stock'] ?>">
                                        <?= htmlspecialchars($c['name']) ?> - 
                                        <?= htmlspecialchars($c['series']) ?> 
                                        (Rp <?= number_format($c['price'], 0, ',', '.') ?>)
                                        - <?= $c['stock'] ?> available
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_id">Customer *</label>
                        <select id="customer_id" name="customer_id" required class="select2">
                            <option value="">Select Customer</option>
                            <?php foreach ($customer->getAllCustomers() as $cu): ?>
                                <option value="<?= $cu['id'] ?>">
                                    <?= htmlspecialchars($cu['name']) ?> - 
                                    <?= htmlspecialchars($cu['email']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="rental_date">Rental Date *</label>
                        <input type="date" id="rental_date" name="rental_date" 
                               value="<?= date('Y-m-d') ?>" required
                               min="<?= date('Y-m-d') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="return_date">Return Date *</label>
                        <input type="date" id="return_date" name="return_date" 
                               value="<?= date('Y-m-d', strtotime('+3 days')) ?>" required
                               min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3"></textarea>
                </div>
                
                <div class="price-summary">
                    <div class="price-item">
                        <span>Daily Price:</span>
                        <span id="daily-price">Rp 0</span>
                    </div>
                    <div class="price-item">
                        <span>Rental Days:</span>
                        <span id="rental-days">0 days</span>
                    </div>
                    <div class="price-total">
                        <span>Total Price:</span>
                        <span id="total-price">Rp 0</span>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="create_rental" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Rental
                    </button>
                    <a href="?page=rentals" class="btn btn-outline">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const costumeSelect = document.getElementById('costume_id');
        const rentalDate = document.getElementById('rental_date');
        const returnDate = document.getElementById('return_date');
        const dailyPriceEl = document.getElementById('daily-price');
        const rentalDaysEl = document.getElementById('rental-days');
        const totalPriceEl = document.getElementById('total-price');

        function calculatePrice() {
            const selectedOption = costumeSelect.options[costumeSelect.selectedIndex];
            const price = selectedOption ? parseFloat(selectedOption.dataset.price) : 0;
            
            const startDate = new Date(rentalDate.value);
            const endDate = new Date(returnDate.value);
            const days = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)) || 0;
            
            dailyPriceEl.textContent = `Rp ${price.toLocaleString('id-ID')}`;
            rentalDaysEl.textContent = `${days} day${days !== 1 ? 's' : ''}`;
            totalPriceEl.textContent = `Rp ${(price * days).toLocaleString('id-ID')}`;
        }

        costumeSelect.addEventListener('change', calculatePrice);
        rentalDate.addEventListener('change', calculatePrice);
        returnDate.addEventListener('change', calculatePrice);

        // Initialize calculation
        calculatePrice();
    });
    </script>
<?php endif; ?>