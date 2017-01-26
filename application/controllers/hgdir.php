<?php

class hgdir extends CI_Controller
{
    private $ofl_lock_hgwebconf;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('hg_directory');
    }

    public function index()
    {
        // retrieve the latest OF lock for hgweb
        $this->ofl_lock_hgwebconf = $this->session->flashdata('ofl_hgwebconf');

        /*
         * Action handler
         */
        $form_action = $this->input->post('form_action');
        if ($form_action == 'create_repository') {
            $this->create();
        } else {
            if ($form_action == 'delete_repository') {
                $this->delete();
            }
        }

        // view the latest repository listings, updating the OF lock
        $lsdir = $this->phphgadmin->lsdir();

        // error handling
        if (!is_array($lsdir)) {
            $this->load->vars(array('user_err' => lang('hgphp_msg_hgwebconf_read_err_absdirs')));
        }

        $this->session->set_flashdata('ofl_hgwebconf', $this->ofl_lock_hgwebconf);

        $this->load->vars(array('pagetype' => 'directory'));

        $view = $this->load->view('index', [], true);
        $this->load->view('include/template', ['view' => $view]);
    }

    public function create()
    {
        $r_name = $this->input->post('form_create_name');
        $this->form_validation->set_rules('form_create_name', 'form_create_name',
            'required|alpha_dash|min_length[1]|max_length[255]');

        $blacklist = '';
        $path = @parse_url($this->config->item('base_url'), PHP_URL_PATH);
        if ($path !== false) {
            $components = @explode('/', $path);
            if (isset($components[1])) {
                $blacklist = $components[1];
            }
        }

        if ($this->form_validation->run() == false) {
            $this->load->vars(array('user_err' => lang('hgphp_msg_hgwebconf_create_err_validname')));
        } else {
            if ($r_name == $blacklist) {
                $this->load->vars(array('user_err' => lang('hgphp_msg_hgwebconf_create_err_blacklistname')));
            } else {
                $this->phphgadmin->start_tx($this->ofl_lock_hgwebconf, $dummy = '');
                $action_status = $this->phphgadmin->create_repository($r_name);
                $this->phphgadmin->end_tx();

                switch ($action_status) {
                    case HGPHP_OK:
                        $this->load->vars(array('user_msg' => $r_name.': '.lang('hgphp_msg_hgwebconf_create_success')));
                        break;
                    case HGPHP_ERR_PERM_USR:
                        $this->load->vars(array('user_err' => $r_name.': '.lang('hgphp_msg_hgwebconf_create_err_permuser')));
                        break;
                    case HGPHP_ERR_PERM_SYS_REPODIR:
                        $this->load->vars(array('user_err' => $r_name.': '.lang('hgphp_msg_hgwebconf_create_err_permsys')));
                        break;
                    case -100:
                        $this->load->vars(array('user_err' => $r_name.': '.'" RESTORE UNSUPPORTED.'));
                        break;
                    case HGPHP_ERR_FS_PREEXISTS:
                        $this->load->vars(array('user_err' => $r_name.': '.lang('hgphp_msg_hgwebconf_create_err_preexists')));
                        break;
                    case OFL_ERR_LOCKING:
                        $this->load->vars(array('user_err' => $r_name.': '.lang('hgphp_msg_hgwebconf_create_err_locking').' <a href="'.site_url('/hgdir').'" class="ui-state-default ui-corner-all dialog_link"><span class="ui-icon"></span>'.lang('hgphp_action_reload').'</a>'));
                        break;
                    default:
                        $this->load->vars(array('user_err' => $r_name.': '.lang('hgphp_msg_unknown_err').$action_status));
                        break;
                }
            }
        }
    }

    public function delete()
    {
        $r_name = $this->input->post('form_delete_name');
        $this->form_validation->set_rules('form_delete_name', 'form_delete_name', 'required');

        if ($this->form_validation->run() == false) {
            $this->load->vars(array('user_err' => lang('hgphp_msg_hgwebconf_delete_err_unspecified_name')));
        } else {
            $this->phphgadmin->start_tx($this->ofl_lock_hgwebconf, $dummy = '');
            $action_status = $this->phphgadmin->delete_repository($r_name);
            $this->phphgadmin->end_tx();

            switch ($action_status) {
                case HGPHP_OK:
                    $this->load->vars(array('user_msg' => $r_name.': '.lang('hgphp_msg_hgwebconf_delete_success')));
                    break;
                case HGPHP_ERR_PERM_USR:
                    $this->load->vars(array('user_err' => $r_name.': '.lang('hgphp_msg_hgwebconf_delete_err_permuser')));
                    break;
                case HGPHP_ERR_PERM_SYS_REPODIR:
                    $this->load->vars(array('user_err' => $r_name.': '.lang('hgphp_msg_hgwebconf_delete_err_permsys')));
                    break;
                case HGPHP_ERR_FS_PREEXISTS:
                    $this->load->vars(array('user_err' => $r_name.': '.lang('hgphp_msg_hgwebconf_delete_err_preexists')));
                    break;
                case OFL_ERR_LOCKING:
                    $this->load->vars(array('user_err' => $r_name.': '.lang('hgphp_msg_hgwebconf_delete_err_locking').' <a href="'.site_url('/hgdir').'" class="ui-state-default ui-corner-all dialog_link"><span class="ui-icon"></span>'.lang('hgphp_action_reload').'</a>'));
                    break;
                default:
                    $this->load->vars(array('user_err' => $r_name.': '.lang('hgphp_msg_unknown_err').$action_status));
                    break;
            }
        }
    }
}
