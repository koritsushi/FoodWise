<div class="container mt-5">
	<div class="row justify-content-center">
		<div class="col-md-8 col-lg-6">
			<div class="card shadow-lg border-0 rounded-4">
				<div class="card-body p-4">
					<h3 class="text-center text-success mb-4">Food Analytics Dashboard</h3>
						<div class="notifications-list">
							<?php if (empty($notifications)): ?>
								<div class="empty-state">
									<h3>No notifications yet</h3>
									<p>We'll let you know when something needs your attention.</p>
								</div>
							<?php else: ?>
								<?php foreach ($notifications as $n): ?>
									<div class="notification-item <?= $n['is_read'] ? '' : 'unread' ?>">
										<div class="notif-icon">
											<?php
											$icons = [
												'inventory' => 'Food',
												'donation'  => 'Heart',
												'meal'      => 'Calendar',
												'system'    => 'Info'
											];
											echo $icons[$n['type']] ?? 'Bell';
											?>
										</div>
										<div class="notif-content">
											<div class="notif-header">
												<strong><?= htmlspecialchars($n['title']) ?></strong>
												<span class="notif-time">
													<?= $this->timeAgo($n['created_at']) ?>
												</span>
											</div>
											<div class="notif-message">
												<?= nl2br(htmlspecialchars($n['message'])) ?>
											</div>
										</div>
										<?php if (!$n['is_read']): ?>
											<div class="notif-unread-dot"></div>
										<?php endif; ?>
									</div>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>