<table class="bigtable" style="border-bottom: 0px">
    <tr>
        <td colspan="4">
            <p><a href="#" id="dialog_link_create" class="ui-state-default ui-corner-all dialog_link"><span
                            class="ui-icon ui-icon-newwin"></span><?php print lang('hgphp_action_create'); ?></a>
                <a href="#" id="dialog_link_delete" class="ui-state-default ui-corner-all dialog_link"><span
                            class="ui-icon ui-icon-newwin"></span><?php print lang('hgphp_action_delete'); ?></a></p>
        </td>
    </tr>
    <tr>
        <th></th>
        <th width="100%"><?php echo lang('hgphp_title_repo'); ?></th>
        <th><?php echo lang('hgphp_title_status'); ?></th>
        <th></th>
    </tr>
    <?php $parity = 0;
    while (have_repos()): the_repo(); ?>
        <tr class="parity<?php echo $parity;
        $parity = ($parity + 1) % 2; ?>">
            <td>
                <input type="checkbox" disabled="disabled"/>
            </td>
            <td>
                <a href="<?php echo site_url('hgrepo/manage/' . repo_name()); ?>"><?php echo htmlentities(repo_name()); ?></a>
            </td>
            <td>
                <?php if (repo_status() == HGPHP_REPO_STATUS_ENABLED): echo lang('hgphp_repostatus_enabled'); ?>
                <?php elseif (repo_status() == HGPHP_REPO_STATUS_DISABLED): echo lang('hgphp_repostatus_disabled'); ?>
                <?php elseif (repo_status() == HGPHP_REPO_STATUS_MISSING): echo lang('hgphp_repostatus_missing'); ?>
                <?php endif; ?>
            </td>
            <td>
                <?php if (repo_status() == HGPHP_REPO_STATUS_ENABLED): ?>
                    <a href="<?php echo site_url('hgrepo/browse/' . repo_name()); ?>"
                       class="ui-state-default ui-corner-all dialog_link"><span
                                class="ui-icon"></span><?php echo lang('hgphp_action_browse'); ?></a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
    <tr>
        <td colspan="4">
            <p><a href="#" id="dialog_link2_create" class="ui-state-default ui-corner-all dialog_link"><span
                            class="ui-icon ui-icon-newwin"></span><?php echo lang('hgphp_action_create'); ?></a>
                <a href="#" id="dialog_link2_delete" class="ui-state-default ui-corner-all dialog_link"><span
                            class="ui-icon ui-icon-newwin"></span><?php echo lang('hgphp_action_delete'); ?></a></p>
        </td>
    </tr>
</table>

<!-- ui-dialog -->
<div id="dialog_create" class="dialog" title="<?php echo lang('hgphp_action_create'); ?>">
    <form action="<?php echo site_url('hgdir'); ?>" method="post" id="form_create" name="form_create">
        <table style="width: 100%">
            <tr>
                <td>
                    <?php echo lang('hgphp_dialog_repo_create'); ?>
                    <br/>
                    <input type="hidden" name="form_action" value="create_repository"/>
                    <input type="text" style="width:100%" name="form_create_name" id="form_create_name"
                           autocomplete="off"/>
                </td>
            </tr>
        </table>
    </form>
</div>
<div id="dialog_delete" class="dialog" title="<?php echo lang('hgphp_action_delete'); ?>">
    <form action="<?php echo site_url('hgdir'); ?>" method="post" id="form_delete" name="form_delete">
        <table style="width: 100%">
            <tr>
                <td>
                    <input type="hidden" name="form_action" value="delete_repository"/>
                    <?php echo lang('hgphp_dialog_repo_delete'); ?>
                    <br/>
                    <select style="width:100%" name="form_delete_name" id="form_delete_name">
                        <option selected="selected" value=""></option>
                        <?php while (have_repos()): the_repo(); ?>
                            <option value="<?php echo repo_name(); ?>"><?php echo repo_name(); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <br/>
                </td>
            </tr>
        </table>
    </form>
</div>