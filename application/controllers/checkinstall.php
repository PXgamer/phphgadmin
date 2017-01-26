<?php

class checkinstall extends CI_Controller
{
    public $checks = array(

        /* base url, htaccess redirects, e-mailer set up, */
        'General' => array(
            'sitename' => array(
                'description' => 'Makes sure it\'s not empty or invalid.',
                'msg_warning' => '',
                'msg_fail' => 'Set the config base_url to an accessible address.',
            ),
            'htaccess' => array(
                'description' => 'Allows clean URLs and redirects while protecting direct access to framework scripts. Ensure Apache Rewrite module is enabled.',
                'msg_warning' => '',
                'msg_fail' => 'Restore the .htaccess file from initial install to this web directory.',
            ),
            'default_profile' => array(
                'description' => 'A valid default profile has been set, used when the user has not explicitly picked a profile to work under.',
                'msg_warning' => '',
                'msg_fail' => 'Set the config default_profile to the name of an existing profile.',
            ),
        ),
        /* write temp directory, allow uploads */
        'Server Permissions' => array(
            'writetemp' => array(
                'description' => 'Ensures local scratch space is writable.',
                'msg_warning' => '',
                'msg_fail' => 'Enable server write permissions to the temp directory. Config: lock_dir',
            ),
            'global_permissions' => array(
                'description' => 'Global lock-down privileges on view, create, delete and update are not in effect.',
                'msg_warning' => '',
                'msg_fail' => 'Lift permissions by setting the following config to TRUE: global_allow_repo_create, global_allow_repo_view, global_allow_repo_delete, global_allow_repo_update',
            ),
        ),
    );

    public $repo_checks = array(
        'writehgwebdir' => array(
            'description' => 'Ensures Mercurial web directory registry is writable.',
            'msg_warning' => '',
            'msg_fail' => 'Allow hgwebdir.config to be writable by PHP. Config: $config[\'profile\'][PROFILE_NAME][\'ini\']',
        ),
        'writehgrc' => array(
            'description' => 'Ensures Mercurial default repository folder writable.',
            'msg_warning' => '',
            'msg_fail' => 'Allow Mercurial repositories folder to be writable by PHP. Config: $config[\'profile\'][PROFILE_NAME][\'default_repo_dir\']',
        ),
    );

    private function table_result($t_name, $t_config, $t_result)
    {
        $status_cell = '';
        if ($t_result) {
            $status_cell = '<td width=50 style="color:green" align=center>OK</td><td width="500"></td>';
        } else {
            $status_cell = '<td width=50 align=center valign=top><span style="color:white;background:red;padding:3px;"><b>FAIL</b></span></td><td width="500" valign=top><font size=-1>'.$t_config['msg_fail'].'</font></td>';
        }

        return "<tr><td width=150 valign=top><b>$t_name</b></td><td width=200 valign=top><font size=-1>".$t_config['description']."</font></td>$status_cell</tr>";
    }

    private function test_sitename()
    {
        return !empty($this->config->item('base_url')) && stripos($this->config->item('base_url'),
                '127.0.0.1') === false;
    }

    private function test_htaccess()
    {
        return @file_exists('.htaccess');
    }

    private function test_default_profile()
    {
        $default_profile = $this->config->item('default_profile');

        return !empty($default_profile) && isset($this->config->item('profile')[$default_profile]);
    }

    private function test_writetemp()
    {
        return is_writable($this->config->item('lock_dir'));
    }

    private function test_global_permissions()
    {
        return $this->config->item('global_allow_repo_view') && $this->config->item('global_allow_repo_create') && $this->config->item('global_allow_repo_update') && $this->config->item('global_allow_repo_delete');
    }

    private function test_writehgwebdir($r_name)
    {
        return is_writable($this->config->item('profile')[$this->config->item('default_profile')]['ini']);
    }

    private function test_writehgrc($r_name)
    {
        return is_writable($this->config->item('profile')[$this->config->item('default_profile')]['default_repo_dir']);
    }

    public function index()
    {
        $view = '<h2>phpHgAdmin Check Installation</h2>';
        $view .= '<fieldset>
    <legend>Server Information</legend>
    <table width="100%">
        <tr>
            <td width="170"><b>PHP version</b></td>
            <td>'.phpversion().'</td>
        </tr>
        <tr>
            <td width="170"><b>Apache version</b></td>
            <td>';

        if (function_exists('apache_get_version')) {
            $view .= apache_get_version();
        } elseif (isset($_SERVER) && isset($_SERVER['SERVER_SOFTWARE'])) {
            $view .= $_SERVER['SERVER_SOFTWARE'];
        }
        $view .= '</td>
        </tr>
    </table>
</fieldset>';

        foreach ($this->checks as $category => $tests) {
            $view .= '<fieldset><legend>'.($category ?? '').'</legend><table width="100%">';
            foreach ($tests as $t_name => $test) {
                $t_result = call_user_func('self::test_'.$t_name);
                $view .= $this->table_result($t_name, $test, $t_result);
            }
            $view .= '</table></fieldset>';
        }
        $this->load->view('include/template', ['view' => $view]);
    }
}
