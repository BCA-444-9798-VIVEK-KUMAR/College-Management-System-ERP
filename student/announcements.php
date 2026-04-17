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

session_start();

// Check if user is logged in and is student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

$pageTitle = "Announcements";
$basePath = "..";
include_once "../includes/header.php";
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<main class="main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-bullhorn me-2"></i> Announcements</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshBtn">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <select class="form-select" id="priorityFilter">
                        <option value="">All Priorities</option>
                        <option value="urgent">Urgent Only</option>
                        <option value="normal">Normal</option>
                        <option value="info">Info</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select class="form-select" id="readFilter">
                        <option value="">All</option>
                        <option value="unread">Unread Only</option>
                        <option value="read">Read</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-primary w-100" id="markAllReadBtn">
                        <i class="fas fa-check-double me-2"></i> Mark All as Read
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Announcements List -->
    <div id="announcementsList">
        <div class="text-center py-5">
            <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
            <p class="mt-3">Loading announcements...</p>
        </div>
    </div>
</main>

<script>
    $(document).ready(function() {
        let allAnnouncements = [];

        // Load announcements on page load
        loadAnnouncements();

        // Filter change handlers
        $('#priorityFilter, #readFilter').change(function() {
            displayFilteredAnnouncements();
        });

        // Refresh button
        $('#refreshBtn').click(function() {
            loadAnnouncements();
        });

        // Mark all as read
        $('#markAllReadBtn').click(function() {
            markAllAsRead();
        });

        // Load announcements from API
        function loadAnnouncements() {
            $.ajax({
                url: '../api/student/announcements.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        allAnnouncements = response.data;
                        updateUnreadBadge(response.unread_count);
                        displayFilteredAnnouncements();
                    } else {
                        $('#announcementsList').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> ${response.error}
                        </div>
                    `);
                    }
                },
                error: function() {
                    $('#announcementsList').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> Failed to load announcements
                    </div>
                `);
                }
            });
        }

        // Display filtered announcements
        function displayFilteredAnnouncements() {
            const priorityFilter = $('#priorityFilter').val();
            const readFilter = $('#readFilter').val();

            let filtered = allAnnouncements.filter(function(ann) {
                if (priorityFilter && ann.priority !== priorityFilter) return false;
                if (readFilter === 'unread' && ann.is_read == 1) return false;
                if (readFilter === 'read' && ann.is_read == 0) return false;
                return true;
            });

            if (filtered.length === 0) {
                $('#announcementsList').html(`
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No announcements to display</p>
                    </div>
                </div>
            `);
                return;
            }

            let html = '';
            filtered.forEach(function(ann) {
                const priorityConfig = {
                    'urgent': {
                        badge: 'bg-danger',
                        icon: 'exclamation-circle'
                    },
                    'normal': {
                        badge: 'bg-primary',
                        icon: 'info-circle'
                    },
                    'info': {
                        badge: 'bg-info',
                        icon: 'info'
                    }
                } [ann.priority];

                const isUnread = ann.is_read == 0;
                const cardClass = isUnread ? 'border-primary shadow-sm' : '';

                html += `
                <div class="card mb-3 ${cardClass}" data-announcement-id="${ann.id}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 class="card-title mb-2">
                                ${isUnread ? '<span class="badge bg-success me-2">NEW</span>' : ''}
                                <i class="fas fa-${priorityConfig.icon} me-2"></i>
                                ${ann.title}
                            </h5>
                            <span class="badge ${priorityConfig.badge}">${ann.priority.toUpperCase()}</span>
                        </div>
                        
                        <div class="card-text announcement-content mb-3">
                            ${ann.message}
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                <i class="fas fa-calendar me-1"></i> ${formatDate(ann.valid_from)}
                                ${ann.valid_until ? ' - ' + formatDate(ann.valid_until) : ''}
                                <span class="ms-3">
                                    <i class="fas fa-user me-1"></i> ${ann.created_by_name || 'Admin'}
                                </span>
                            </div>
                            ${isUnread ? `
                                <button class="btn btn-sm btn-primary mark-read-btn" data-id="${ann.id}">
                                    <i class="fas fa-check me-1"></i> Mark as Read
                                </button>
                            ` : `
                                <span class="text-success small">
                                    <i class="fas fa-check-double me-1"></i> Read on ${formatDateTime(ann.read_at)}
                                </span>
                            `}
                        </div>
                        
                        ${ann.attachment_path ? `
                            <div class="mt-3">
                                <a href="../${ann.attachment_path}" class="btn btn-sm btn-outline-secondary" download="${ann.attachment_name}">
                                    <i class="fas fa-paperclip me-1"></i> Download Attachment
                                </a>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
            });

            $('#announcementsList').html(html);

            // Attach mark as read handlers
            $('.mark-read-btn').click(function() {
                const announcementId = $(this).data('id');
                markAsRead(announcementId);
            });
        }

        // Mark single announcement as read
        function markAsRead(announcementId) {
            $.ajax({
                url: '../api/student/mark_announcement_read.php',
                method: 'POST',
                data: JSON.stringify({
                    announcement_id: announcementId
                }),
                contentType: 'application/json',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Update local data
                        const ann = allAnnouncements.find(a => a.id == announcementId);
                        if (ann) {
                            ann.is_read = 1;
                            ann.read_at = new Date().toISOString();
                        }

                        // Update unread count
                        const unreadCount = allAnnouncements.filter(a => a.is_read == 0).length;
                        updateUnreadBadge(unreadCount);

                        // Refresh display
                        displayFilteredAnnouncements();

                        // Show toast
                        Swal.fire({
                            icon: 'success',
                            title: 'Marked as read',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    }
                }
            });
        }

        // Mark all announcements as read
        function markAllAsRead() {
            const unreadAnnouncements = allAnnouncements.filter(a => a.is_read == 0);

            if (unreadAnnouncements.length === 0) {
                Swal.fire('Info', 'All announcements are already marked as read', 'info');
                return;
            }

            Swal.fire({
                title: 'Mark All as Read?',
                text: `Mark ${unreadAnnouncements.length} announcement(s) as read?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, mark all'
            }).then((result) => {
                if (result.isConfirmed) {
                    let completed = 0;

                    Swal.fire({
                        title: 'Processing...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    unreadAnnouncements.forEach(function(ann) {
                        $.ajax({
                            url: '../api/student/mark_announcement_read.php',
                            method: 'POST',
                            data: JSON.stringify({
                                announcement_id: ann.id
                            }),
                            contentType: 'application/json',
                            dataType: 'json',
                            success: function() {
                                ann.is_read = 1;
                                ann.read_at = new Date().toISOString();
                                completed++;

                                if (completed === unreadAnnouncements.length) {
                                    Swal.fire('Success', 'All announcements marked as read', 'success');
                                    updateUnreadBadge(0);
                                    displayFilteredAnnouncements();
                                }
                            }
                        });
                    });
                }
            });
        }

        // Update unread badge in navigation (if exists)
        function updateUnreadBadge(count) {
            const badge = $('#announcementBadge');
            if (badge.length) {
                if (count > 0) {
                    badge.text(count).show();
                } else {
                    badge.hide();
                }
            }
        }

        // Format date
        function formatDate(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            return d.toLocaleDateString('en-GB', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });
        }

        // Format datetime
        function formatDateTime(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            return d.toLocaleString('en-GB', {
                day: 'numeric',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    });
</script>

<style>
    .announcement-content {
        line-height: 1.6;
    }

    .announcement-content p {
        margin-bottom: 0.5rem;
    }

    .card.border-primary {
        border-width: 2px !important;
    }
</style>

<?php include_once "../includes/footer.php"; ?>