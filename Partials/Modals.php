<!-- Dashboard modals — included from Header.php.
     Shared scope: $log_access, $log_intrusions, $csrf_token (defined in index.php). -->
    <!-- Modal phpinfo -->
    <div class="modal fade" id="phpinfoModal" tabindex="-1" aria-labelledby="phpinfoModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="phpinfoModalLabel">phpinfo()</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-0">
            <iframe id="phpinfoFrame" class="modal-iframe"></iframe>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Adminer -->
    <div class="modal fade" id="adminerModal" tabindex="-1" aria-labelledby="adminerModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="adminerModalLabel">Adminer</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-0">
            <iframe id="adminerFrame" class="modal-iframe"></iframe>
          </div>
        </div>
      </div>
    </div>

    <!-- Connection log modal -->
    <div class="modal fade" id="journalModal" tabindex="-1" aria-labelledby="journalModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="journalModalLabel"><?php echo __('log.connection_log'); ?></h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div style="display:flex; gap:1.5rem; flex-wrap:wrap;">

              <!-- Legitimate access -->
              <div style="flex:1 1 220px; border:1px solid green; border-radius:6px; padding:.8rem;">
                <strong style="color:green;"><?php echo __('log.local_access'); ?></strong>
                <p style="margin:.4rem 0;"><?php echo __('log.recorded_connections', ['count' => count($log_access)]); ?></p>
                <?php if (!empty($log_access)): ?>
                  <p style="font-size:.8rem; color:#888; word-break:break-all;"><?php echo __('log.last'); ?> <?php echo htmlspecialchars(end($log_access)); ?></p>
                <?php endif; ?>
                <div style="display:flex; gap:.5rem; margin-top:.5rem; flex-wrap:wrap;">
                  <form method="post" onsubmit="return confirm(__('log.confirm_clear_access'));" style="margin:0;">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="clear_log" value="access">
                    <button type="submit" class="btn btn-success btn-sm"><?php echo __('common.clear_log'); ?></button>
                  </form>
                  <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalAccess"><?php echo __('common.view_details'); ?></button>
                </div>
              </div>

              <!-- Intrusions -->
              <div style="flex:2 1 280px; border:1px solid <?php echo count($log_intrusions) > 0 ? 'red' : '#ccc'; ?>; border-radius:6px; padding:.8rem;">
                <strong style="color:<?php echo count($log_intrusions) > 0 ? 'red' : '#888'; ?>;">
                  <?php echo __('log.blocked_attempts'); ?> &mdash; <?php echo __('log.entries', ['count' => count($log_intrusions)]); ?>
                </strong>
                <?php if (!empty($log_intrusions)): ?>
                  <p style="font-size:.8rem; color:#888; margin:.4rem 0; word-break:break-all;"><?php echo __('log.last'); ?> <?php echo htmlspecialchars(array_reverse($log_intrusions)[0]); ?></p>
                <?php else: ?>
                  <p style="color:#888; font-size:.85rem; margin:.4rem 0;"><?php echo __('log.no_attempts'); ?></p>
                <?php endif; ?>
                <div style="display:flex; gap:.5rem; margin-top:.5rem; flex-wrap:wrap;">
                  <form method="post" onsubmit="return confirm(__('log.confirm_clear_intrusions'));" style="margin:0;">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="clear_log" value="intrusions">
                    <button type="submit" class="btn btn-danger btn-sm"><?php echo __('common.clear_log'); ?></button>
                  </form>
                  <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalIntrusions"><?php echo __('common.view_details'); ?></button>
                </div>
              </div>

            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><?php echo __('common.close'); ?></button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Authorized access (detail sub-modal) -->
    <div class="modal fade" id="modalAccess" tabindex="-1" aria-labelledby="modalAccessLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalAccessLabel"><?php echo __('log.authorized_connections'); ?> &mdash; <?php echo __('log.entries', ['count' => count($log_access)]); ?></h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" style="max-height:70vh;">
            <div class="modal-table-wrap">
              <?php if (!empty($log_access)): ?>
                <table class="table table-sm table-striped" style="font-size:.82rem;">
                  <thead><tr><th>#</th><th><?php echo __('log.col_type'); ?></th><th><?php echo __('log.col_date'); ?></th><th><?php echo __('log.col_ip'); ?></th></tr></thead>
                  <tbody>
                    <?php foreach (array_reverse($log_access) as $i => $line): ?>
                      <?php
                        $a_type = 'INDEX'; $a_date = ''; $a_ip = '';
                        if (preg_match('/^(.+?) - \[(.+?)\] OK - IP: (.+)$/', $line, $ma)) {
                          $a_date = $ma[1]; $a_type = $ma[2]; $a_ip = $ma[3];
                        } elseif (preg_match('/^(.+?) - IP: (.+)$/', $line, $ma)) {
                          $a_date = $ma[1]; $a_ip = $ma[2];
                        } else {
                          $a_date = $line;
                        }
                      ?>
                      <tr>
                        <td><?php echo count($log_access) - $i; ?></td>
                        <td><span class="badge" style="background:<?php echo $a_type === 'INDEX' ? '#198754' : ($a_type === 'API' ? '#0d6efd' : '#6f42c1'); ?>; font-size:.7rem;"><?php echo htmlspecialchars($a_type); ?></span></td>
                        <td style="white-space:nowrap;"><?php echo htmlspecialchars($a_date); ?></td>
                        <td><?php echo htmlspecialchars($a_ip); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              <?php else: ?>
                <p class="text-muted text-center"><?php echo __('log.no_connections'); ?></p>
              <?php endif; ?>
            </div>
          </div>
          <div class="modal-footer">
            <form method="post" onsubmit="return confirm(__('log.confirm_clear_access'));">
              <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
              <input type="hidden" name="clear_log" value="access">
              <button type="submit" class="btn btn-success btn-sm"><?php echo __('common.clear_log'); ?></button>
            </form>
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><?php echo __('common.close'); ?></button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Blocked attempts (detail sub-modal) -->
    <div class="modal fade" id="modalIntrusions" tabindex="-1" aria-labelledby="modalIntrusionsLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalIntrusionsLabel"><?php echo __('log.blocked_attempts'); ?> &mdash; <?php echo __('log.entries', ['count' => count($log_intrusions)]); ?></h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" style="max-height:70vh;">
            <div class="modal-table-wrap">
              <?php if (!empty($log_intrusions)): ?>
                <table class="table table-sm table-striped" style="font-size:.82rem;">
                  <thead><tr><th>#</th><th><?php echo __('log.col_type'); ?></th><th><?php echo __('log.col_ip'); ?></th><th><?php echo __('log.col_detail'); ?></th><th><?php echo __('log.col_date'); ?></th></tr></thead>
                  <tbody>
                    <?php foreach (array_reverse($log_intrusions) as $i => $line): ?>
                      <?php
                        $i_type = 'INDEX'; $i_date = ''; $i_ip = ''; $i_detail = '';
                        // New format: DATE - [TYPE] KO - IP: x - URI: y - Agent: z  OR  - Detail: d
                        if (preg_match('/^(.+?) - \[(.+?)\] KO - IP: (.+?) - URI: (.+?) - Agent: (.+)$/', $line, $m)) {
                          $i_date = $m[1]; $i_type = $m[2]; $i_ip = $m[3];
                          $i_detail = $m[4] . ' | ' . $m[5];
                        } elseif (preg_match('/^(.+?) - \[(.+?)\] KO - IP: (.+?) - Detail: (.+)$/', $line, $m)) {
                          $i_date = $m[1]; $i_type = $m[2]; $i_ip = $m[3]; $i_detail = $m[4];
                        // Legacy format: [HISTORIQUE] DATE - IP: x - URI: y - Agent: z
                        } elseif (preg_match('/^(\[HISTORIQUE\] )?(.+?) - IP: (.+?) - URI: (.+?) - Agent: (.+)$/', $line, $m)) {
                          $i_date = ($m[1] ? '[H] ' : '') . $m[2]; $i_ip = $m[3];
                          $i_detail = $m[4] . ' | ' . $m[5];
                        } else {
                          $i_date = $line;
                        }
                      ?>
                      <tr>
                        <td><?php echo count($log_intrusions) - $i; ?></td>
                        <td><span class="badge" style="background:<?php echo $i_type === 'INDEX' ? '#dc3545' : ($i_type === 'SHARE' ? '#fd7e14' : ($i_type === 'API' ? '#0d6efd' : '#6f42c1')); ?>; font-size:.7rem;"><?php echo htmlspecialchars($i_type); ?></span></td>
                        <td style="color:#c00; white-space:nowrap;"><?php echo htmlspecialchars($i_ip); ?></td>
                        <td style="font-size:.75rem; max-width:280px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?php echo htmlspecialchars($i_detail); ?>"><?php echo htmlspecialchars($i_detail); ?></td>
                        <td style="white-space:nowrap;"><?php echo htmlspecialchars($i_date); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              <?php else: ?>
                <p class="text-muted text-center"><?php echo __('log.no_attempts'); ?></p>
              <?php endif; ?>
            </div>
          </div>
          <div class="modal-footer">
            <form method="post" onsubmit="return confirm(__('log.confirm_clear_intrusions'));">
              <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
              <input type="hidden" name="clear_log" value="intrusions">
              <button type="submit" class="btn btn-danger btn-sm"><?php echo __('common.clear_log'); ?></button>
            </form>
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><?php echo __('common.close'); ?></button>
          </div>
        </div>
      </div>
    </div>

    <!-- Learning cycle modal -->
    <div class="modal fade" id="teachingModal" tabindex="-1" aria-labelledby="teachingModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="teachingModalLabel"><?php echo __('learning.title'); ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div style="text-align:left; font-size:.9rem; max-width:540px; margin:0 auto 1.5rem;">
              <h3 class="Cible" style="text-align:center; margin-bottom:1rem;"><?php echo __('learning.target'); ?></h3>
              <p style="font-style:italic; color:#9fd0ff; margin-bottom:1rem;"><?php echo __('learning.intro_perso'); ?></p>
              <p style="margin-bottom:1rem; color:#aaa;"><?php echo __('learning.intro'); ?></p>
              <p><strong style="color:#b39ddb;"><?php echo __('learning.imitate_label'); ?></strong> &mdash; <?php echo __('learning.imitate_desc'); ?></p>
              <p><strong style="color:#ffd700;"><?php echo __('learning.adapt_label'); ?></strong> &mdash; <?php echo __('learning.adapt_desc'); ?></p>
              <p><strong style="color:#4caf50;"><?php echo __('learning.transpose_label'); ?></strong> &mdash; <?php echo __('learning.transpose_desc'); ?></p>
              <p style="margin-bottom:0;"><strong style="color:#adff2f;"><?php echo __('learning.transmit_label'); ?></strong> &mdash; <?php echo __('learning.transmit_desc'); ?></p>
            </div>
            <div class="text-center">
              <div class="teaching-circle-wrapper">
                <div class="Transmettre">
                  <p class="CircleTexte"><?php echo __('learning.transmit_label'); ?></p>
                  <div class="Transposer">
                    <p class="CircleTexte"><?php echo __('learning.transpose_label'); ?></p>
                    <div class="Adapter">
                      <p class="CircleTexte"><?php echo __('learning.adapt_label'); ?></p>
                      <div class="Imiter">
                        <p class="CircleTexte"><?php echo __('learning.imitate_label'); ?></p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal README -->
    <div class="modal fade" id="readmeModal" tabindex="-1" aria-labelledby="readmeModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="readmeModalLabel"><?php echo __('burger.readme'); ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p style="margin-bottom:1rem;"><?php echo __('readme.intro'); ?></p>
            <h6 style="color:#e94560; border-bottom:1px solid #0f3460; padding-bottom:.4rem; margin-bottom:.6rem;"><?php echo __('readme.features_title'); ?></h6>
            <ul style="line-height:1.7; padding-left:1.2rem;">
              <li><?php echo __('readme.f_listing'); ?></li>
              <li><?php echo __('readme.f_create'); ?></li>
              <li><?php echo __('readme.f_tools'); ?></li>
              <li><?php echo __('readme.f_lang'); ?></li>
              <li><?php echo __('readme.f_security'); ?></li>
            </ul>
            <h6 style="color:#e94560; border-bottom:1px solid #0f3460; padding-bottom:.4rem; margin:1.1rem 0 .6rem;"><?php echo __('readme.usage_title'); ?></h6>
            <ul style="line-height:1.7; padding-left:1.2rem;">
              <li><?php echo __('readme.u1'); ?></li>
              <li><?php echo __('readme.u2'); ?></li>
              <li><?php echo __('readme.u3'); ?></li>
            </ul>
            <p style="font-size:.85rem; color:#888; margin-top:1rem;">
              <?php echo __('readme.author'); ?>
              <a href="https://nicolas-degabriel.digital" target="_blank" style="color:#09f;">nicolas-degabriel.digital</a>
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Menu customization modal (preferences stored locally, no server write) -->
    <div class="modal fade" id="menuPrefsModal" tabindex="-1" aria-labelledby="menuPrefsLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="menuPrefsLabel"><?php echo __('menuprefs.title'); ?></h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="<?php echo __('common.close'); ?>"></button>
          </div>
          <div class="modal-body">
            <div class="form-check form-switch mb-3">
              <input class="form-check-input" type="checkbox" id="prefSidebarToggle" checked>
              <label class="form-check-label" for="prefSidebarToggle"><?php echo __('menuprefs.show_sidebar'); ?></label>
            </div>
            <p style="font-size:.9rem; color:#aaa;"><?php echo __('menuprefs.choose'); ?></p>
            <div id="menuPrefsList" class="menu-prefs-list"></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-light btn-sm" id="prefSelectAll"><?php echo __('menuprefs.all'); ?></button>
            <button type="button" class="btn btn-success btn-sm" data-bs-dismiss="modal"><?php echo __('common.close'); ?></button>
          </div>
        </div>
      </div>
    </div>
