<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Usuarios extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model('usuarios_model', 'modelusuarios');
	}

	public function index()
	{
		$this->load->view('frontend/template/html-header');
		$this->load->view('frontend/cadastro');
		$this->load->view('frontend/template/footer');
		$this->load->view('frontend/template/html-footer');
	}

	public function inserir(){
		$this->load->library('form_validation');
		$this->form_validation->set_rules('txt-nome', 'Nome do usuário', 'required|min_length[3]'); #nome
		$this->form_validation->set_rules('txt-email', 'Email', 'required|valid_email'); #email
		$this->form_validation->set_rules('txt-senha', 'Senha', 'required|min_length[3]'); #senha
		$this->form_validation->set_rules('txt-confir-senha', 'Confirmar senha', 'required|matches[txt-senha]'); #senha
		$this->form_validation->set_rules('txt-projeto', 'Projeto', 'required|min_length[3]'); #projeto

		if($this->form_validation->run() == FALSE){
			$this->index();
		}else{
			$nome= $this->input->post('txt-nome');
			$email= $this->input->post('txt-email');
			$senha= $this->input->post('txt-senha');
			$projeto= $this->input->post('txt-projeto');
			$criptografia = base64_encode($senha);
			if ($this->modelusuarios->adicionar($nome, $email, $projeto, $criptografia)) {
				$id_usuario = $this->modelusuarios->recuperar_id($nome, $criptografia);
				if ($projeto == "ed1") {
					$this->modelusuarios->preencher_ed1($id_usuario, 0, 0, 0);
				}
				//elseif ($projeto == "nti"){
				//	$this->modelusuarios->preencher_nti($id_usuario, 0, 0);
				//}
				redirect(base_url('login'));
			}else{
				echo "Houve um erro no sistema";
			}
		}
	}

	public function excluir($id){
		if ($this->usuarios_model->excluir($id)) {
			redirect(base_url(home));
		}else{
			echo "Houve um erro no sistema!";
		}
	}

	public function pag_cadastro(){
		$this->load->view('frontend/template/html-header');
		$this->load->view('frontend/cadastro');
		$this->load->view('frontend/template/html-footer');
	}


	public function pag_login(){
		$this->load->view('frontend/template/html-header');
		$this->load->view('frontend/login');
		$this->load->view('frontend/template/html-footer');
	}

	public function login(){
		$this->load->library('form_validation');
		$this->form_validation->set_rules('txt-user', 'Usuário', 'required|min_length[3]');
		$this->form_validation->set_rules('txt-senha', 'Senha', 'required|min_length[3]');

		if($this->form_validation->run() == FALSE){
			$this->pag_login();
		}else{
			$usuario = $this->input->post('txt-user');
			$senha = $this->input->post('txt-senha');
			$criptografia = base64_encode($senha);
			$this->db->where('nome', $usuario);
			$this->db->where('senha', $criptografia);
			$userlogado = $this->db->get('usuarios')->result();

			if(count($userlogado)==1){
				$dadosSessao['userlogado'] = $userlogado[0];
				$dadosSessao['logado'] = TRUE;
				$this->session->set_userdata($dadosSessao);
				
				if($this->session->userdata('userlogado')->projeto == "ed1"){
					redirect(base_url('ed1'));
				} 
				elseif ($this->session->userdata('userlogado')->projeto == "nti"){
					redirect(base_url('nti'));
				}
				elseif($this->session->userdata('userlogado')->projeto == "admin"){
					redirect(base_url('admin'));
				}
				else {
					redirect(base_url('login'));
				}
			}else{
				$dadosSessao['userlogado'] = NULL;
				$dadosSessao['logado'] = FALSE;
				$this->session->set_userdata($dadosSessao);
				redirect(base_url('login'));
			}
		}
	}

	public function logout(){
		$dadosSessao['userlogado'] = NULL;
		$dadosSessao['logado'] = FALSE;
		$this->session->set_userdata($dadosSessao);
		redirect(base_url('login'));
	}
}
