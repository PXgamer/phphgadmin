<?php

$_the_name;
$_hgrc_ini64;
$_sections;
$_section_iter_keys;
$_section_iter_count;
$_section_hashname;
$_items;
$_item_iter_keys;
$_item_iter_count;
$_item_hashname;
function the_name()
{
    global $_the_name;

    if ($_the_name == null) {
        $ci =& get_instance();
        $_the_name = rawurldecode($ci->uri->segment(3, 0));
    }
    return $_the_name;
}

function has_sections()
{
    global $_hgrc_ini64, $_sections, $_section_iter_keys, $_section_iter_count, $_section_hashname;
    global $_items, $_item_iter_keys, $_item_iter_count;
    if ($_hgrc_ini64 == null) {
        $ci =& get_instance();
        $_hgrc_ini64 = $ci->phphgadmin->stat_repository(the_name());
        if (is_object($_hgrc_ini64)) {
            $_sections = $_hgrc_ini64->get();
            $_section_iter_keys = array_keys($_sections);
        } else {
            return false;
        }
        $_section_iter_count = 0;
        $_item_iter_count = 0;
    }

    $has_section = ($_section_iter_count < count($_section_iter_keys));
    if ($has_section) {
//		$_section_hashname = $_section_iter_keys[$_section_iter_count];
//		$_items = $_sections[$_section_hashname];
//		$_item_iter_keys = array_keys($_items);
//		$_section_iter_count += 1;
    } else {
        $_section_iter_count = 0;
    }

    return $has_section;
}

function the_section()
{
    global $_hgrc_ini64, $_sections, $_section_iter_keys, $_section_iter_count, $_section_hashname;
    global $_items, $_item_iter_keys, $_item_iter_count;

    $has_section = has_sections();

    if ($has_section) {
        $_section_hashname = $_section_iter_keys[$_section_iter_count];
        $_items = $_sections[$_section_hashname];
        $_item_iter_keys = array_keys($_items);
        $_section_iter_count += 1;
    }

    return $_items;
}

function section_name()
{
    global $_section_hashname;
    return base64_decode($_section_hashname);
}

function has_items()
{
    global $_items, $_item_iter_keys, $_item_iter_count, $_item_hashname;

    $has_items = ($_item_iter_count < count($_item_iter_keys));
    if ($has_items) {
//		$_item_hashname = $_item_iter_keys[$_item_iter_count];
//		$_item_iter_count += 1;
    } else {
        $_item_iter_count = 0;
    }

    return $has_items;
}

function the_item()
{
    global $_items, $_item_iter_keys, $_item_iter_count, $_item_hashname;

    $has_items = has_items();

    if ($has_items) {
        $_item_hashname = $_item_iter_keys[$_item_iter_count];
        $_item_iter_count += 1;
    }

    return $_item_hashname;
}

function item_name()
{
    global $_item_hashname;
    return base64_decode($_item_hashname);
}

function item_current_value()
{
    global $_items, $_item_hashname;
    return $_items[$_item_hashname];
}

function item_dirty_value()
{
    $hgrc_form = get_phphginfo('hgrc_form');
    if (!empty($hgrc_form) && isset($hgrc_form[section_name()]) && isset($hgrc_form[section_name()][item_name()])) {
        return $hgrc_form[section_name()][item_name()];
    }
    return item_current_value();
}

function item_is_boolean()
{
    $bools = get_phphginfo('hgrc_bools_arr');
    return isset($bools[section_name()]) && in_array(item_name(), $bools[section_name()]);
}