<?php

class Hg_filesystem
{
    private $_ci;

    public function __construct()
    {
        $this->_ci = &get_instance();
    }

    /**
     * create_repository_dir
     * Creates a whole repository directory, with hgrc.
     *
     * @param r_name name of repository to create directory for
     *
     * @return status code
     */
    public function create_repository_dir($r_name, $hgweb = null)
    {
        $install_config = $this->_ci->hg_ini->getInstallConfig($hgweb);
        $repo_dir = $install_config['default_repo_dir'];
        // create repo directory structure recursively
        $create_status = @mkdir($repo_dir.$r_name.'/.hg/store/data/', 0755, true);

        if ($create_status == true) {
            // create hgrc
            $create_status = HGPHP_OK;
        } else {
            // system couldn't make directory
            $create_status = HGPHP_ERR_PERM_SYS_REPODIR;
        }

        return $create_status;
    }

    /**
     * delete_repository_dir
     * Deletes a whole repository directory including hgrc and data files.
     *
     * @param r_name name of repository to delete directory of
     *
     * @return status code
     */
    public function delete_repository_dir($r_name, $hgweb = null)
    {
        $cd = $this->_ci->hg_ini->getHgRCPath($r_name, $hgweb);

        $this->recursiveDelete($cd.'/.hg/');
        $del_status = $this->recursiveDelete($cd);
        if ($del_status == true) {
            $del_status = HGPHP_OK;
        } else {
            $del_status = HGPHP_ERR_PERM_SYS_REPODIR;
        }

        return $del_status;
    }

    /**
     * Performs a real directory scan where the projects are suppose to reside.
     *
     * @return the array containing 0 or more valid directories
     */
    public function real_dirscan($hgweb = null)
    {
        $this->_ci->load->helper('directory');

        $install_config = $this->_ci->hg_ini->getInstallConfig($hgweb);
        $repo_dir = $install_config['default_repo_dir'];

        $realdir = directory_map($repo_dir, true);

        $verifiedrealdir = array();
        if (is_array($realdir)) {
            foreach ($realdir as $file) {
                // checks if we detected a folder
                if (is_dir($repo_dir.$file)) {
                    $verifiedrealdir[$file] = $file;
                }
            }
        }

        return $verifiedrealdir;
    }

    /**
     * Delete a file or recursively delete a directory.
     *
     * @param string $str Path to file or directory
     *
     * @return true on successful deletion
     */
    public function recursiveDelete($str)
    {
        if (is_file($str)) {
            return @unlink($str);
        } elseif (is_dir($str)) {
            $scan = glob(rtrim($str, '/').'/*');
            foreach ($scan as $index => $path) {
                $this->recursiveDelete($path);
            }

            return @rmdir($str);
        }
    }
}
