<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class IniParser
{
    /**
     * Mimics PHP's function call of similar name.
     * This one has a built-in compatibility layer which should ensure
     * most ini files are readable.
     *
     * @param
     *
     * @return ini64 an ini64 object representing the ini file
     *
     * @see http://ca2.php.net/manual/en/function.parse-ini-string.php
     * FIXME process_sections
     */
    public function parse_ini_string_base64($ini, $process_sections = false)
    {
        $lines = explode("\n", $ini);
        $ini_arr = array();
        $hashcollection = false;
        foreach ($lines as $line) {
            $line = trim($line);

            // comments can be skipped
            if (!$line || $line[0] == ';' || $line[0] == '#') {
                continue;
            } // collection detection
            else {
                if ($line[0] == '[' && $endline = strpos($line, ']')) {
                    $collection = substr($line, 1, $endline - 1);
                    $hashcollection = base64_encode($collection);
                    $ini_arr[$hashcollection] = array();
                    continue;
                }
            }

            $keyval = explode('=', $line, 2);
            $key = trim($keyval[0]);
            $hashkey = base64_encode($key);
            $value = ltrim($keyval[1]);
            if ($hashcollection !== false) {
                $ini_arr[$hashcollection][$hashkey] = $value;
            } else {
                $ini_arr[$hashkey] = $value;
            }
        }

        return new ini64($ini_arr, true);
    }

    /**
     * Mimics PHP's function call of similar name.
     */
    public function parse_ini_file_base64($filename, $process_sections = false)
    {
        if (is_readable($filename)) {
            $ini = @file_get_contents($filename);
            if ($ini !== false) {
                return $this->parse_ini_string_base64($ini, $process_sections);
            }
        }

        return false;
    }
}

/**
 * An object which represents key-values of an ini file.
 * This implementation is necessary as certain values of a key are illegal in the PHP implementation (attempts to store as associative array).
 */
class ini64
{
    private $_values;

    public function __construct($values, $prehashed = false)
    {
        if ($prehashed) {
            $this->_values = $values;
        } else {
            if (is_array($values)) {
                foreach ($values as $collection_name => $collection_value) {
                    $hashcollection_name = base64_encode($collection_name);
                    if (is_array($collection_value)) {
                        foreach ($collection_value as $key_name => $key_value) {
                            $hashkey_name = base64_encode($key_name);
                            $this->_values[$hashcollection_name][$hashkey_name] = $key_value;
                        }
                    } else {
                        $this->_values[$hashcollection_name] = $collection_value;
                    }
                }
            }
        }
    }

    public function keys($collection = null)
    {
        $hashkeys = [];
        if ($collection != null) {
            $hashcollection = base64_encode($collection);
            $hashkeys = array_keys($this->_values[$hashcollection]);
        } else {
            $hashkeys = array_keys($this->_values);
        }
        $keys = array();
        foreach ($hashkeys as $hashkey) {
            $keys[] = base64_decode($hashkey);
        }

        return $keys;
    }

    public function get($key = null, $collection = null)
    {
        if ($key != null) {
            $hashkey = base64_encode($key);

            if ($collection != null) {
                $hashcollection = base64_encode($collection);

                return $this->_values[$hashcollection][$hashkey] ?? redirect('/hgdir'); // FIXME isset check
            } else {
                if (isset($this->_values[$hashkey])) {
                    return $this->_values[$hashkey];
                } else {
                    return;
                }
            }
        } else {
            return $this->_values;
        }
    }

    public function set($key, $val, $collection = null)
    {
        $hashkey = base64_encode($key);
        if ($collection != null) {
            $hashcollection = base64_encode($collection);
            $this->_values[$hashcollection][$hashkey] = $val; // FIXME isset check
        } else {
            $this->_values[$hashkey] = $val;
        }
    }

    public function unsetKey($hashkey, $collection = null, $pre_hashed = false)
    {
        if (!$pre_hashed) {
            $hashkey = base64_encode($hashkey);
        }

        if ($collection != null) {
            $hashcollection = base64_encode($collection);
            unset($this->_values[$hashcollection][$hashkey]);
        } else {
            unset($this->_values[$hashkey]);
        }
    }

    public function toString()
    {
        // generate ini string
        $ini = '';
        if (is_array($this->_values)) {
            foreach ($this->_values as $hashcollection_name => $collection_val) {
                $collection_name = base64_decode($hashcollection_name);

                if (is_array($collection_val)) {
                    // section header
                    $ini .= "\n[".$collection_name.']';

                    foreach ($collection_val as $hashkey_name => $key_val) {
                        $key_name = base64_decode($hashkey_name);
                        $ini .= "\n".$key_name.' = '.$key_val;
                    }
                } else {
                    $ini .= "\n".$collection_name.' = '.$collection_val;
                }
            }
        }

        return $ini;
    }
}
