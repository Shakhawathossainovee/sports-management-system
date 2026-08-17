<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

require_once 'includes/config.php';

// Get all cities for dropdown
$cities_stmt = $conn->prepare("SELECT DISTINCT city FROM grounds WHERE status = 'active' ORDER BY city");
$cities_stmt->execute();
$cities_result = $cities_stmt->get_result();
$cities = [];
while ($row = $cities_result->fetch_assoc()) {
    $cities[] = $row['city'];
}
$cities_stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>🏆 Khela Hobe | Search</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    
    <!-- ===== FLATPICKR CSS ===== -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
    
    <style>
        /* ===== FIX: Calendar Overlap Issue ===== */
        .flatpickr-calendar {
            z-index: 9999 !important;
            position: absolute !important;
            top: 100% !important;
            left: 0 !important;
            margin-top: 4px !important;
            background: #1a1a1a !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
            border-radius: 12px !important;
            box-shadow: 0 20px 60px rgba(0,0,0,0.8) !important;
            width: 320px !important;
        }
        
        .flatpickr-calendar.open {
            display: block !important;
            opacity: 1 !important;
        }
        
        .flatpickr-current-month {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 8px !important;
            padding: 8px 0 !important;
        }
        
        .flatpickr-current-month select {
            background: rgba(255,255,255,0.08) !important;
            color: white !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
            border-radius: 6px !important;
            padding: 4px 8px !important;
            font-size: 14px !important;
            cursor: pointer !important;
        }
        
        .flatpickr-current-month select:hover {
            background: rgba(255,255,255,0.15) !important;
        }
        
        .flatpickr-day {
            color: white !important;
            border-radius: 8px !important;
            transition: 0.2s !important;
        }
        
        .flatpickr-day:hover {
            background: #7CCB96 !important;
            color: #000 !important;
        }
        
        .flatpickr-day.selected {
            background: #7CCB96 !important;
            color: #000 !important;
            border-color: #7CCB96 !important;
        }
        
        .flatpickr-day.today {
            border-color: #7CCB96 !important;
        }
        
        .flatpickr-day.disabled {
            color: #444 !important;
        }
        
        .flatpickr-weekday {
            color: #7CCB96 !important;
            font-weight: 600 !important;
        }
        
        .flatpickr-prev-month, .flatpickr-next-month {
            color: #7CCB96 !important;
            fill: #7CCB96 !important;
            padding: 8px !important;
        }
        
        .flatpickr-prev-month:hover, .flatpickr-next-month:hover {
            background: rgba(124, 203, 150, 0.1) !important;
            border-radius: 50% !important;
        }
    </style>
</head>
<body>

<div class="home-page">

    <!-- ===== NAVBAR ===== -->
    <header>
        <div class="container navbar">
            <div class="logo">
                <h2>
                    <span class="logo-khela">Khela</span>
                    <span class="logo-hobe">Hobe</span>
                    <span class="logo-trophy">🏆</span>
                </h2>
            </div>
            <nav>
                <ul>
                    <li><a href="index.html">Home</a></li>
                    <li><a href="search.php" class="active">Turfs & Fields</a></li>
                    <li><a href="privacy.html">Privacy Policy</a></li>
                    <li><a href="terms.html">Terms & Conditions</a></li>
                    <li><a href="contact.html">Contact Us</a></li>
                    <li><a href="about.html">About Us</a></li>
                </ul>
            </nav>
            <div class="nav-btn">
                <a href="login.html" class="login-btn">Login</a>
                <a href="register.html" class="register-btn">Register</a>
            </div>
        </div>
    </header>

    <!-- ===== SEARCH CONTENT ===== -->
    <div class="search-content">

        <!-- Search Header -->
        <div class="search-header">
            <div class="title-section">
                <h2><i class="fas fa-futbol"></i> Find Your Turfs & Fields</h2>
                <div class="sports-filter-icons">
                    <span class="filter-icon <?php echo (!isset($_GET['sport']) || $_GET['sport'] == '') ? 'active' : ''; ?>" data-sport="">🏟️ All</span>
                    <span class="filter-icon <?php echo (isset($_GET['sport']) && $_GET['sport'] == 'Football') ? 'active' : ''; ?>" data-sport="Football">⚽ Football</span>
                    <span class="filter-icon <?php echo (isset($_GET['sport']) && $_GET['sport'] == 'Cricket') ? 'active' : ''; ?>" data-sport="Cricket">🏏 Cricket</span>
                    <span class="filter-icon <?php echo (isset($_GET['sport']) && $_GET['sport'] == 'Basketball') ? 'active' : ''; ?>" data-sport="Basketball">🏀 Basketball</span>
                </div>
            </div>
            <div class="welcome-text">
                Welcome, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>!
            </div>
        </div>

        <!-- ===== SEARCH FILTERS ===== -->
        <form action="search.php" method="GET" class="search-filters" id="searchForm">
            <div class="filter-group">
                <label>Sport</label>
                <select name="sport" id="sportFilter">
                    <option value="">All Sports</option>
                    <option value="Football" <?php echo (isset($_GET['sport']) && $_GET['sport'] == 'Football') ? 'selected' : ''; ?>>⚽ Football</option>
                    <option value="Cricket" <?php echo (isset($_GET['sport']) && $_GET['sport'] == 'Cricket') ? 'selected' : ''; ?>>🏏 Cricket</option>
                    <option value="Basketball" <?php echo (isset($_GET['sport']) && $_GET['sport'] == 'Basketball') ? 'selected' : ''; ?>>🏀 Basketball</option>
                </select>
            </div>

            <div class="filter-group">
                <label>City</label>
                <select name="city" id="cityFilter">
                    <option value="">All Cities</option>
                    <?php foreach ($cities as $city): ?>
                        <option value="<?php echo htmlspecialchars($city); ?>" <?php echo (isset($_GET['city']) && $_GET['city'] == $city) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($city); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label>Location</label>
                <input type="text" name="location" id="locationInput" placeholder="📍 Search..." value="<?php echo isset($_GET['location']) ? htmlspecialchars($_GET['location']) : ''; ?>">
            </div>

            <div class="filter-group">
                <label>Date</label>
                <input type="text" name="date" id="dateFilter" placeholder="YYYY-MM-DD" value="<?php echo isset($_GET['date']) ? htmlspecialchars($_GET['date']) : ''; ?>">
            </div>

            <!-- ===== BUTTONS INLINE ===== -->
            <div class="filter-group">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i> Search
                </button>
                <a href="search.php" class="clear-btn">
                    <i class="fas fa-times"></i> Clear
                </a>
            </div>
        </form>

        <!-- Ground Results -->
        <div id="groundResults">
        <?php
        $location = isset($_GET['location']) ? $_GET['location'] : '';
        $city = isset($_GET['city']) ? $_GET['city'] : '';
        $date = isset($_GET['date']) ? $_GET['date'] : '';
        $sport = isset($_GET['sport']) ? $_GET['sport'] : '';

        $sql = "SELECT DISTINCT g.*, g.grade FROM grounds g 
                WHERE g.status = 'active'";

        if (!empty($sport)) {
            $sql .= " AND g.sport_type = '" . $conn->real_escape_string($sport) . "'";
        }
        if (!empty($city)) {
            $sql .= " AND g.city = '" . $conn->real_escape_string($city) . "'";
        }
        if (!empty($location)) {
            $sql .= " AND g.location LIKE '%" . $conn->real_escape_string($location) . "%'";
        }

        if (!empty($date)) {
            $sql .= " AND EXISTS (
                SELECT 1 FROM time_slots ts 
                WHERE ts.ground_id = g.ground_id 
                AND ts.date = '" . $conn->real_escape_string($date) . "' 
                AND ts.is_available = 1
            )";
        }

        $sql .= " ORDER BY g.ground_id DESC";

        $result = $conn->query($sql);
        ?>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="ground-grid">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="ground-card">
                        <div class="card-header">
                            <span class="sport-badge"><?php echo htmlspecialchars($row['sport_type']); ?></span>
                            <span class="rating">
                                <?php
                                $rating = $row['average_rating'] ?? 0;
                                $total_reviews = $row['total_reviews'] ?? 0;
                                echo str_repeat('⭐', round($rating));
                                ?>
                                <span>(<?php echo $total_reviews; ?>)</span>
                            </span>
                        </div>

                        <div class="ground-name"><?php echo htmlspecialchars($row['name']); ?></div>

                        <?php if ($row['grade']): ?>
                            <span class="grade-badge grade-<?php echo strtolower(htmlspecialchars($row['grade'])); ?>">⭐ Grade: <?php echo htmlspecialchars($row['grade']); ?></span>
                        <?php endif; ?>

                        <div class="location">
                            <i class="fas fa-map-marker-alt" style="color:#7CCB96;"></i> <?php echo htmlspecialchars($row['location']); ?>
                        </div>

                        <div class="facilities">
                            <i class="fas fa-tools"></i> <?php echo htmlspecialchars(substr($row['facilities'] ?? 'No facilities listed', 0, 40)); ?>
                            <?php if (strlen($row['facilities'] ?? '') > 40) echo '...'; ?>
                        </div>

                        <div class="price">
                            ৳<?php echo number_format($row['rental_fee_per_hour'], 0); ?> <span>/ hr</span>
                        </div>

                        <a href="booking.php?ground_id=<?php echo $row['ground_id']; ?>" class="book-btn">
                            <i class="fas fa-calendar-plus"></i> Book Now
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <div class="found-count">
                Found <?php echo $result->num_rows; ?> ground(s)
            </div>
        <?php else: ?>
            <div style="background:rgba(26,26,26,0.85); padding:60px; border-radius:12px; text-align:center; border:1px solid rgba(255,255,255,0.06); backdrop-filter:blur(3px);">
                <div style="font-size:48px; color:#555;">🏟️</div>
                <p style="color:#888; font-size:16px; margin:10px 0;">
                    <?php if (!empty($sport) || !empty($city) || !empty($location) || !empty($date)): ?>
                        No grounds found for 
                        <?php if (!empty($sport)): ?>
                            <strong style="color:#7CCB96;"><?php echo htmlspecialchars($sport); ?></strong>
                        <?php endif; ?>
                        <?php if (!empty($city)): ?>
                            <?php if (!empty($sport)): ?> in <?php endif; ?>
                            <strong style="color:#7CCB96;"><?php echo htmlspecialchars($city); ?></strong>
                        <?php endif; ?>
                        <?php if (!empty($location)): ?>
                            <?php if (!empty($sport) || !empty($city)): ?> near <?php endif; ?>
                            <strong style="color:#7CCB96;"><?php echo htmlspecialchars($location); ?></strong>
                        <?php endif; ?>
                        <?php if (!empty($date)): ?>
                            on <strong style="color:#7CCB96;"><?php echo date('d M Y', strtotime($date)); ?></strong>
                        <?php endif; ?>
                    <?php else: ?>
                        No grounds available at the moment.
                    <?php endif; ?>
                </p>
                <a href="search.php" style="color:#7CCB96; text-decoration:none;">Clear all filters</a>
            </div>
        <?php endif; ?>
        </div>
    </div>

    <!-- ===== PLAYER IMAGES ===== -->
    <div class="player-image-left">
        <img src="players.png" alt="Player" />
    </div>
    <div class="player-image-right">
        <img src="players.png" alt="Player" />
    </div>

</div>

<!-- ===== FLATPICKR JS ===== -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
// ===== SIMPLE DATE PICKER - CLEAN & WORKING =====
flatpickr("#dateFilter", {
    dateFormat: "Y-m-d",
    allowInput: true,
    placeholder: "YYYY-MM-DD",
    disableMobile: true,
    
    // ===== FULL YEAR/MONTH NAVIGATION =====
    yearSelector: true,
    monthSelector: true,
    showMonths: 1,
    minYear: 1900,
    maxYear: 2100,
    prevArrow: "<",
    nextArrow: ">",
    
    // ===== AUTO SUBMIT ON DATE SELECT =====
    onChange: function(selectedDates, dateStr, instance) {
        if (dateStr) {
            document.getElementById('searchForm').submit();
        }
    }
});

// ===== LOCATION SEARCH WITH AJAX =====
let locationTimeout;
document.getElementById('locationInput').addEventListener('input', function() {
    clearTimeout(locationTimeout);
    locationTimeout = setTimeout(function() {
        updateResults();
    }, 300);
});

// ===== FILTER ICONS =====
document.querySelectorAll('.filter-icon').forEach(function(icon) {
    icon.addEventListener('click', function() {
        var sport = this.getAttribute('data-sport');
        var url = new URL(window.location.href);
        if (sport) {
            url.searchParams.set('sport', sport);
        } else {
            url.searchParams.delete('sport');
        }
        window.location.href = url.toString();
    });
});

// ===== AUTO-SUBMIT FOR SELECT =====
document.getElementById('sportFilter').addEventListener('change', function() {
    document.getElementById('searchForm').submit();
});
document.getElementById('cityFilter').addEventListener('change', function() {
    document.getElementById('searchForm').submit();
});

// ===== AJAX UPDATE FOR LOCATION =====
function updateResults() {
    var location = document.getElementById('locationInput').value;
    var currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('location', location);
    currentUrl.searchParams.delete('_ajax');

    fetch(currentUrl.toString(), {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        var parser = new DOMParser();
        var doc = parser.parseFromString(html, 'text/html');
        var newResults = doc.getElementById('groundResults');
        if (newResults) {
            document.getElementById('groundResults').innerHTML = newResults.innerHTML;
        }
        window.history.pushState({}, '', currentUrl.toString());
    })
    .catch(() => {
        document.getElementById('searchForm').submit();
    });
}
</script>

</body>
</html>