<h2><?= the_title(); ?></h2>

<?php if (is_directory()) { ?>
<table class="bigtable">
    <tr>
        <td><?php $this->load->view('repositories/repo_directory', [], true); ?></td>
    </tr>
</table>
<?php } elseif (is_repo_config()) { ?>
<table class="bigtable">
    <tr>
        <td><?php $this->load->view('repositories/repository', [], true); ?></td>
    </tr>
</table>
<?php } elseif (is_repo_browser()) { ?>
<table class="bigtable">
    <tr>
        <td><?php $this->load->view('repositories/repo_browse', [], true); ?></td>
    </tr>
</table>
<?php } else { ?>
<p class="alert alert-danger">An error occurred, please check everything is set up correctly <a href="<?= base_url() ?>/checkinstall/">here</a>.</p>
<?php } ?>