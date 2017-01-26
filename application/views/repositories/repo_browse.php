<table class="bigtable" style="border-bottom: 0">
    <form action="<?php echo current_url(); ?>" method="post" id="form_hgrc" name="form_hgrc">
        <input type="hidden" name="form_action" id="form_action" value=""/>
        <tr>
            <td colspan="3" align="right">
                <p>
                    <a href="<?php echo site_url('hgrepo/manage/'.the_name()); ?>"
                       class="ui-state-default ui-corner-all dialog_link"><span
                                class="ui-icon"></span><?php echo lang('hgphp_action_update_config'); ?></a>
                </p>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <div style="height:500px">
                    <iframe src="<?php echo hgserve_url(the_name()); ?>" frameborder="0" border="0" cellspacing="0"
                            style="display:block; width: 100%; height: 100%;">
                        Your browser must support iframes to use this feature.
                    </iframe>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="3" align="right">
                <p>
                    <a href="<?php echo site_url('hgrepo/manage/'.the_name()); ?>"
                       class="ui-state-default ui-corner-all dialog_link"><span
                                class="ui-icon"></span><?php echo lang('hgphp_action_update_config'); ?></a>
                </p>
            </td>
        </tr>
</table>

