<?php
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Include database connection
require_once 'php/includes/dbh.inc.php';

// Get admin information
$admin_name = $_SESSION['full_name'] ?? 'Admin';
$admin_email = $_SESSION['email'] ?? '';

// Fetch statistics
try {
    // Total service requests
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM service_requests");
    $totalRequests = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Pending requests
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM service_requests WHERE status = 'pending'");
    $pendingRequests = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Completed requests
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM service_requests WHERE status = 'completed'");
    $completedRequests = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total subscribers
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM newsletter_subscribers WHERE is_active = 1");
    $totalSubscribers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Today's requests
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM service_requests WHERE DATE(requested_at) = CURDATE()");
    $todayRequests = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // This week's requests
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM service_requests WHERE WEEK(requested_at) = WEEK(NOW())");
    $weekRequests = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // This month's requests
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM service_requests WHERE MONTH(requested_at) = MONTH(NOW()) AND YEAR(requested_at) = YEAR(NOW())");
    $monthRequests = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
} catch(PDOException $e) {
    error_log("Dashboard stats error: " . $e->getMessage());
    $totalRequests = $pendingRequests = $completedRequests = $totalSubscribers = $todayRequests = $weekRequests = $monthRequests = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Maxman Security</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #001f3f 0%, #003d7a 100%);
            --success-gradient: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            --warning-gradient: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            --info-gradient: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            --danger-gradient: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        }
        
        body {
            background: #f5f7fa;
            font-family: 'Inter', sans-serif;
        }
        
        .dashboard-header {
            background: var(--primary-gradient);
            color: #fff;
            padding: 2rem 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            margin-bottom: 2rem;
        }
        
        .admin-info {
            background: rgba(255,255,255,0.1);
            padding: 1.5rem;
            border-radius: 12px;
            backdrop-filter: blur(10px);
        }
        
        .stat-card {
            background: #fff;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            border-left: 4px solid;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }
        
        .stat-card.primary { border-left-color: #001f3f; }
        .stat-card.success { border-left-color: #28a745; }
        .stat-card.warning { border-left-color: #ffc107; }
        .stat-card.info { border-left-color: #17a2b8; }
        
        .stat-card .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .stat-card.primary .stat-icon { background: rgba(0, 31, 63, 0.1); color: #001f3f; }
        .stat-card.success .stat-icon { background: rgba(40, 167, 69, 0.1); color: #28a745; }
        .stat-card.warning .stat-icon { background: rgba(255, 193, 7, 0.1); color: #ffc107; }
        .stat-card.info .stat-icon { background: rgba(23, 162, 184, 0.1); color: #17a2b8; }
        
        .stat-card h3 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0.5rem 0;
            color: #2c3e50;
        }
        
        .stat-card p {
            color: #6c757d;
            margin: 0;
            font-size: 0.95rem;
        }
        
        .dashboard-section {
            background: #fff;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }
        
        .section-title {
            color: #001f3f;
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 3px solid #007bff;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .table-responsive {
            border-radius: 12px;
            overflow: hidden;
        }
        
        .table thead {
            background: var(--primary-gradient);
            color: #fff;
        }
        
        .table tbody tr {
            transition: background 0.2s ease;
        }
        
        .table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 0.5rem 0.75rem;
            font-weight: 500;
            border-radius: 6px;
        }
        
        .logout-btn {
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
        
        .refresh-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .refresh-btn:hover {
            background: #0056b3;
            transform: translateY(-1px);
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
        }
        
        #notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 3000;
            min-width: 300px;
            display: none;
        }
        
        .chart-container {
            height: 300px;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-0"><i class="bi bi-shield-check"></i> Admin Dashboard</h1>
                    <div class="admin-info mt-3">
                        <h5 class="mb-1">Welcome, <?php echo htmlspecialchars($admin_name); ?></h5>
                        <p class="mb-0"><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($admin_email); ?></p>
                    </div>
                </div>
                <div>
                    <a href="index.php" class="btn btn-outline-light me-2">
                        <i class="bi bi-house"></i> Home
                    </a>
                    <button class="btn logout-btn" id="logoutBtn">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div id="notification" class="alert"></div>
        
        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="stat-card primary">
                    <div class="stat-icon">
                        <i class="bi bi-clipboard-data"></i>
                    </div>
                    <h3 id="totalRequests"><?php echo $totalRequests; ?></h3>
                    <p>Total Service Requests</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card warning">
                    <div class="stat-icon">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <h3 id="pendingRequests"><?php echo $pendingRequests; ?></h3>
                    <p>Pending Requests</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card success">
                    <div class="stat-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <h3 id="completedRequests"><?php echo $completedRequests; ?></h3>
                    <p>Completed Requests</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card info">
                    <div class="stat-icon">
                        <i class="bi bi-envelope-heart"></i>
                    </div>
                    <h3 id="totalSubscribers"><?php echo $totalSubscribers; ?></h3>
                    <p>Newsletter Subscribers</p>
                </div>
            </div>
        </div>

        <!-- Activity Overview -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="stat-card info">
                    <div class="stat-icon">
                        <i class="bi bi-calendar-day"></i>
                    </div>
                    <h3><?php echo $todayRequests; ?></h3>
                    <p>Today's Requests</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card info">
                    <div class="stat-icon">
                        <i class="bi bi-calendar-week"></i>
                    </div>
                    <h3><?php echo $weekRequests; ?></h3>
                    <p>This Week's Requests</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card info">
                    <div class="stat-icon">
                        <i class="bi bi-calendar-month"></i>
                    </div>
                    <h3><?php echo $monthRequests; ?></h3>
                    <p>This Month's Requests</p>
                </div>
            </div>
        </div>

        <!-- Service Requests Section -->
        <div class="dashboard-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="section-title mb-0">
                    <i class="bi bi-clipboard-data"></i> Service Requests
                </h3>
                <button class="btn refresh-btn" onclick="fetchDashboardData()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover" id="serviceRequestsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Service Type</th>
                            <th>Guards</th>
                            <th>Date/Time</th>
                            <th>Status</th>
                            <th>Requested At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="10" class="text-center">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Newsletter Subscribers Section -->
        <div class="dashboard-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="section-title mb-0">
                    <i class="bi bi-envelope-heart"></i> Newsletter Subscribers
                </h3>
                <button class="btn refresh-btn" onclick="fetchNewsletterData()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover" id="newsletterTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Subscribed At</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="4" class="text-center">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script>
    // Helper function to escape HTML to prevent XSS
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, m => map[m]);
    }
    
    // Logout functionality
    $('#logoutBtn').on('click', function() {
        if (confirm('Are you sure you want to logout?')) {
            fetch('php/logout.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                window.location.href = 'index.php';
            })
            .catch(() => {
                // Silent error handling - redirect anyway
                window.location.href = 'index.php';
            });
        }
    });

    // Show notification
    function showNotification(message, type = 'info') {
        const notification = $('#notification');
        const iconClass = type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle';
        notification.removeClass('alert-info alert-success alert-danger alert-warning')
                   .addClass(`alert alert-${type === 'error' ? 'danger' : type}`)
                   .html(`<i class="bi bi-${iconClass}"></i> ${escapeHtml(message)}`)
                   .fadeIn();
        setTimeout(() => notification.fadeOut(), 4000);
    }

    // Fetch service requests
    function fetchDashboardData() {
        $.ajax({
            url: 'php/fetch_service_requests.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                let rows = '';
                if (data && Array.isArray(data) && data.length > 0) {
                    data.forEach(function(r) {
                        const status = (r.status || 'pending').toString();
                        const statusClass = status === 'completed' ? 'success' : 
                                          status === 'approved' ? 'info' : 
                                          status === 'rejected' ? 'danger' : 'warning';
                        const statusText = status.length > 0 ? status.charAt(0).toUpperCase() + status.slice(1) : 'Pending';
                        
                        rows += `
                            <tr>
                                <td>#${r.id}</td>
                                <td>${escapeHtml(r.full_name || 'N/A')}</td>
                                <td>${escapeHtml(r.email || 'N/A')}</td>
                                <td>${escapeHtml(r.phone || 'N/A')}</td>
                                <td>${escapeHtml(r.service_type || 'N/A')}</td>
                                <td>${r.num_guards || 'N/A'}</td>
                                <td>${r.service_date ? (() => { try { return new Date(r.service_date).toLocaleString(); } catch(e) { return escapeHtml(r.service_date); } })() : 'N/A'}</td>
                                <td><span class="badge bg-${statusClass}">${escapeHtml(statusText)}</span></td>
                                <td>${r.requested_at ? (() => { try { return new Date(r.requested_at).toLocaleString(); } catch(e) { return escapeHtml(r.requested_at); } })() : 'N/A'}</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-primary" onclick="viewRequest(${r.id})" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        ${status === 'pending' ? `
                                            <button class="btn btn-sm btn-success" onclick="updateStatus(${r.id}, 'approved')" title="Approve">
                                                <i class="bi bi-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="updateStatus(${r.id}, 'rejected')" title="Reject">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        ` : ''}
                                        ${status === 'approved' ? `
                                            <button class="btn btn-sm btn-success" onclick="updateStatus(${r.id}, 'completed')" title="Mark Complete">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                        ` : ''}
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    rows = '<tr><td colspan="10" class="text-center">No service requests found</td></tr>';
                }
                $('#serviceRequestsTable tbody').html(rows);
            },
            error: function() {
                $('#serviceRequestsTable tbody').html('<tr><td colspan="10" class="text-center text-danger">Error loading service requests</td></tr>');
            }
        });
    }

    // Fetch newsletter subscribers
    function fetchNewsletterData() {
        $.ajax({
            url: 'php/fetch_newsletter.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                let rows = '';
                if (data && Array.isArray(data) && data.length > 0) {
                    data.forEach(function(n) {
                        rows += `
                            <tr>
                                <td>#${n.id}</td>
                                <td>${escapeHtml(n.email || 'N/A')}</td>
                                <td>${n.subscribed_at ? (() => { try { return new Date(n.subscribed_at).toLocaleString(); } catch(e) { return escapeHtml(n.subscribed_at); } })() : 'N/A'}</td>
                                <td><span class="badge bg-success">Active</span></td>
                            </tr>
                        `;
                    });
                } else {
                    rows = '<tr><td colspan="4" class="text-center">No newsletter subscribers found</td></tr>';
                }
                $('#newsletterTable tbody').html(rows);
            },
            error: function() {
                $('#newsletterTable tbody').html('<tr><td colspan="4" class="text-center text-danger">Error loading newsletter data</td></tr>');
            }
        });
    }

    // Update request status
    function updateStatus(requestId, newStatus) {
        if (confirm(`Are you sure you want to ${newStatus} this request?`)) {
            $.ajax({
                url: 'php/update_request_status.php',
                method: 'POST',
                data: {
                    id: requestId,
                    status: newStatus
                },
                success: function(response) {
                    try {
                        const data = typeof response === 'string' ? JSON.parse(response) : response;
                        if (data && data.success) {
                            showNotification(`Request ${newStatus} successfully!`, 'success');
                            fetchDashboardData();
                        } else {
                            showNotification(data && data.message ? data.message : 'Failed to update status', 'error');
                        }
                    } catch (e) {
                        showNotification('Error processing response', 'error');
                    }
                },
                error: function() {
                    showNotification('Error updating request status', 'error');
                }
            });
        }
    }

    // View request details
    function viewRequest(requestId) {
        // Fetch and display request details in a modal
        $.ajax({
            url: 'php/fetch_service_requests.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                if (!data || !Array.isArray(data)) {
                    showNotification('Error loading request details', 'error');
                    return;
                }
                const request = data.find(r => r.id == requestId);
                if (request) {
                    const modal = `
                        <div class="modal fade" id="requestModal" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Request Details #${request.id}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p><strong>Name:</strong> ${escapeHtml(request.full_name || 'N/A')}</p>
                                        <p><strong>Email:</strong> ${escapeHtml(request.email || 'N/A')}</p>
                                        <p><strong>Phone:</strong> ${escapeHtml(request.phone || 'N/A')}</p>
                                        <p><strong>Service Type:</strong> ${escapeHtml(request.service_type || 'N/A')}</p>
                                        <p><strong>Number of Guards:</strong> ${request.num_guards || 'N/A'}</p>
                                        <p><strong>Preferred Date/Time:</strong> ${request.service_date ? escapeHtml(request.service_date) : 'Not specified'}</p>
                                        <p><strong>Message:</strong> ${escapeHtml(request.message || 'N/A')}</p>
                                        <p><strong>Status:</strong> <span class="badge bg-${request.status === 'completed' ? 'success' : request.status === 'approved' ? 'info' : request.status === 'rejected' ? 'danger' : 'warning'}">${request.status}</span></p>
                                        <p><strong>Requested At:</strong> ${request.requested_at ? (() => { try { return new Date(request.requested_at).toLocaleString(); } catch(e) { return escapeHtml(request.requested_at); } })() : 'N/A'}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    $('body').append(modal);
                    const bsModal = new bootstrap.Modal(document.getElementById('requestModal'));
                    bsModal.show();
                    $('#requestModal').on('hidden.bs.modal', function() {
                        $(this).remove();
                    });
                }
            }
        });
    }

    // Auto-refresh every 30 seconds
    setInterval(fetchDashboardData, 30000);
    setInterval(fetchNewsletterData, 30000);

    // Initial load
    fetchDashboardData();
    fetchNewsletterData();
    </script>
</body>
</html>

