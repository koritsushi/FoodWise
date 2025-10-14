<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>FoodWise</title>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">		
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
	</head>
	<style>
	html, body {
		height: 100%;
	}

	/* This makes the layout a flex column that fills the viewport */
	.page-container {
		display: flex;
		flex-direction: column;
		min-height: 100vh;
	}

	/* This makes the main content grow to push the footer down */
	.content-wrap {
		flex: 1;
	}
	/* Sidebar Styling */
	.sidebar {
		width: 250px;
		height: 100vh;
		background-color: #2c3e50;
		position: fixed;
		top: 0;
		left: 0;
		color: #ecf0f1;
		display: flex;
		flex-direction: column;
		justify-content: space-between;
		transition: all 0.3s ease-in-out;
	}

	.sidebar .sidebar-header {
		padding: 1.5rem;
		text-align: center;
		border-bottom: 1px solid rgba(255, 255, 255, 0.1);
	}

	.sidebar .sidebar-header h3 {
		font-weight: bold;
		margin-top: 0.5rem;
	}

	.sidebar .nav-link {
		color: #ecf0f1;
		padding: 12px 20px;
		transition: all 0.3s;
		border-radius: 8px;
		margin: 4px 12px;
	}

	.sidebar .nav-link:hover,
	.sidebar .nav-link.active {
		background-color: #34495e;
		color: #fff;
	}

	.sidebar .footer {
		text-align: center;
		padding: 1rem;
		border-top: 1px solid rgba(255, 255, 255, 0.1);
		font-size: 0.9rem;
		color: #bdc3c7;
	}

	/* Responsive Toggle */
	@media (max-width: 768px) {
		.sidebar {
			width: 200px;
		}
	}
	</style>
	<body class="page-container">

	