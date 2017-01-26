<?php
if (is_directory()) {
    $this->load->view('repositories/repo_directory', []);
} elseif (is_repo_config()) {
    $this->load->view('repositories/repository', []);
} elseif (is_repo_browser()) {
    $this->load->view('repositories/repo_browse', []);
} else {
    ?>
    <p class="alert alert-danger">An error occurred, please check everything is set up correctly <a
                href="<?= base_url() ?>/checkinstall/">here</a>.</p>
<?php 
} ?>