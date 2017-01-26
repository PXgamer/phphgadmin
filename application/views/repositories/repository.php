<table class="bigtable" style="border-bottom: 0">
    <form action="<?php echo current_url(); ?>" method="post" id="form_hgrc" name="form_hgrc">
        <input type="hidden" name="form_action" id="form_action" value=""/>
        <tr>
            <td colspan="4" align="right">
                <p>
                    <a href="<?php echo site_url('hgrepo/browse/' . the_name()); ?>"
                       class="ui-state-default ui-corner-all dialog_link"><span
                                class="ui-icon ui-icon-newwin"></span><?php echo lang('hgphp_action_browse'); ?></a>
                    <a href="<?php echo site_url('hgdir'); ?>" id="dialog_link_cancel"
                       class="ui-state-default ui-corner-all dialog_link"><span
                                class="ui-icon"></span><?php print lang('hgphp_action_cancel'); ?></a>
                    <a href="<?php echo site_url('hgrepo/manage/' . the_name()); ?>" id="dialog_link_reset"
                       class="ui-state-default ui-corner-all dialog_link"><span
                                class="ui-icon"></span><?php print lang('hgphp_action_reset'); ?></a>
                    <a href="#" id="dialog_link_save" class="ui-state-default ui-corner-all dialog_link"><span
                                class="ui-icon"></span><?php print lang('hgphp_action_save'); ?></a>
                </p>
            </td>
        </tr>
        <?php

        function restore_value($current_value, $hgrc_form, $section_name, $key_name)
        {
            if (!empty($hgrc_form) && isset($hgrc_form[$section_name]) && isset($hgrc_form[$section_name][$key_name])) {
                return $hgrc_form[$section_name][$key_name];
            }
            return $current_value;
        }

        ?>

        <?php while (has_sections()): the_section(); ?>
            <tr>
                <th><?php echo htmlentities(section_name()) ?></th>
                <th></th>
                <th><a href='<?php echo lang('hgphp_doclink_hgrc') . section_name() ?>'
                       target="_blank"><?php echo lang('hgphp_title_help'); ?></a></th>
                <th></th>
            </tr>

            <?php $parity = 0;
            while (has_items()): the_item(); ?>
                <tr class="parity<?php echo $parity;
                $parity = ($parity + 1) % 2; ?>">
                    <td style="vertical-align:middle"><?php echo htmlentities(item_name()); ?></td>
                    <td style="vertical-align:middle;text-align:right">
                        <nobr>
                            <?php if (item_is_boolean()): ?>
                                <select name="hgrc[<?php echo section_name() ?>][<?php echo item_name(); ?>]">
                                    <option value="false" <?php if (item_dirty_value() == 'false') {
                                        echo 'selected="selected"';
                                    } ?>>false
                                    </option>
                                    <option value="true" <?php if (item_dirty_value() == 'true') {
                                        echo 'selected="selected"';
                                    } ?>>true
                                    </option>
                                </select>
                            <?php elseif (strlen(item_dirty_value()) > 70): ?>
                                <textarea name="hgrc[<?php echo section_name() ?>][<?php echo item_name(); ?>]"
                                          style="width:90%" rows="3"><?php echo item_dirty_value(); ?></textarea>
                            <?php else: ?>
                                <input type="text" name="hgrc[<?php echo section_name() ?>][<?php echo item_name(); ?>]"
                                       value="<?php echo item_dirty_value(); ?>" style="width:90%" size="150"/>
                            <?php endif; ?>
                        </nobr>
                    </td>
                    <td style="vertical-align:middle">
                        <?php if (lang('hgphp_tooltip_hgrc_' . section_name() . '_' . item_name()) != false): ?>
                            <a href="#"
                               title="<?php echo htmlentities(lang('hgphp_tooltip_hgrc_' . section_name() . '_' . item_name())); ?>"
                               class="ui-state-default ui-corner-all">&nbsp;&nbsp;?&nbsp;&nbsp;</a>
                        <?php endif; ?>
                    </td>
                    </td>
                    <td style="vertical-align:middle;width:40%"><?php echo htmlentities(item_current_value()); ?></td>
                </tr>
            <?php endwhile; endwhile; ?>

        <tr>
            <td colspan="4" align="right">
                <p>
                    <a href="<?php echo site_url('hgrepo/browse/' . the_name()); ?>"
                       class="ui-state-default ui-corner-all dialog_link"><span
                                class="ui-icon ui-icon-newwin"></span><?php echo lang('hgphp_action_browse'); ?></a>
                    <a href="<?php echo site_url('hgdir'); ?>" id="dialog_link2_cancel"
                       class="ui-state-default ui-corner-all dialog_link"><span
                                class="ui-icon"></span><?php print lang('hgphp_action_cancel'); ?></a>
                    <a href="<?php echo site_url('hgrepo/manage/' . the_name()); ?>" id="dialog_link2_reset"
                       class="ui-state-default ui-corner-all dialog_link"><span
                                class="ui-icon"></span><?php print lang('hgphp_action_reset'); ?></a>
                    <a href="#" id="dialog_link2_save" class="ui-state-default ui-corner-all dialog_link"><span
                                class="ui-icon"></span><?php print lang('hgphp_action_save'); ?></a>
                </p>
            </td>
        </tr>
</table>
<div id="dialog_save" class="dialog" title="<?php echo lang('hgphp_action_save'); ?>">
    <?php echo lang('hgphp_dialog_repo_save'); ?>
</div>