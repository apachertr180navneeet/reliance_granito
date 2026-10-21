<?php
// Reliance Granito EDMS - Common Footer Component
?>
      <!-- Professional Enterprise Footer -->
      <footer class="app-footer">
        <div class="d-flex align-items-center gap-2">
          <span>&copy; <?= date('Y') ?> <strong>Reliance Granito Private Limited</strong></span>
          <span class="d-none d-md-inline">&bull; Export Documentation Management System (EDMS)</span>
        </div>

        <div class="d-flex align-items-center gap-3">
          <span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2 d-none d-sm-inline-flex align-items-center">
            <i class="bi bi-shield-check me-1"></i> ICEGATE EDI &amp; DGFT Verified
          </span>
          <span class="d-none d-lg-inline text-muted" style="font-size:0.75rem;">
            Facility: Morbi, Gujarat &bull; Jodhpur Export HQ
          </span>
          <a href="login.php" class="text-danger d-inline-flex align-items-center gap-1" style="font-weight:600; font-size:0.76rem;">
            <i class="bi bi-box-arrow-right"></i> Sign Out
          </a>
        </div>
      </footer>
    </div> <!-- /main-wrapper -->
  </div> <!-- /app-container -->

  <!-- Bootstrap 5 JavaScript Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- Core EDMS Scripts & Mock Data Store -->
  <script src="js/data.js"></script>
  <script src="js/app.js"></script>
</body>
</html>
