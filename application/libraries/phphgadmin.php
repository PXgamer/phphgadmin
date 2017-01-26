<?php

class phphgadmin
{
    private $_ci;
    private $_ofl_lock_hgweb;
    private $_ofl_lock_hgrc;

    private $_profile;
    private $_cache;

    public function __construct()
    {
        $this->_ci = &get_instance();
        $this->_ci->load->library('Hg_Filesystem');
        $this->_ci->load->library('Hg_Ini');

        $this->flush_cache();
    }

    public function set_profile($profile = null)
    {
        $this->_profile = $profile;
        $this->flush_cache();
    }

    public function get_profile()
    {
        return $this->_profile;
    }

    public function flush_cache()
    {
        $this->_cache = array();
    }

    public function lsdir()
    {
        $webdir = $this->get_profile();

        if (isset($this->_cache['lsdir'])) {
            return $this->_cache['lsdir'];
        }

        // retrieves current directory structure
        $realdir = $this->_ci->hg_filesystem->real_dirscan($webdir);

        // retrieves current
        $this->_ci->hg_ini->register_OFL($this->_ofl_lock_hgweb);
        $hgwebrepos_compat = $this->_ci->hg_ini->getRepoList($webdir);

        $hgwebrepos = array();
        if (is_array($hgwebrepos_compat)) {
            foreach ($hgwebrepos_compat as $r_name => $r_path) {
                $hgwebrepos[] = base64_decode($r_name);
            }
        } else {
            // error code
            return $hgwebrepos;
        }

        $allrepo = $realdir;
        $allrepo = array_merge($realdir, $hgwebrepos);

        $hgrepos = array();
        foreach ($allrepo as $repo) {
            $hgrepos[$repo]['name'] = $repo;

            if (isset($realdir[$repo]) && in_array($repo, $hgwebrepos)) {
                $hgrepos[$repo]['status'] = HGPHP_REPO_STATUS_ENABLED;
            } else {
                if (isset($realdir[$repo]) && !in_array($repo, $hgwebrepos)) {
                    $hgrepos[$repo]['status'] = HGPHP_REPO_STATUS_DISABLED;
                } else {
                    if (!isset($realdir[$repo]) && in_array($repo, $hgwebrepos)) {
                        $hgrepos[$repo]['status'] = HGPHP_REPO_STATUS_MISSING;
                    }
                }
            }
        }

        $this->_cache['lsdir'] = $hgrepos;

        return $hgrepos;
    }

    /**
     * stat_repository
     * Returns the HGRC represented as an array for the specified repository.
     *
     * @param r_name name of the project whose hgrc to retrieve
     *
     * @return array representing hgrc or status code
     */
    public function stat_repository($r_id)
    {
        if (!$this->can_view($r_id)) {
            return HGPHP_ERR_PERM_USR;
        }

        $profile = $this->get_profile();

        if (isset($this->_cache['stat'][$r_id])) {
            return $this->_cache['lsdir'];
        }

        $this->_ci->hg_ini->register_OFL($this->_ofl_lock_hgrc);

        return $this->_ci->hg_ini->loadHgRC($r_id, $profile);
    }

    public function create_repository($r_name)
    {
        $profile = $this->get_profile();

        if (!$this->can_create($r_name)) {
            return HGPHP_ERR_PERM_USR;
        }

        $create_status = HGPHP_OK;

        $lsdir = $this->_ci->hg_ini->getRepoList($profile);

        // not registered in hgweb.config
        $hashr_name = base64_encode($r_name);
        if (!isset($lsdir[$hashr_name])) {

            // edit the directory
            $this->_ci->hg_ini->register_OFL($this->_ofl_lock_hgweb);
            $create_status = $this->_ci->hg_ini->registerRepo($r_name, $profile);
            $this->flush_cache();
            if ($create_status == HGPHP_OK) {
                // then create the repository
                $this->_ci->hg_ini->register_OFL($this->_ofl_lock_hgrc);
                $create_status = $this->_ci->hg_filesystem->create_repository_dir($r_name, $profile);

                if ($create_status == HGPHP_OK) {
                    $create_status = $this->_ci->hg_ini->touchHgRC($r_name, $profile);
                }
            }
        } else {
            // repository already exists
            $create_status = HGPHP_ERR_FS_PREEXISTS;
        }

        return $create_status;
    }

    /**
     * update_repository
     * Update repository's hgrc.
     *
     * @param r_name name of the repository to update hgrc for
     * @param hgrc_data array representing new hgrc file
     *
     * @return status code
     */
    public function update_repository($r_name, $hgrc_data)
    {
        $profile = $this->get_profile();

        if (!$this->can_update($r_name)) {
            return HGPHP_ERR_PERM_USR;
        }
        $this->_ci->hg_ini->register_OFL($this->_ofl_lock_hgrc);

        $this->flush_cache();

        return $this->_ci->hg_ini->saveHgRC($r_name, $hgrc_data, $profile);
    }

    /**
     * delete_repository
     * Deletes a repository from the file system and unregisters it from hgweb.config.
     *
     * @param r_key id of the repo to delete permanently
     *
     * @return status code
     */
    public function delete_repository($r_key)
    {
        $hgweb = $this->get_profile();

        $hashr_key = base64_encode($r_key);
        if (!$this->can_delete($r_key)) {
            return HGPHP_ERR_PERM_USR;
        }

        $del_status = HGPHP_OK;
        $lsdir = $this->_ci->hg_ini->getRepoList($hgweb);

        if (isset($lsdir[$hashr_key])) {
            // edit the directory
            // remove from filesystem
            $this->_ci->hg_ini->register_OFL($this->_ofl_lock_hgweb);

            $del_status = $this->_ci->hg_filesystem->delete_repository_dir($r_key, $hgweb);

            // remove from hgweb.config
            if ($del_status == HGPHP_OK) {
                $del_status = $this->_ci->hg_ini->unregisterRepo($r_key, $hgweb);
            }
        } else {
            $del_status = HGPHP_ERR_FS_PREEXISTS;
        }
        $this->flush_cache();

        return $del_status;
    }

    /**
     * can_create
     * Checks if user has permissions to create this repository.
     * Requires view permission.
     *
     * @param r_name name of repository wanting to be created
     *
     * @return true if allowed
     */
    public function can_create($r_name)
    {
        return $this->_ci->config->item('global_allow_repo_create');
    }

    /**
     * can_update
     * Checks if user has permissions to update this repository
     * Requires view permission.
     *
     * @param r_name name of repository wanting to be updated
     *
     * @return true if allowed
     */
    public function can_update($r_name)
    {
        return $this->_ci->config->item('global_allow_repo_update');
    }

    /**
     * can_create
     * Checks if user has permissions to view this repository.
     *
     * @param r_name name of repository wanting to be created
     *
     * @return true if allowed
     */
    public function can_view($r_name)
    {
        return $this->_ci->config->item('global_allow_repo_view');
    }

    /**
     * can_delete
     * Checks if user has permissions to delete this repository
     * Requires view permission.
     *
     * @param r_name name of repository wanting to be deleted
     *
     * @return true if allowed
     */
    public function can_delete($r_name)
    {
        return $this->_ci->config->item('global_allow_repo_delete');
    }
}
