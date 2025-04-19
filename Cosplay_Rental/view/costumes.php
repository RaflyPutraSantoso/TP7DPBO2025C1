<div class="page-header">
    <h1><i class="fas fa-tshirt"></i> Costume Management</h1>
    <div class="actions">
        <a href="?page=costumes&action=create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Costume
        </a>
    </div>
</div>

<div class="search-bar">
    <form method="GET">
        <input type="hidden" name="page" value="costumes">
        <div class="input-group">
            <input type="text" name="search" placeholder="Search costumes..." 
                   value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-search"></i> Search
            </button>
            <?php if (!empty($_GET['search'])): ?>
                <a href="?page=costumes" class="btn btn-outline">
                    <i class="fas fa-times"></i> Clear
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php if (empty($costumes = $costume->getAllCostumes($_GET['search'] ?? ''))): ?>
    <div class="empty-state">
        <i class="fas fa-tshirt fa-4x"></i>
        <h3>No costumes found</h3>
        <p>Add your first costume to get started</p>
        <a href="?page=costumes&action=create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Costume
        </a>
    </div>
<?php else: ?>
    <div class="grid-container">
        <?php foreach ($costumes as $c): ?>
            <div class="card costume-card">
                <?php if ($c['image_path']): ?>
                    <div class="card-image">
                        <img src="<?= htmlspecialchars($c['image_path']) ?>" alt="<?= htmlspecialchars($c['name']) ?>">
                    </div>
                <?php endif; ?>
                <div class="card-body">
                    <h3><?= htmlspecialchars($c['name']) ?></h3>
                    <div class="meta">
                        <span class="series"><?= htmlspecialchars($c['series']) ?></span>
                        <span class="size">Size: <?= htmlspecialchars($c['size']) ?></span>
                    </div>
                    <div class="price-stock">
                        <span class="price">Rp <?= number_format($c['price'], 0, ',', '.') ?></span>
                        <span class="stock <?= $c['stock'] > 0 ? 'in-stock' : 'out-of-stock' ?>">
                            <?= $c['stock'] > 0 ? "{$c['stock']} available" : 'Out of stock' ?>
                        </span>
                    </div>
                    <p class="description"><?= htmlspecialchars($c['description']) ?></p>
                    <div class="card-actions">
                        <a href="?page=costumes&action=edit&id=<?= $c['id'] ?>" class="btn btn-sm btn-secondary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="?page=costumes&delete_costume=<?= $c['id'] ?>" 
                           class="btn btn-sm btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this costume?')">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['action']) && in_array($_GET['action'], ['create', 'edit'])): ?>
    <div class="modal active">
        <div class="modal-content">
            <div class="modal-header">
                <h2>
                    <i class="fas fa-tshirt"></i> 
                    <?= ucfirst($_GET['action']) ?> Costume
                </h2>
                <a href="?page=costumes" class="close-btn">&times;</a>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <?php if ($_GET['action'] == 'edit'): ?>
                    <input type="hidden" name="id" value="<?= $_GET['id'] ?>">
                    <?php $costumeData = $costume->getCostumeById($_GET['id']); ?>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="name">Costume Name</label>
                    <input type="text" id="name" name="name" 
                           value="<?= htmlspecialchars($costumeData['name'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="series">Series/Character</label>
                    <input type="text" id="series" name="series" 
                           value="<?= htmlspecialchars($costumeData['series'] ?? '') ?>" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="size">Size</label>
                        <select id="size" name="size" required>
                            <option value="XS" <?= isset($costumeData) && $costumeData['size'] == 'XS' ? 'selected' : '' ?>>XS</option>
                            <option value="S" <?= isset($costumeData) && $costumeData['size'] == 'S' ? 'selected' : '' ?>>S</option>
                            <option value="M" <?= isset($costumeData) && $costumeData['size'] == 'M' ? 'selected' : '' ?>>M</option>
                            <option value="L" <?= isset($costumeData) && $costumeData['size'] == 'L' ? 'selected' : '' ?>>L</option>
                            <option value="XL" <?= isset($costumeData) && $costumeData['size'] == 'XL' ? 'selected' : '' ?>>XL</option>
                            <option value="XXL" <?= isset($costumeData) && $costumeData['size'] == 'XXL' ? 'selected' : '' ?>>XXL</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price (Rp)</label>
                        <input type="number" id="price" name="price" 
                               value="<?= htmlspecialchars($costumeData['price'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="stock">Stock</label>
                        <input type="number" id="stock" name="stock" min="0"
                               value="<?= htmlspecialchars($costumeData['stock'] ?? 1) ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="image">Image</label>
                    <input type="file" id="image" name="image" <?= $_GET['action'] == 'create' ? 'required' : '' ?>>
                    <?php if ($_GET['action'] == 'edit' && !empty($costumeData['image_path'])): ?>
                        <div class="current-image">
                            <small>Current Image:</small>
                            <img src="<?= htmlspecialchars($costumeData['image_path']) ?>" 
                                 alt="<?= htmlspecialchars($costumeData['name']) ?>" 
                                 style="max-width: 100px; display: block; margin-top: 5px;">
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"><?= htmlspecialchars($costumeData['description'] ?? '') ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="<?= $_GET['action'] ?>_costume" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save
                    </button>
                    <a href="?page=costumes" class="btn btn-outline">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>