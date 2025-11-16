<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-4">
                    <h3 class="text-center text-success mb-4">Add New Food Item</h3>

                    <form method="POST" action="/inventory/add">
                        
                        <!-- Food Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label fw-bold">Food Name</label>
                            <input type="text" name="name" id="name" class="form-control" placeholder="e.g., Chicken Breast" required>
                        </div>

                        <!-- Type -->
                        <div class="mb-3">
                            <label for="type" class="form-label fw-bold">Type</label>
                            <select name="type" id="type" class="form-select" required>
                                <option value="" selected disabled>Select type</option>
                                <option value="canned">Canned</option>
                                <option value="frozen">Frozen</option>
                                <option value="fresh">Fresh</option>
                                <option value="dry">Dry</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <!-- Category -->
                        <div class="mb-3">
                            <label for="category" class="form-label fw-bold">Category</label>
                            <select name="category" id="category" class="form-select">
                                <option value="protein">Protein</option>
                                <option value="vegetable">Vegetable</option>
                                <option value="grain">Grain</option>
                                <option value="fruit">Fruit</option>
                                <option value="dairy">Dairy</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <!-- Quantity -->
                        <div class="mb-3">
                            <label for="quantity" class="form-label fw-bold">Quantity</label>
                            <input type="number" name="quantity" id="quantity" class="form-control" value="1" min="1" required>
                        </div>

                        <!-- Expiration Date -->
                        <div class="mb-3">
                            <label for="expiration_date" class="form-label fw-bold">Expiration Date</label>
                            <input type="date" name="expiration_date" id="expiration_date" class="form-control">
                        </div>

                        <!-- Storage Location -->
                        <div class="mb-3">
                            <label for="storage_location" class="form-label fw-bold">Storage Location</label>
                            <input type="text" name="storage_location" id="storage_location" class="form-control" placeholder="e.g., Freezer, Pantry">
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label fw-bold">Notes</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Any additional details..."></textarea>
                        </div>

                        <!-- Submit -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg rounded-pill">Add Food</button>
                        </div>
                    </form>

                    <!-- Back button -->
                    <div class="text-center mt-3">
                        <a href="/inventory" class="text-decoration-none">
                            <i class="bi bi-arrow-left"></i> Back to Inventory
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>