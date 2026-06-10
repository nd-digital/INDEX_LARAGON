  <aside class="sidebar" id="sidebar">
    <header>
      <?php echo __('sidebar.header'); ?>
    </header>
    <nav class="sidebar-nav" aria-label="<?php echo __('sidebar.header'); ?>">
      <ul>
        <?php
        include './INDEX_LARAGON/Menu/Sub_Menu_Laragon.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Accessibility.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Actuality_Info.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Agile.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Apache.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_API.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Appointment_Test.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Backup.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_CMS_ERP_Etc.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Code_Control.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Code_Editor.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Coding.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Composer.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Concept.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Cours_Certifications.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Css.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Database.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Debug.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Design_Tools_Help.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Diagram.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Diploma_Certificate.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Docker.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Emulation.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Find_Job.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Font.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Free_Picture.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Git.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Html.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_IA.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Job.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_JS.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Learning.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Legislation.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Linux.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Localhost.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Mind_Map.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Module_CMS.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Open_Source.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Recommandation.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_RGPD.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Sand_Box.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Search_Engine.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Security.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Security_Health.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_SEO.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Server.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Software.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_SQL.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Symfony.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Terminal.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Tools.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_UX_UI.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Validation.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Visio.php';
        include './INDEX_LARAGON/Menu/Sub_Menu_Web_Hosting.php';
        ?>
      </ul>
    </nav>
  </aside>
  <!-- Toggle sidebar (mobile/tablet) -->
  <button class="sidebar-toggle" id="sidebarToggle" aria-label="<?php echo __('sidebar.header'); ?>" aria-expanded="false" aria-controls="sidebar">
    <span class="sidebar-toggle-arrow" aria-hidden="true">&#10095;</span>
  </button>
  <div class="sidebar-overlay" id="sidebarOverlay"></div>
