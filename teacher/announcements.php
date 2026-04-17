<?php
/**
 * ============================================================================
 *  Project     : College ERP System
 *  Author      : Vivek Kumar
 *  LinkedIn    : https://www.linkedin.com/in/vivek-info
 *  Instagram   : https://www.instagram.com/its.vivek.raj/
 * ============================================================================
 *  Copyright (c) 2026 Vivek Kumar. All Rights Reserved.
 *  This code is the intellectual property of Vivek Kumar.
 *  Unauthorized copying, distribution, or use of this project is strictly prohibited.
 * ============================================================================
 */

// Start session
session_start();

// Check if user is logged in and is teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../auth/login.php");
    exit;
}

// Set page title and include header
$pageTitle = "Announcements";
$basePath = "..";
include_once "../includes/header.php";
?>

<!-- Main content -->
<main class="main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-bullhorn me-2"></i>Announcements</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Filter Controls -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Filter by Priority</label>
                    <select class="form-select" id="priorityFilter">
                        <option value="">All Priorities</option>
                        <option value="urgent">Urgent</option>
                        <option value="normal">Normal</option>
                        <option value="info">Info</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Show</label>
                    <select class="form-select" id="limitFilter">
                        <option value="10">10 Announcements</option>
                        <option value="25">25 Announcements</option>
                        <option value="50" selected>50 Announcements</option>
                        <option value="100">100 Announcements</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button class="btn btn-primary w-100" onclick="loadAnnouncements()">
                        <i class="fas fa-filter me-2"></i>Apply Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Announcements List -->
    <div id="announcements-container">
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading announcements...</p>
        </div>
    </div>
</main>

<script>
    $(document).ready(function() {
        loadAnnouncements();
    });

    function loadAnnouncements() {
        const priority = $('#priorityFilter').val();
        const limit = $('#limitFilter').val();

        let url = '../api/teacher/announcements.php?limit=' + limit;
        if (priority) {
            url += '&priority=' + priority;
        }

        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayAnnouncements(response.data);
                } else {
                    showError(response.error || 'Failed to load announcements');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                showError('Failed to load announcements. Please try again.');
            }
        });
    }

    function displayAnnouncements(announcements) {
        let html = '';

        if (announcements.length === 0) {
            html = `
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-bullhorn fa-4x text-muted mb-3 opacity-25"></i>
                        <h5 class="text-muted">No Announcements Found</h5>
                        <p class="text-muted">There are no announcements available at this time.</p>
                    </div>
                </div>
            `;
        } else {
            announcements.forEach(function(announcement) {
                // Priority badge
                let priorityClass = 'primary';
                let priorityIcon = 'info-circle';
                if (announcement.priority === 'urgent') {
                    priorityClass = 'danger';
                    priorityIcon = 'exclamation-circle';
                } else if (announcement.priority === 'info') {
                    priorityClass = 'info';
                    priorityIcon = 'bell';
                }

                // Status badge
                let statusBadge = announcement.is_active == 1 ?
                    '<span class="badge bg-success">Active</span>' :
                    '<span class="badge bg-secondary">Inactive</span>';

                // Format dates
                let createdDate = formatDate(announcement.created_at);
                let validFrom = formatDate(announcement.valid_from);
                let validUntil = announcement.valid_until ? formatDate(announcement.valid_until) : 'No Expiry';

                // Attachment
                let attachmentHtml = '';
                if (announcement.attachment_name) {
                    attachmentHtml = `
                        <div class="mt-3">
                            <a href="../uploads/announcements/${announcement.attachment_path}" 
                               class="btn btn-sm btn-outline-primary" download>
                                <i class="fas fa-paperclip me-1"></i>${announcement.attachment_name}
                            </a>
                        </div>
                    `;
                }

                html += `
                    <div class="card mb-3 shadow-sm announcement-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h5 class="card-title mb-1">
                                        <i class="fas fa-${priorityIcon} text-${priorityClass} me-2"></i>
                                        ${announcement.title}
                                    </h5>
                                    <div class="mb-2">
                                        <span class="badge bg-${priorityClass} me-2">${announcement.priority.toUpperCase()}</span>
                                        ${statusBadge}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="announcement-message mb-3">
                                ${announcement.message}
                            </div>

                            ${attachmentHtml}

                            <div class="row text-muted small mt-3 pt-3 border-top">
                                <div class="col-md-4">
                                    <i class="fas fa-user me-1"></i>
                                    Posted by: <strong>${announcement.created_by_name || 'Admin'}</strong>
                                </div>
                                <div class="col-md-4">
                                    <i class="fas fa-calendar me-1"></i>
                                    Valid: ${validFrom} - ${validUntil}
                                </div>
                                <div class="col-md-4 text-end">
                                    <i class="far fa-clock me-1"></i>
                                    Posted: ${createdDate}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
        }

        $('#announcements-container').html(html);
    }

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}-${month}-${year}`;
    }

    function showError(message) {
        const html = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('#announcements-container').html(html);
    }
</script>

<style>
    .announcement-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .announcement-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15) !important;
    }

    .announcement-message {
        line-height: 1.6;
    }

    .announcement-message h1,
    .announcement-message h2,
    .announcement-message h3,
    .announcement-message h4 {
        margin-top: 1rem;
        margin-bottom: 0.5rem;
    }

    .announcement-message ul,
    .announcement-message ol {
        padding-left: 1.5rem;
    }
</style>

<?php
// Include footer
include_once "../includes/footer.php";
?>