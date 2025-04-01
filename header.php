<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduJobs Scholars - Your Gateway to Education and Career Opportunities</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #6B73FF;
            --secondary-color: #000DFF;
            --accent-color: #FF6B6B;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 1rem 0;
        }

        .navbar-brand {
            color: white !important;
            font-weight: bold;
            font-size: 1.5rem;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: white !important;
        }

        .home-banner {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 4rem 0;
            text-align: center;
            margin-bottom: 3rem;
        }

        .home-banner h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .home-buttons {
            margin-top: 2rem;
        }

        .home-buttons .btn {
            margin: 0.5rem;
            padding: 0.75rem 1.5rem;
        }

        .featured-section {
            padding: 4rem 0;
            border-bottom: 1px solid #eee;
        }

        .section-intro {
            max-width: 800px;
            margin: 0 auto 2rem;
            text-align: center;
        }

        .tab-container {
            margin-top: 2rem;
        }

        .tabs {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
            gap: 1rem;
        }

        .tab-btn {
            padding: 0.75rem 1.5rem;
            border: none;
            background: #f8f9fa;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .tab-btn.active {
            background: var(--primary-color);
            color: white;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .university-card, .scholarship-card, .job-card, .service-card, .feature-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .university-card:hover, .scholarship-card:hover, .job-card:hover, .service-card:hover, .feature-card:hover {
            transform: translateY(-5px);
        }

        .university-logo, .scholarship-icon, .service-icon, .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .deadline {
            color: var(--accent-color);
            font-weight: bold;
        }

        .job-search-container {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .search-row {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .search-group {
            flex: 1;
            min-width: 200px;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .service-features {
            list-style: none;
            padding: 0;
            margin: 1rem 0;
        }

        .service-features li {
            margin-bottom: 0.5rem;
            padding-left: 1.5rem;
            position: relative;
        }

        .service-features li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: var(--primary-color);
        }

        .service-pricing {
            margin: 1.5rem 0;
            text-align: center;
        }

        .price {
            font-size: 1.25rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .about-section {
            text-align: center;
            max-width: 800px;
            margin: 4rem auto;
            padding: 0 1rem;
        }

        .contact-info {
            margin-top: 2rem;
            text-align: left;
        }

        .footer-section {
            background: #333;
            color: white;
            text-align: center;
            padding: 2rem 0;
            margin-top: 4rem;
        }

        @media (max-width: 768px) {
            .search-row {
                flex-direction: column;
            }
            
            .search-group {
                width: 100%;
            }
            
            .tabs {
                flex-direction: column;
            }
            
            .tab-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="home.php">EduJobs Scholars</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#university-section">Universities</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#jobs-section">Jobs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#consulting-section">Consulting</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                        <li class="nav-item">
                            <span class="nav-link">Welcome, <?php echo isset($_SESSION["username"]) ? htmlspecialchars($_SESSION["username"]) : 'User'; ?></span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</body>
</html> 