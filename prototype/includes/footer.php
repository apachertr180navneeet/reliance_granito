<?php
// Reliance Granito EDMS - Common Footer Component
?>
    </div> <!-- /main-wrapper -->
  </div> <!-- /app-container -->

  <!-- Bootstrap 5 JavaScript Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- Core EDMS Scripts & Mock Data Store -->
  <script src="js/data.js"></script>
  <script src="js/app.js"></script>
  
  <!-- Sidebar Toggle Controller -->
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const toggleBtn = document.getElementById("sidebarToggleBtn");
      const sidebar = document.getElementById("appSidebar");
      const mainWrapper = document.querySelector(".main-wrapper");

      if (toggleBtn && sidebar) {
        toggleBtn.addEventListener("click", () => {
          sidebar.classList.toggle("collapsed");
          if (mainWrapper) {
            mainWrapper.classList.toggle("full-width");
          }
          // Store preference in localStorage
          const isCollapsed = sidebar.classList.contains("collapsed");
          localStorage.setItem("edms_sidebar_collapsed", isCollapsed ? "true" : "false");
        });

        // Restore saved preference if any
        if (localStorage.getItem("edms_sidebar_collapsed") === "true") {
          sidebar.classList.add("collapsed");
          if (mainWrapper) mainWrapper.classList.add("full-width");
        }
      }
    });
  </script>
</body>
</html>
