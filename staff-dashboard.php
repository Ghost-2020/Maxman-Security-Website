<?php
session_start();

// Check if user is logged in as admin and redirect to admin dashboard
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['role'])) {
    header('Location: index.php');
    exit();
}

// Redirect to admin dashboard if admin
if ($_SESSION['role'] === 'admin') {
    header('Location: admin-dashboard.php');
    exit();
}

// Include database connection
require_once 'php/includes/dbh.inc.php';

// Get staff information
$staff_name = $_SESSION['full_name'] ?? 'Staff Member';
$staff_role = $_SESSION['role'] ?? 'Staff';
$staff_email = $_SESSION['email'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - Maxman Security</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .dashboard-header { 
            background: linear-gradient(135deg, #001f3f 0%, #003d7a 100%); 
            color: #fff; 
            padding: 32px 0; 
            text-align: center; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .staff-info {
            background: rgba(255,255,255,0.1);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            backdrop-filter: blur(10px);
        }
        .dashboard-section { 
            margin: 32px 0; 
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            padding: 24px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 24px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 20px;
        }
        .stat-card h3 { font-size: 2.5rem; font-weight: bold; margin: 0; }
        .stat-card p { margin: 8px 0 0 0; opacity: 0.9; }
        .logout-btn { 
            position: absolute; 
            top: 24px; 
            right: 32px; 
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            transition: all 0.3s ease;
        }
        .logout-btn:hover {
            background: rgba(255,255,255,0.2);
            color: white;
            transform: translateY(-2px);
        }
        .table-responsive { 
            background: #fff; 
            border-radius: 12px; 
            box-shadow: 0 2px 12px rgba(0,0,0,0.07); 
            padding: 20px; 
            margin-top: 20px;
        }
        .alert-item {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 12px;
        }
        .alert-urgent {
            background: #f8d7da;
            border-color: #f5c6cb;
        }
        .section-title {
            color: #001f3f;
            border-bottom: 3px solid #007bff;
            padding-bottom: 8px;
            margin-bottom: 20px;
        }
        #popupNotification { 
            position: fixed; 
            top: 20px; 
            right: 20px; 
            z-index: 3000; 
            display: none;
            min-width: 300px;
        }
        .refresh-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .refresh-btn:hover {
            background: #0056b3;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="dashboard-header position-relative">
        <div class="container">
            <h1><i class="bi bi-shield-check"></i> Service Requests Dashboard</h1>
            <div class="staff-info">
                <h4>Welcome, <?php echo htmlspecialchars($staff_name); ?></h4>
                <p><i class="bi bi-person-badge"></i> Role: <?php echo htmlspecialchars($staff_role); ?></p>
                <p><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($staff_email); ?></p>
            </div>
            <button class="btn btn-outline-light logout-btn" id="logoutBtn">
                <i class="bi bi-box-arrow-right"></i> Logout
            </button>
        </div>
    </div>

    <div class="container">
        <div id="popupNotification" class="alert alert-info"></div>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <h3 id="totalRequests">-</h3>
                    <p><i class="bi bi-clipboard-data"></i> Service Requests</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h3 id="totalSubscribers">-</h3>
                    <p><i class="bi bi-envelope-heart"></i> Newsletter Subscribers</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h3 id="totalToday">-</h3>
                    <p><i class="bi bi-calendar-day"></i> Today's Activity</p>
                </div>
            </div>
        </div>

        <!-- Service Requests Section -->
        <div class="dashboard-section">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="section-title"><i class="bi bi-clipboard-data"></i> Service Requests</h3>
                <button class="refresh-btn" onclick="fetchDashboardData()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="serviceRequestsTable">
                    <thead class="table-dark">
                        <tr>
                            <th>Client Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Service Type</th>
                            <th>Guards</th>
                            <th>Date/Time</th>
                            <th>Message</th>
                            <th>Requested At</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody><!-- AJAX data --></tbody>
                </table>
            </div>
        </div>


        <!-- Newsletter Subscribers Section -->
        <div class="dashboard-section">
            <h3 class="section-title"><i class="bi bi-envelope-heart"></i> Newsletter Subscribers</h3>
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="newsletterTable">
                    <thead class="table-dark">
                        <tr>
                            <th>Email</th>
                            <th>Subscribed At</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody><!-- AJAX data --></tbody>
                </table>
            </div>
        </div>

    </div>

    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script>
    // Logout functionality
    $('#logoutBtn').on('click', function() {
        if (confirm('Are you sure you want to logout?')) {
            fetch('php/logout.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'index.php';
                } else {
                    showNotification('Logout failed. Please try again.', 'error');
                }
            })
            .catch(() => {
                // Silent error handling - redirect anyway
                window.location.href = 'index.php';
            });
        }
    });

    // Fetch dashboard data
    function fetchDashboardData() {
        // Fetch service requests
        $.get('php/fetch_service_requests.php', function(data) {
            let rows = '';
            if (data && data.length > 0) {
                data.forEach(function(r) {
                    const status = r.status || 'Pending';
                    const statusClass = status === 'Completed' ? 'success' : status === 'In Progress' ? 'warning' : 'secondary';
                    rows += `
                        <tr>
                            <td>${r.full_name || 'N/A'}</td>
                            <td>${r.email || 'N/A'}</td>
                            <td>${r.phone || 'N/A'}</td>
                            <td>${r.service_type || 'N/A'}</td>
                            <td>${r.num_guards || 'N/A'}</td>
                            <td>${r.service_date || 'N/A'}</td>
                            <td>${r.message || 'N/A'}</td>
                            <td>${r.requested_at || 'N/A'}</td>
                            <td><span class="badge bg-${statusClass}">${status}</span></td>
                        </tr>
                    `;
                });
            } else {
                rows = '<tr><td colspan="9" class="text-center">No service requests found</td></tr>';
            }
            $('#serviceRequestsTable tbody').html(rows);
            $('#totalRequests').text(data ? data.length : 0);
        }).fail(function() {
            $('#serviceRequestsTable tbody').html('<tr><td colspan="9" class="text-center text-danger">Error loading service requests</td></tr>');
        });

        // Fetch newsletter subscribers
        $.get('php/fetch_newsletter.php', function(data) {
            let rows = '';
            if (data && data.length > 0) {
                data.forEach(function(n) {
                    rows += `
                        <tr>
                            <td>${n.email || 'N/A'}</td>
                            <td>${n.subscribed_at || 'N/A'}</td>
                            <td><span class="badge bg-success">Active</span></td>
                        </tr>
                    `;
                });
            } else {
                rows = '<tr><td colspan="3" class="text-center">No newsletter subscribers found</td></tr>';
            }
            $('#newsletterTable tbody').html(rows);
            $('#totalSubscribers').text(data ? data.length : 0);
        }).fail(function() {
            $('#newsletterTable tbody').html('<tr><td colspan="3" class="text-center text-danger">Error loading newsletter data</td></tr>');
        });

        // Calculate today's activity (requests only)
        $.get('php/fetch_service_requests.php', function(requests) {
            const todayActivity = requests ? requests.length : 0;
            $('#totalToday').text(todayActivity);
        });
    }

    // Auto-refresh data every 30 seconds
    setInterval(fetchDashboardData, 30000);

    // Initial data load
    fetchDashboardData();

    // Show notification function
    function showNotification(message, type = 'info') {
        const notification = $('#popupNotification');
        notification.removeClass('alert-info alert-success alert-danger alert-warning')
                   .addClass(`alert-${type === 'error' ? 'danger' : type}`)
                   .text(message)
                   .fadeIn();
        setTimeout(function() { 
            notification.fadeOut(); 
        }, 4000);
    }

    // Listen for storage events (for real-time updates)
    window.addEventListener('storage', function(e) {
        if (e.key === 'newRequest' && e.newValue === '1') {
            showNotification('New service request received!', 'success');
            fetchDashboardData();
            localStorage.setItem('newRequest', '0');
        }
    });
    </script>
</body>
</html>
