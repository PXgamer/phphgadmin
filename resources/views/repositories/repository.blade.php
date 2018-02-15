<form action="{{ url() }}" method="post" id="form_hgrc" name="form_hgrc">
    <input type="hidden" name="form_action" id="form_action" value=""/>
    <table class="table">
        <tr>
            <td colspan="4">
                <p class="pull-right">
                    <a href="{{  url('hgrepo/browse/' . the_name()) }}"
                       class="btn btn-default">
                        <span class="fa fa-fw fa-link"></span> Browse
                    </a>
                    <a href="{{  url('hgdir') }}" id="dialog_link_cancel"
                       class="btn btn-default">
                        <span class="fa fa-fw fa-times"></span> Cancel
                    </a>
                    <a href="{{  url('hgrepo/manage/'.the_name()) }}" id="dialog_link_reset"
                       class="btn btn-default">
                        <span class="fa fa-fw fa-trash"></span> Reset
                    </a>
                    <a href="#" id="dialog_link_save"
                       class="btn btn-default">
                        <span class="fa fa-fw fa-save"></span> Save
                    </a>
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

        @while (has_sections())
            {{ the_section() }}
            <tr>
                <th>{{ section_name() }}</th>
                <th></th>
                <th>
                    <a href="{{ section_name() }}"
                       target="_blank" class="btn btn-info">
                        <span class="fa fa-fw fa-info"></span>
                    </a>
                </th>
                <th></th>
            </tr>

            @while (has_items())
                {{ the_item() }}
                <tr class="parity{{ $parity }}">
                    {{ $parity = ($parity + 1) % 2 }}
                    <td>{{ htmlentities(item_name()) }}</td>
                    <td style="text-align:right">
                        <nobr>
                            @if (item_is_boolean())
                                <select name="hgrc[{{ section_name() }}][{{ item_name() }}]">
                                    <option value="false"
                                            @if (item_dirty_value() == 'false')selected="selected"@endif>false
                                    </option>
                                    <option value="true"
                                            @if (item_dirty_value() == 'true')selected="selected"@endif>true
                                    </option>
                                </select>
                            @elseif (strlen(item_dirty_value()) > 70)
                                <textarea name="hgrc[{{ section_name() }}][{{ item_name() }}]"
                                          style="width:90%" rows="3">{{ item_dirty_value() }}</textarea>
                            @else
                                <input type="text" name="hgrc[{{ section_name() }}][{{ item_name() }}]"
                                       value="{{ item_dirty_value() }}" style="width:90%" size="150"/>
                            @endif
                        </nobr>
                    </td>
                    <td style="vertical-align:middle">
                        @if (__('phphgadmin.tooltip_hgrc_'.section_name().'_'.item_name()) != false)
                            <a href="#"
                               title="{{ htmlentities(lang('hgphp_tooltip_hgrc_'.section_name().'_'.item_name())) }}"
                               class="ui-state-default ui-corner-all">&nbsp;&nbsp;?&nbsp;&nbsp;</a>
                        @endif
                    </td>
                    <td style="vertical-align:middle;width:40%">{{ htmlentities(item_current_value()) }}</td>
                </tr>
            @endwhile
        @endwhile

        <tr>
            <td colspan="4">
                <p class="pull-right">
                    <a href="{{ url('hgrepo/browse/'.the_name()) }}"
                       class="btn btn-default">
                        <span class="fa fa-fw fa-link"></span> Browse
                    </a>
                    <a href="{{ url('hgdir') }}" id="dialog_link_cancel"
                       class="btn btn-default">
                        <span class="fa fa-fw fa-times"></span> Cancel
                    </a>
                    <a href="{{ url('hgrepo/manage/'.the_name()) }}" id="dialog_link_reset"
                       class="btn btn-default">
                        <span class="fa fa-fw fa-trash"></span> Reset
                    </a>
                    <a href="#" id="dialog_link_save"
                       class="btn btn-default">
                        <span class="fa fa-fw fa-save"></span> Save
                    </a>
                </p>
            </td>
        </tr>
    </table>
</form>
<div id="dialog_save" class="dialog" title="@lang('phphgadmin.action_save')">
    @lang('phphgadmin.dialog_repo_save')
</div>
