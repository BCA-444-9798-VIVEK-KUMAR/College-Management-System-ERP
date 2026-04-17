<!-- Footer content -->
<footer class="footer mt-auto py-4 bg-dark text-light">
    <div class="container-fluid px-3" style="transition: margin-left 0.3s;">
        <style>
            @media (min-width: 768px) {
                .footer .container-fluid {
                    margin-left: 250px;
                    max-width: calc(100% - 250px);
                    padding-left: 2rem;
                    padding-right: 3rem;
                }
            }
        </style>
        <div class="row g-3">
            <!-- Copyright Section -->
            <div class="col-12 col-md-6 text-center text-md-start ps-md-4">
                <h6 class="mb-2 fw-bold">College Management System (ERP)</h6>
                <p class="mb-1 small">© <?php echo date('Y'); ?> All Rights Reserved</p>
                <p class="mb-0 small text-muted">Version 4.0.1</p>
            </div>
            
            <!-- Legal Notice Section -->
            <div class="col-12 col-md-6 text-center text-md-end">
                <p class="mb-1 small">
                    <span class="badge bg-danger me-1">⚠</span>
                    <strong>Proprietary Software</strong>
                </p>
                <p class="mb-2 small text-muted" style="font-size: 0.8rem;">
                    This software is protected by copyright law.<br class="d-none d-md-inline">
                    Unauthorized use, reproduction, or distribution is strictly prohibited.
                </p>
                <p class="mb-0 small">
                    <a href="https://www.instagram.com/its.vivek.raj/" class="text-light text-decoration-none me-2" target="_blank" rel="noopener">
                        <i class="bi bi-code-slash"></i> Developed by Vivek Kumar
                    </a>
                </p>
            </div>
        </div>
        
        <!-- Copyright Notice Link -->
        <div class="row mt-3 pt-3 border-top border-secondary">
            <div class="col-12 text-center">
                <p class="mb-0 small">
                    <i class="bi bi-shield-lock-fill text-warning"></i>
                    <a href="#" class="text-light text-decoration-none" data-bs-toggle="modal" data-bs-target="#copyrightModal">
                        <strong>View Copyright Notice <!--& Legal Terms--></strong>
                    </a>
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Copyright Modal -->
<div class="modal fade" id="copyrightModal" tabindex="-1" aria-labelledby="copyrightModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="copyrightModalLabel">
                    <i class="bi bi-shield-lock-fill text-warning me-2"></i>
                    Copyright Notice <!--& Legal Terms-->
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" role="alert">
                    <h6 class="alert-heading">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Proprietary Software - Unauthorized Use Prohibited
                    </h6>
                </div>
                
                <h6 class="text-warning mb-3">Copyright Declaration</h6>
                <p class="mb-3">
                    This system and its source code, including but not limited to all files, databases, designs, documentation, 
                    and associated intellectual property, are the <strong>exclusive property of 
                    <a href="https://www.linkedin.com/in/vivek-info/" class="text-info" target="_blank" rel="noopener">Vivek Kumar</a></strong>.
                </p>
                
                <h6 class="text-warning mb-3">Restrictions</h6>
                <p class="mb-3">
                    No part of this software, including but not limited to:
                </p>
                <ul class="mb-3">
                    <li>Source code (PHP, JavaScript, SQL, HTML, CSS)</li>
                    <li>Database schemas and structures</li>
                    <li>UI/UX designs and templates</li>
                    <li>Documentation and technical specifications</li>
                    <li>Business logic and algorithms</li>
                </ul>
                <p class="mb-3">
                    May be <strong>copied, modified, distributed, sublicensed, sold, or used</strong> for any commercial 
                    or non-commercial purpose without explicit written permission from the copyright holder.
                </p>
                <!-- Legal Consequences
                <h6 class="text-warning mb-3">Legal Consequences</h6>
                <p class="mb-3">
                    Violators will be subject to:
                </p>
                <ul class="mb-3">
                    <li>Civil litigation for damages and injunctive relief</li>
                    <li>Criminal prosecution under applicable copyright laws</li>
                    <li>Statutory damages and attorney's fees</li>
                </ul>
				-->                

                <h6 class="text-warning mb-3">Contact for Licensing</h6>
                <p class="mb-0">
                    For licensing inquiries or permission requests, please contact:<br>
                    <a href="https://www.linkedin.com/in/vivek-info/" class="text-info" target="_blank" rel="noopener">Vivek Kumar</a> | 
                    <a href="https://www.instagram.com/its.vivek.raj/" class="text-info" target="_blank" rel="noopener">@its.vivek.raj</a>
                </p>
            </div>
            <div class="modal-footer border-secondary">
                <small class="text-muted me-auto">© <?php echo date('Y'); ?> Vivek Kumar. All Rights Reserved.</small>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Custom JS -->
<script src="<?php echo isset($basePath) ? $basePath : ""; ?>/assets/js/main.js"></script>
</body>

</html>