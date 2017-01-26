<?php

function have_repos()
{
    global $_lsdir, $_lsdir_iter, $_lsdir_iter_count;

    if ($_lsdir == null) {
        $ci = &get_instance();
        $dirs = $ci->phphgadmin->lsdir();
        // order
        uksort($dirs, 'strnatcasecmp');
        $_lsdir = array_values($dirs);
        $_lsdir_iter_count = 0;
    }

    $has_next = ($_lsdir_iter_count < count($_lsdir));

    if (!$has_next) {
        // reset
        $_lsdir_iter_count = 0;
    }

    return $has_next;
}

function the_repo()
{
    global $_lsdir, $_lsdir_iter, $_lsdir_iter_count;

    $has_next = have_repos();

    if ($has_next) {
        $_lsdir_iter = $_lsdir[$_lsdir_iter_count];
        $_lsdir_iter_count += 1;
    }

    return $_lsdir_iter;
}

function repo_name()
{
    global $_lsdir_iter;

    return $_lsdir_iter['name'];
}

function repo_status()
{
    global $_lsdir_iter;

    return $_lsdir_iter['status'];
}
