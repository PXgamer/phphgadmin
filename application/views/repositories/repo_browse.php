<form action="<?= current_url(); ?>" method="post" id="form_hgrc" name="form_hgrc">
    <input type="hidden" name="form_action" id="form_action" value=""/>
    <div class="form-group">
        <iframe src="<?= $this->config->item('profile')['default']['hgserve_url'] . the_name(); ?>"
                frameborder="0" border="0" cellspacing="0"
                style="display:block; width: 100%; height: 100%;">
            Your browser must support iframes to use this feature.
        </iframe>
    </div>
    <div class="form-group">
        <a href="<?= site_url('hgrepo/manage/' . the_name()); ?>" class="btn btn-default btn-xs pull-right">
            <span class="fa fa-fw fa-cog"></span> Update Config
        </a>
    </div>
</form>