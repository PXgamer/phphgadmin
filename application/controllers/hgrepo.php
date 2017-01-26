<?php

class hgrepo extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('hg_repo');
        $this->load->library('iniparser');
    }

    public function browse()
    {
        $repositoryName = rawurldecode($this->uri->segment(3, 0));

        if ($repositoryName == false || preg_match('/^([a-z0-9\-_ ])+$/i', $repositoryName) != 1) {
            $this->load->helper('url');
            redirect('/hgdir');

            return;
        }

        $title = $repositoryName . '@default | ' . HGPHP_NAME;

        $this->load->vars(['title' => $title, 'pagetype' => 'browser']);

        $view = $this->load->view('index', [], true);
        $this->load->view('include/template', ['view' => $view]);
    }

    public function manage()
    {
        $repositoryName = rawurldecode(preg_split('/manage\//', $this->uri->uri_string())[1]);

        if ($repositoryName == false || preg_match('/^([a-z0-9:\/\-_ ])+$/i', $repositoryName) != 1) {
            $this->load->helper('url');
            redirect('/hgdir');

            return;
        }

        $ofl_lock_hgrc = $this->session->flashdata('ofl_hgrc_' . $repositoryName);
        $valid = true;
        $save_status = true; // a value that isn't HGPHP_OK or an error code
        $form_action = $this->input->post('form_action');
        $hgrc_form = array();
        if ($form_action != false) {
            $hgrc_form = $this->input->post('hgrc');

            // data validation (due to INI-parsing capabilities of PHP<=5.2
            $validation_regex_key = '/^([^=])+$/i'; // should match the entire input
            $validation_regex_value = '/^([a-z0-9\-_:\/`@#\%\* \+\]\\\',\.])*$/i';
            if ($hgrc_form != false) {
                foreach ($hgrc_form as $collection_name => $collection) {
                    if (is_array($collection)) {
                        foreach ($collection as $item_key => $item_value) {
                            $valid &= (preg_match($validation_regex_key, $item_key) == 1);
                            //$valid &= (preg_match($validation_regex_value, $item_value) == 1);
                        }
                    }
                }
            }

            if ($valid) {
                $this->phphgadmin->start_tx($dummy = '', $ofl_lock_hgrc);
                $ini64 = new ini64($hgrc_form);
                $save_status = $this->phphgadmin->update_repository($repositoryName, $ini64);
                $this->phphgadmin->end_tx();

                switch ($save_status) {
                    case HGPHP_OK:
                        $this->load->vars(array('user_msg' => lang('hgphp_msg_hgrc_save_success')));
                        break;
                    case HGPHP_ERR_PERM_USR:
                        $this->load->vars(array('user_err' => lang('hgphp_msg_hgwebconf_create_err_permuser')));
                        break;
                    case OFL_ERR_LOCKING:
                        $this->load->vars(array('user_err' => lang('hgphp_msg_hgrc_save_err_locking') . '<a href="' . site_url('/hgrepo/manage/' . $repositoryName) . '" class="ui-state-default ui-corner-all dialog_link"><span class="ui-icon"></span>' . lang('hgphp_action_reload') . '</a>'));
                        break;
                    case OFL_ERR_NOTEXISTS_OR_PERM:
                        $this->load->vars(array('user_err' => lang('hgphp_msg_hgrc_save_err_notfound')));
                        break;
                    default:
                        $this->load->vars(array('user_err' => lang('hgphp_msg_unknown_err') . $save_status));
                        break;
                }
            } else {
                $this->load->vars(array('user_err' => lang('hgphp_msg_hgrc_save_err_validation') . '='));
            }
        }

        $title = $repositoryName . '@default | ' . HGPHP_NAME;

        $this->load->vars(['title' => $title]);

        $hgrc = $this->phphgadmin->stat_repository($repositoryName);

        if (is_integer($hgrc) && $save_status === true) {
            switch ($hgrc) {
                case OFL_ERR_NOTEXISTS_OR_PERM:
                    $this->load->vars(array('user_err' => 'READ ERROR'));
                    break;
                case HGPHP_ERR_PERM_USR:
                    $this->load->vars(array('user_err' => 'NO PERMISSIONS'));
                    break;
                default:
                    $this->load->vars(array('user_err' => 'UNKNOWN ERROR'));
                    break;
            }
        }

        // just a reminder, only when we're not pushing a successful save message
        if ($valid && ($save_status != HGPHP_OK)) {
            //			$this->template->inject_partial('user_msg', lang('hgphp_msg_hgrc_save_err_validation').'-_:/`@#%* +]\\\',.');
            $hgrc_form = array();
        }

        $this->session->set_flashdata('ofl_hgrc_' . $repositoryName, $ofl_lock_hgrc);

        $this->load->vars(array(
            'pagetype' => 'config',
            'hgrc_bools_arr' => $this->config->item('hgrc_bools_arr'),
            'hgrc_form' => $hgrc_form,
        ));

        $view = $this->load->view('index', [], true);
        $this->load->view('include/template', ['view' => $view]);
    }

    public function index()
    {
        $this->manage();
    }
}
