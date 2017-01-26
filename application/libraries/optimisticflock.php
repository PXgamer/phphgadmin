<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class optimisticflock
{
    private $_ci;
    private $_ofl_disabled = false;
    private $_tm_index = './lock/ofl_index.dat';
    private $cache_tm_index;

    public function __construct($config = array())
    {
        $this->_ci = &get_instance();

        $this->_ofl_disabled = $this->_ci->config->item('ofl_disabled');
        $lock_dir = $this->_ci->config->item('lock_dir');
        $lock_ofl_index = $this->_ci->config->item('lock_ofl_index');
        $this->_tm_index = $lock_dir.$lock_ofl_index;
    }

    /**
     * ofl_file_get_contents
     * Gets file contents under the supervision of the transaction manager.
     *
     * @param f_name file to be accessing contents
     * @param ofl_lock (updated by reference) the optimistic file lock value, FALSE on failure
     * @param err_code (updated by reference) set to status code on a failure
     *
     * @return file contents or false on error
     */
    public function ofl_file_get_contents($f_name, &$ofl_lock, &$err_code)
    {
        // check file exists and readable
        if (file_exists($f_name) && is_readable($f_name)) {
            // shared flock index
            $this->__tm_index_flock(LOCK_SH);

            // get or register in index
            $ofl_lock = $this->__tm_read_or_insert_ofl($f_name);

            // read in content
            $f_data = @file_get_contents($f_name);

            // release shared flock index
            $this->__tm_index_flock(LOCK_UN);

            // return contents (+ofl lock indirectly)
            if ($f_data != false) {
                $err_code = HGPHP_OK;

                return $f_data;
            } else {
                $err_code = OFL_ERR_READING;
            }
        } else {
            // discard this dead index if present
            $this->__tm_delete_ofl($f_name);

            // return failure status
            $ofl_lock = false;
            $err_code = OFL_ERR_NOTEXISTS_OR_PERM;
        }

        return false;
    }

    /**
     * ofl_file_put_contents
     * Puts new file contents under the supervision of the transaction manager.
     *
     * @param f_name file to be writing contents to
     * @param f_contents contents to write to file
     * @param ofl_lock (updated by reference) the optimistic file lock value, FALSE on fail
     * @param ofl_force (optional) if ofl_lock doesn't match, still allow operation
     *
     * @return status code
     */
    public function ofl_file_put_contents($f_name, $f_contents, &$ofl_lock, $ofl_force = false)
    {
        // check file exists and writable
        if (file_exists($f_name) && is_writable($f_name)) {
            // exclusive flock index
            $this->__tm_index_flock(LOCK_EX);

            // compare optimistic locks
            $ofl_currlock = $this->__tm_read_ofl($f_name);

            // registers new file in the event it's never been read by the tm before
            if ($ofl_currlock == false) {
                $ofl_currlock = $this->__tm_insert_or_update_ofl($f_name);
                $ofl_lock = $ofl_currlock;
            }

            if (($this->_ofl_disabled) || ($ofl_currlock === $ofl_lock) || ($ofl_force)) {
                // if match or force enabled? then write
                $put_status = @file_put_contents($f_name, $f_contents);

                // write new oflock in index
                $ofl_lock = $this->__tm_insert_or_update_ofl($f_name);

                // release exclusive flock index
                $this->__tm_index_flock(LOCK_UN);

                // return success of the save (+ofl lock indirectly)
                if ($put_status != false) {
                    return HGPHP_OK;
                } else {
                    return OFL_ERR_WRITING;
                }
            } else {
                // optimistic locking fail
                return OFL_ERR_LOCKING;
            }
        } else {
            // discard this dead index if present
            $this->__tm_delete_ofl($f_name);

            // return failure status
            $ofl_lock = false;

            return OFL_ERR_NOTEXISTS_OR_PERM;
        }
    }

    /**
     * ofl_parse_ini_file
     * Reads ini contents to multi-dimension array under the supervision of the transaction manager.
     *
     * @param f_name file to be reading contents from
     * @param ini_structured (optional) whether to structure returned array, default false
     * @param ofl_lock (updated by reference) the optimistic file lock value, FALSE on fail
     * @param err_code (updated by reference) set to status code on a failure
     *
     * @return an array or false on error
     */
    public function ofl_parse_ini_file($f_name, $ini_structured = false, &$ofl_lock, &$err_code)
    {
        // check file exists and readable
        if (file_exists($f_name) && is_readable($f_name)) {
            // shared flock index
            $this->__tm_index_flock(LOCK_SH);

            // get or register in index
            $ofl_lock = $this->__tm_read_or_insert_ofl($f_name);

            // parse ini
            $this->_ci->load->library('iniparser');
            $f_data = $this->_ci->iniparser->parse_ini_file_base64($f_name, $ini_structured);

            // release shared flock index
            $this->__tm_index_flock(LOCK_UN);

            // return parsed ini (+ofl lock indirectly)
            if ($f_data !== false) {
                $err_code = HGPHP_OK;

                return $f_data;
            } else {
                $err_code = OFL_ERR_READING;
            }
        } else {
            // discard this dead index if present
            $this->__tm_delete_ofl($f_name);

            // return failure status
            $ofl_lock = false;
            $err_code = OFL_ERR_NOTEXISTS_OR_PERM;
        }

        return false;
    }

    /**
     * ofl_touch
     * Deletes file under the supervision of the transaction manager.
     *
     * @param f_name file to be touched
     * @param ofl_lock (updated by reference) the optimistic file lock value, FALSE on fail
     *
     * @return status code
     */
    public function ofl_touch($f_name, &$ofl_lock)
    {
        // exclusive flock index
        $this->__tm_index_flock(LOCK_EX);

        // create the new file
        $touch_status = @touch($f_name);

        if ($touch_status == true) {
            // write new oflock in index
            $ofl_lock = $this->__tm_insert_or_update_ofl($f_name);
        }

        // release exclusive flock index
        $this->__tm_index_flock(LOCK_UN);

        if ($touch_status == true) {
            return HGPHP_OK;
        }
        // insufficient privilege to touch file
        return OFL_ERR_WRITING;
    }

    /**
     * ofl_unlink
     * Deletes file under the supervision of the transaction manager.
     *
     * @param f_name file to be delete
     * @param ofl_lock (updated by reference) the optimistic file lock value, FALSE on fail
     * @param ofl_force (optional) if ofl_lock doesn't match, still allow operation
     *
     * @return true on success or false on error
     */
    public function ofl_unlink($f_name, &$ofl_lock, $ofl_force = false)
    {
        // check file exists and writable (read:deletable)
        if (file_exists($f_name) && is_writable($f_name)) {
            // exclusive flock index
            $this->__tm_index_flock(LOCK_EX);

            // compare optimistic locks
            $ofl_currlock = $this->__tm_read_ofl($f_name);

            if (($this->_ofl_disabled)
                || ($ofl_currlock === $ofl_lock) // locks match
                || ($ofl_currlock == false)  // not tm-managed file, don't bother creating new ofl lock
                || ($ofl_force)
            ) {
                // force lock

                // if match or force enabled? then delete
                $unlink_status = @unlink($f_name);

                if ($unlink_status == true) {
                    // write new oflock in index
                    $ofl_lock = $this->__tm_delete_ofl($f_name);
                }

                // release exclusive flock index
                $this->__tm_index_flock(LOCK_UN);

                // return success of the unlink (+ofl lock indirectly)
                if ($unlink_status == true) {
                    return HGPHP_OK;
                } else {
                    return OFL_ERR_WRITING;
                }
            } else {
                return OFL_ERR_LOCKING;
            }
        } else {
            // discard this dead index if present
            $this->__tm_delete_ofl($f_name);

            // return failure status
            $ofl_lock = false;

            return OFL_ERR_NOTEXISTS_OR_PERM;
        }
    }

    /**
     * __tm_index_flock (private method)
     * Direct file system locking to the Transaction Manager's optimistic locking index.
     *
     * @param LOCK_SH , LOCK_EX or LOCK_UN for flock()
     *
     * @return true on transaction manager lock success
     */
    public function __tm_index_flock($operation)
    {
        if (!file_exists($this->_tm_index)) {
            touch($this->_tm_index);
        }

        $fp_index = null;

        switch ($operation) {
            case LOCK_SH:
            case LOCK_EX:
                $fp_index = fopen($this->_tm_index, 'r+');
                break;
            case LOCK_UN:
            default:
                $fp_index = fopen($this->_tm_index, 'r');
                break;
        }

        return flock($fp_index, $operation);
    }

    /**
     * __tm_read_or_insert_ofl (private method)
     * Returns current ofl_lock value for f_name, or inserts a new one.
     *
     * @param f_name file to retrieve ofl lock for
     *
     * @return f_name's current ofl lock or FALSE on error
     */
    public function __tm_read_or_insert_ofl($f_name)
    {
        $ofl_lock = $this->__tm_read_ofl($f_name);
        if ($ofl_lock == false) {
            $ofl_lock = $this->__tm_insert_or_update_ofl($f_name);
        }

        return $ofl_lock;
    }

    /**
     * __tm_insert_or_update_ofl (private method)
     * Creates a new optimistic locking number for the specified file name.
     *
     * @param f_name file to create new ofl lock for
     *
     * @return the optimistic locking number of the specified file name
     */
    public function __tm_insert_or_update_ofl($f_name)
    {
        if (!is_array($this->cache_tm_index)) {
            $this->__tm_index_load();
        }

        $this->cache_tm_index[$f_name]['ofl'] = uniqid('', true);
        $this->__tm_index_persist();

        return $this->cache_tm_index[$f_name]['ofl'];
    }

    /**
     * __tm_delete_ofl (private method)
     * Deletes f_name from the transaction manager index.
     *
     * @param f_name file to delete from index
     *
     * @return true if deleted
     */
    public function __tm_delete_ofl($f_name)
    {
        if (!is_array($this->cache_tm_index)) {
            $this->__tm_index_load();
        }

        if (isset($this->cache_tm_index[$f_name])) {
            unset($this->cache_tm_index[$f_name]);
            $this->__tm_index_persist();

            return true;
        }

        return false;
    }

    /**
     * __tm_read_ofl (private method)
     * Returns the optimistic locking number of the specified file name, or return FALSE if it does not exist.
     *
     * @param f_name file to search ofl lock for in index
     *
     * @return the optimistic locking number of the specified file name, FALSE if it does not exist
     */
    public function __tm_read_ofl($f_name)
    {
        if (!is_array($this->cache_tm_index)) {
            $this->__tm_index_load();
        }

        if (isset($this->cache_tm_index[$f_name])) {
            return $this->cache_tm_index[$f_name]['ofl'];
        } else {
            return false;
        }
    }

    /**
     * __tm_index_load (private method)
     * Loads tm index from disk into memory.
     *
     * @return true if retrieved from disk, false if empty index set up
     */
    public function __tm_index_load()
    {
        // get serialized index data
        $tm_data = @file_get_contents($this->_tm_index);

        // is the index populated already?
        // FIXME what if fialed to load index?
        if ($tm_data != false) {
            $this->cache_tm_index = unserialize($tm_data);

            return true;
        } else {
            $this->cache_tm_index = array();

            return false;
        }
    }

    /**
     * __tm_index_persist (private helper)
     * Saves tm index from cache to disk.
     *
     * @return true on successful save
     */
    public function __tm_index_persist()
    {
        return @file_put_contents($this->_tm_index, serialize($this->cache_tm_index));
    }
}
