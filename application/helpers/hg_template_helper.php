<?php

function theme_name()
{
    return get_phphginfo('theme_name');
}

function is_directory()
{
    return get_phphginfo('pagetype') == 'directory';
}

function is_repo_config()
{
    return get_phphginfo('pagetype') == 'config';
}

function is_repo_browser()
{
    return get_phphginfo('pagetype') == 'browser';
}

function has_messages()
{
    return get_phphginfo('user_msg') !== false;
}

function user_messages()
{
    phphginfo('user_msg');
}

function has_errors()
{
    return get_phphginfo('user_err') !== false;
}

function user_errors()
{
    phphginfo('user_err');
}

function phphginfo($item)
{
    echo get_phphginfo($item);
}

function get_phphginfo($item)
{
    $ci = &get_instance();

    return $ci->load->get_var($item) ?? false;
}
