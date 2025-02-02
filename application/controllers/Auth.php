<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public   function __construct()
    {
        parent::__construct();
        // if ($this->session->userdata('email') && $this->session->userdata('role_id') == 2) {
        //     redirect('tutor');
        // } else if ($this->session->userdata('email') && $this->session->userdata('role_id') == 3) {
        //     redirect('pelajar');
        // }
    }
    public function index()
    {
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email', [
            'valid_email' => 'Email tidak valid',
            'required' => 'Email dibutuhkan untuk login'
        ]);
        $this->form_validation->set_rules('password', 'Password', 'trim|required');

        if ($this->form_validation->run() == false) {
            $data['title'] = 'Login tutory';
            $this->load->view('templates/header', $data);
            $this->load->view('login/index', $data);
            $this->load->view('templates/footer');
        } else {
            // validasinya success
            $this->_login();
        }
    }
    private function _login()
    {
        $email = $this->input->post('email');
        $password = $this->input->post('password');

        $user = $this->db->get_where('mahasiswa', ['email' => $email])->row_array();

        // jika usernya ada
        if ($user) {
            // jika usernya aktif
            if ($user['is_active'] == 1) {
                // cek password
                if (password_verify($password, $user['password'])) {
                    $data = [
                        'email' => $user['email'],
                        'role_id' => $user['role_id']
                    ];
                    $this->session->set_userdata($data);
                    if ($user['role_id'] == 1) {
                        redirect('admin');
                    } else if ($user['role_id'] == 2) {
                        redirect('tutor');
                    } else redirect('pelajar');
                } else {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Password salah!</div>');
                    redirect('auth');
                }
            } else {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Email ini belum aktif</div>');
                redirect('auth');
            }
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Email ini tidak terdaftar</div>');
            redirect('auth');
        }
    }
    public function email_check($str)
    {
        if (stristr($str, '@apps.ipb.ac.id') !== false) return true;
        $this->form_validation->set_message('email_check', 'Harus menggunakan email IPB University');
        return FALSE;
    }
    public function regisPelajar()
    {
        $this->form_validation->set_rules('nama', 'Nama', 'required|trim');
        $this->form_validation->set_rules('fakultas', 'Fakultas', 'required|trim');
        $this->form_validation->set_rules('jurusan', 'Jurusan', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[mahasiswa.email]|callback_email_check', [
            'is_unique' => 'Email ini telah digunakan'
            // 'email_check' => 'Harus menggunakan email IPB University'
        ]);
        $this->form_validation->set_rules('password1', 'Password', 'required|trim|min_length[3]|matches[password2]', [
            'matches' => 'Password tidak sama',
            'min_length' => 'Password terlalu pendek'
        ]);
        $this->form_validation->set_rules('password2', 'Password', 'required|trim|matches[password1]');

        if ($this->form_validation->run() == false) {

            $data['fakultas'] = $this->db->get('fakultas')->result_array();
            $data['title'] = 'Tutory pelajar registration';
            $this->load->view('templates/header', $data);
            $this->load->view('daftar/pelajar', $data);
            $this->load->view('templates/footer');
        } else {
            $email = $this->input->post('email', true);
            $data = [
                'nama' => htmlspecialchars($this->input->post('nama', true)),
                'nim' => htmlspecialchars($this->input->post('nim', true)),
                'email' => htmlspecialchars($email),
                'fakultas' => htmlspecialchars($this->input->post('fakultas', true)),
                'jurusan' => $this->input->post('jurusan', true),
                'password' => password_hash($this->input->post('password1'), PASSWORD_DEFAULT),
                'image' => 'default.jpg',
                'date_created' => time(),
                'is_active' => 1,
                'role_id' => 2
            ];

            $this->db->insert('mahasiswa', $data);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Congratulation! your account has been created. Please Login</div>');
            redirect("Auth");
        }
    }

    public function regisTutor()
    {
        $data['fakultas'] = $this->db->get('fakultas')->result_array();

        $data['matkul'] = $this->db->get('matkul')->result_array();

        $data['title'] = 'Tutory';
        $this->load->view('templates/header', $data);
        $this->load->view('daftar/tutor', $data);
        $this->load->view('templates/footer');
    }

    public function cekJurusan()
    {
        echo json_encode($this->db->get_where('jurusan', array('fakultas_id' => $_POST['id']))->result_object());
    }

    public function logout()
    {
        $this->session->unset_userdata('email');
        $this->session->unset_userdata('role_id');

        $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">You have been logged out!</div>');
        redirect('auth');
    }
}
