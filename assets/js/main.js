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
/**
 * College Attendance Management System
 * Main JavaScript - Sidebar Toggle & Animations
 */

$(document).ready(function() {

    // ==================== Sidebar Toggle (Mobile) ====================
    $('#sidebarToggle, .sidebar-toggle').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('#sidebar').toggleClass('active');
        $('#sidebarOverlay, .sidebar-overlay').toggleClass('active');
        $('body').toggleClass('sidebar-open');
    });

    // Close sidebar when clicking overlay
    $('#sidebarOverlay, .sidebar-overlay').on('click', function() {
        $('#sidebar').removeClass('active');
        $('#sidebarOverlay, .sidebar-overlay').removeClass('active');
        $('body').removeClass('sidebar-open');
    });

    // Close sidebar when clicking a link on mobile
    $('#sidebar .nav-link').on('click', function() {
        if ($(window).width() < 992) {
            setTimeout(function() {
                $('#sidebar').removeClass('active');
                $('#sidebarOverlay, .sidebar-overlay').removeClass('active');
                $('body').removeClass('sidebar-open');
            }, 200);
        }
    });

    // ==================== Active Link Highlighting ====================
    // Get current page filename
    var currentPage = window.location.pathname.split('/').pop();
    
    // Remove active class from all links
    $('#sidebar .nav-link').removeClass('active');
    
    // Add active class to current page link
    $('#sidebar .nav-link').each(function() {
        var linkHref = $(this).attr('href');
        if (linkHref) {
            var linkPage = linkHref.split('/').pop();
            if (linkPage === currentPage) {
                $(this).addClass('active');
            }
        }
    });

    // If no active link found, activate dashboard
    if ($('#sidebar .nav-link.active').length === 0) {
        $('#sidebar .nav-link[href*="dashboard"]').first().addClass('active');
    }

    // ==================== Tooltips & Popovers ====================
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize Bootstrap popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // ==================== Alert Auto-hide ====================
    // Auto-hide alerts after 5 seconds
    $('.alert:not(.alert-permanent)').each(function() {
        var alert = $(this);
        setTimeout(function() {
            alert.fadeOut(500, function() {
                $(this).remove();
            });
        }, 5000);
    });

    // ==================== Confirm Dialogs ====================
    // Confirmation for delete actions
    $('.btn-delete, .delete-btn, [data-action="delete"]').on('click', function(e) {
        if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
            e.preventDefault();
            return false;
        }
    });

    // ==================== Print Functionality ====================
    $('.btn-print').on('click', function(e) {
        e.preventDefault();
        window.print();
    });

    // ==================== Window Resize Handler ====================
    $(window).on('resize', function() {
        // Close mobile sidebar on desktop resize
        if ($(window).width() >= 992) {
            $('#sidebar').removeClass('active');
            $('#sidebarOverlay, .sidebar-overlay').removeClass('active');
            $('body').removeClass('sidebar-open');
        }

        // Resize charts if they exist
        if (typeof Chart !== 'undefined') {
            Chart.helpers.each(Chart.instances, function(instance) {
                instance.resize();
            });
        }
    });
});

// ==================== Global Utility Functions ====================

/**
 * Show loading state on button
 */
function showButtonLoading(button, text = 'Loading...') {
    var $btn = $(button);
    $btn.data('original-text', $btn.html());
    $btn.prop('disabled', true);
    $btn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' + text);
}

/**
 * Hide loading state on button
 */
function hideButtonLoading(button) {
    var $btn = $(button);
    $btn.prop('disabled', false);
    $btn.html($btn.data('original-text'));
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    var bgClass = 'bg-' + type;
    var toastHtml = `
        <div class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    // Create toast container if it doesn't exist
    if ($('#toast-container').length === 0) {
        $('body').append('<div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 11"></div>');
    }

    var $toast = $(toastHtml);
    $('#toast-container').append($toast);
    
    var toast = new bootstrap.Toast($toast[0], {
        autohide: true,
        delay: 3000
    });
    toast.show();
    
    // Remove toast after it's hidden
    $toast.on('hidden.bs.toast', function() {
        $(this).remove();
    });
}

/**
 * Animate counter from 0 to target value
 */
function animateCounter(element, target, duration = 1000, suffix = '') {
    var $element = $(element);
    var start = 0;
    var current = start;
    var increment = target / (duration / 16);
        
    var timer = setInterval(function() {
        current += increment;
        if (current >= target) {
            current = target;
            clearInterval(timer);
        }
        $element.text(Math.floor(current) + suffix);
    }, 16);
}
