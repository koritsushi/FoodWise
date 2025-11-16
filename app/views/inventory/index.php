<div class="container mt-4">
    <h2 class="mb-4">My Food Inventory</h2>

    <div class="mb-3">
        <a href="/inventory/add" class="btn btn-primary">➕ Add New Food Item</a>
    </div>

    <?php if (!empty($foods)) : ?>
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Storage Location</th>
                    <th>Expiration Date</th>
                    <th>Quantity</th>
                    <th>Notes</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($foods as $food): ?>
                    <tr>
                        <td><?= htmlspecialchars($food['name']) ?></td>
                        <td><?= ucfirst($food['type']) ?></td>
                        <td><?= ucfirst($food['category']) ?></td>
                        <td><?= htmlspecialchars($food['storage_location']) ?></td>
                        <td>
                            <?= $food['expiration_date'] ? date('Y-m-d', strtotime($food['expiration_date'])) : 'N/A' ?>
                        </td>
                        <td><?= (int)$food['quantity'] ?></td>
                        <td><?= htmlspecialchars($food['notes']) ?></td>
                        <td>
                            <?php if ($food['is_expired']): ?>
                                <span class="badge bg-danger">Expired</span>
                            <?php else: ?>
                                <span class="badge bg-success">Good</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/inventory/delete/<?= $food['food_id'] ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Are you sure you want to delete this item?')">
                               Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No food items found. Add your first one!</div>
    <?php endif; ?>
</div>