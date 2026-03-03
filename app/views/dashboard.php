<div class="container-fluid mt-4">
	<div class="row justify-content-center">
		<div class="col-md-8 col-lg-6">
			<div class="card shadow-lg border-0 rounded-4">
				<div class="card-body p-4">
					<h3 class="text-center text-success mb-4">Food Analytics Dashboard</h3>
						<?php
						// Fallback: if $data is missing (should not happen)
						$data = $data ?? [];
						// Extract with defaults
						extract($data, EXTR_SKIP);

						
						// Set defaults for safety
						$totalFood          ??= 0;
						$expiredFood        ??= 0;
						$foodInMeals        ??= 0;
						$completedDonations ??= 0;
						$foodSaved          ??= 0;
						$months             ??= [];
						$savedCounts        ??= [];
						$donationCounts     ??= [];
						$foodAddedCounts	??= [];
						$dateFrom           ??= '';
                    	$dateTo             ??= '';
						
						?>
						<script>
							console.log("DASHBOARD DEBUG START");
							console.log("User ID:", <?= json_encode($_SESSION['user_id']) ?>);
							console.log("Total Food:", <?= $totalFood ?>);
							console.log("Months:", <?= json_encode($months) ?>);
							console.log("foodAddedCounts:", <?= json_encode($foodAddedCounts) ?>);
							console.log("DASHBOARD DEBUG END");
						</script>
						<div class="stats">
							<p><strong>Total Food Added:</strong> <?= $totalFood ?></p>
							<p><strong>Food Saved:</strong> <?= $foodSaved ?>
								(Meals: <?= $foodInMeals ?>, Donated: <?= $completedDonations ?>)</p>
							<p><strong>Expired:</strong> <?= $expiredFood ?></p>
						</div>

						 <!-- Date Filter Form -->
						<form method="GET" action="/dashboard" class="row g-2 align-items-end mb-4">
							<div class="col-sm-5">
								<label class="form-label fw-semibold mb-1">From</label>
								<input
									type="date"
									name="date_from"
									class="form-control"
									value="<?= htmlspecialchars($dateFrom) ?>"
								>
							</div>
							<div class="col-sm-5">
								<label class="form-label fw-semibold mb-1">To</label>
								<input
									type="date"
									name="date_to"
									class="form-control"
									value="<?= htmlspecialchars($dateTo) ?>"
								>
							</div>
							<div class="col-sm-2 d-flex gap-1">
								<button type="submit" class="btn btn-success w-100">Filter</button>
								<a href="/dashboard" class="btn btn-outline-secondary w-100">Reset</a>
							</div>
						</form>

						<?php if ($dateFrom || $dateTo): ?>
							<p class="text-muted small mb-3">
								Showing data
								<?= $dateFrom ? 'from <strong>' . htmlspecialchars($dateFrom) . '</strong> ' : '' ?>
								<?= $dateTo   ? 'to <strong>'   . htmlspecialchars($dateTo)   . '</strong>' : '' ?>
							</p>
						<?php endif; ?>

						<h2>Trends</h2>
						<?php if (!empty($months)): ?>
							<canvas id="monthlyChart" style="width:600px; height: 400px; margin:auto;"></canvas>
						<?php else: ?>
							<p>No data available for the chart.</p>
						<?php endif; ?>

						<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
						<script>
							<?php if (!empty($months)): ?>
							const ctx = document.getElementById('monthlyChart').getContext('2d');
							new Chart(ctx, {
								type: 'line',
								data: {
									labels: <?= json_encode($months) ?>,
									datasets: [
										{
											label: 'Food Saved',
											data: <?= json_encode($savedCounts) ?>,
											borderColor: '#4caf50',
											backgroundColor: 'rgba(76,175,80,0.1)',
											fill: true,
											tension: 0.3
										},
										{
											label: 'Donations',
											data: <?= json_encode($donationCounts) ?>,
											borderColor: '#9c27b0',
											backgroundColor: 'rgba(156,39,176,0.1)',
											fill: true,
											tension: 0.3
										},
										{
											label: 'Total Food Added',
											data: <?= json_encode($foodAddedCounts) ?>,
											borderColor: '#2196f3',
											backgroundColor: 'rgba(33, 150, 243, 0.6)',
											fill: true,
											tension: 0.3
										}
									]
								},
								options: {
									responsive: true,
									plugins: { legend: { position: 'top' } },
									scales: { y: { beginAtZero: true } }
								}
							});
							<?php endif; ?>
						</script>
				</div>
            </div>
        </div>
    </div>
</div>