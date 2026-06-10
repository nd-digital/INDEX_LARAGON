<!-- Dashboard scripts (modal iframes, offcanvas) — included from Header.php. -->
    <!-- Modal iframe scripts (lazy load) -->
    <script>
      // phpinfo: load on show, clear on hide
      document.getElementById('phpinfoModal').addEventListener('show.bs.modal', function () {
        document.getElementById('phpinfoFrame').src = '/?q=info';
      });
      document.getElementById('phpinfoModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('phpinfoFrame').src = '';
      });

      // Adminer: load on show, clear on hide
      document.getElementById('adminerModal').addEventListener('show.bs.modal', function () {
        document.getElementById('adminerFrame').src = './INDEX_LARAGON/Tools/Adminer/';
      });
      document.getElementById('adminerModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('adminerFrame').src = '';
      });

      // Help: static multilingual content rendered server-side (see Modals.php)

      // Close the offcanvas when a modal opens
      document.querySelectorAll('#offcanvasTools .offcanvas-tools-link').forEach(function(link) {
        link.addEventListener('click', function () {
          var offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('offcanvasTools'));
          if (offcanvas) offcanvas.hide();
        });
      });
    </script>
