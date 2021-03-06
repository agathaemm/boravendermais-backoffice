<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Treinamentos extends MY_Controller {

    // indica se o controller é publico
	protected $public = false;

   /**
    * __construct
    *
    * metodo construtor
    *
    */
    public function __construct() {
        parent::__construct();
        
        // carrega o finder
        $this->load->finder( [ 'TreinamentosFinder', 'QuestionariosFinder' ] );

        // carrega a librarie de fotos
		$this->load->library( 'Picture' );
        
        // chama o modulo
        $this->view->module( 'navbar' )->module( 'aside' );
    }

   /**
    * _formularioEstados
    *
    * valida o formulario de estados
    *
    */
    private function _formularioTreinamentos() {

        // seta as regras
        $rules = [
            [
                'field' => 'nome',
                'label' => 'Nome',
                'rules' => 'required|min_length[3]|trim'
            ], [
                'field' => 'descricao',
                'label' => 'Descricao',
                'rules' => 'required|min_length[20]|trim'
            ],
        ];

        // valida o formulário
        $this->form_validation->set_rules( $rules );
        return $this->form_validation->run();
    }

   /**
    * index
    *
    * mostra o grid de contadores
    *
    */
	public function index() {

        // faz a paginacao
		$this->TreinamentosFinder->grid()

		// seta os filtros
        ->addFilter( 'Nome', 'text' )
		->filter()
		->order()
		->paginate( 0, 20 )

		// seta as funcoes nas colunas
		->onApply( 'Ações', function( $row, $key ) {
			echo '<a href="'.site_url( 'treinamentos/alterar/'.$row['Código'] ).'" class="margin btn btn-xs btn-info"><span class="glyphicon glyphicon-pencil"></span></a>';
			echo '<a href="'.site_url( 'treinamentos/excluir/'.$row['Código'] ).'" class="margin btn btn-xs btn-danger"><span class="glyphicon glyphicon-trash"></span></a>';            
		})

        // seta as funcoes nas colunas
		->onApply( 'Foto', function( $row, $key ) {
            if( $row[$key] )
			    echo '<img src="'.$row[$key].'" style="width: 50px; height: 50px;">';
            else echo 'Sem Foto';
		})

		// renderiza o grid
		->render( site_url( 'treinamentos/index' ) );
		
        // seta a url para adiciona
        $this->view->set( 'add_url', site_url( 'treinamentos/adicionar' ) );

		// seta o titulo da pagina
		$this->view->setTitle( 'Treinamentos - listagem' )->render( 'grid' );
    }

   /**
    * adicionar
    *
    * mostra o formulario de adicao
    *
    */
    public function adicionar() {

        // Obtem todos os questionários
        $questionarios = $this->QuestionariosFinder->clean()->get();
        $questionarios = $questionarios ? $questionarios : [];
        $this->view->set( 'questionarios', $questionarios );

        // carrega a view de adicionar
        $this->view->setTitle( 'Samsung - Adicionar treinamento' )->render( 'forms/treinamento' );
    }

   /**
    * alterar
    *
    * mostra o formulario de edicao
    *
    */
    public function alterar( $key ) {

        // Obtem todos os questionários
        $questionarios = $this->QuestionariosFinder->clean()->get();
        $questionarios = $questionarios ? $questionarios : [];
        $this->view->set( 'questionarios', $questionarios );

        // carrega o cargo
        $treinamento = $this->TreinamentosFinder->key( $key )->get( true );

        // verifica se o mesmo existe
        if ( !$treinamento ) {
            redirect( 'treinamentos/index' );
            exit();
        }

        // salva na view
        $this->view->set( 'treinamento', $treinamento );

        // carrega a view de adicionar
        $this->view->setTitle( 'Samsung - Alterar treinamento' )->render( 'forms/treinamento' );
    }

   /**
    * excluir
    *
    * exclui um item
    *
    */
    public function excluir( $key ) {
        
        // Verifica se existe um treinamento
        $treinamento = $this->TreinamentosFinder->key( $key )->get( true );
        if ( !$treinamento ) {
            redirect( site_url( 'treinamentos' ) );
            return;
        }

        // Apaga a foto
        $this->picture->delete( $treinamento->foto );
        
        // Tenta deleta o treinamento
        if ( $treinamento->delete() ) {
            flash( 'success', 'Item deletado com sucesso!' );
        } else {
            flash( 'error', 'Erro ao deletar o item' );
        }
        
        // Redireciona para a listagem
        redirect( site_url( 'treinamentos' ) );
        return;
    }

   /**
    * salvar
    *
    * salva os dados
    *
    */
    public function salvar() {

        // faz o upload da imagem
        $file_name = $this->picture->upload( 'foto', [ 'square' => 200 ] );

        if ( $this->input->post( 'cod' ) ) {
            $treinamento = $this->TreinamentosFinder->key( $this->input->post( 'cod' ) )->get( true );
        } else {

            // instancia um novo objeto grpo
            $treinamento = $this->TreinamentosFinder->getTreinamento();            
            $treinamento->setFoto( 'sem-foto.jpg' );
        }

        // Seta os dados do item
        $treinamento->setNome( $this->input->post( 'nome' ) );
        $treinamento->setDescricao( $this->input->post( 'descricao' ) );
        $treinamento->setVideo( $this->input->post( 'video' ) );
        $treinamento->setCod( $this->input->post( 'cod' ) );
        $treinamento->setQuestionario( $this->input->post( 'questionario' ) );

        // Verifica se existe foto
        if ( $file_name ) {
            if( $file_name != 'sem-foto' ) $this->picture->delete( $treinamento->foto );
            $treinamento->setFoto( $file_name );
        }

        // verifica se o formulario é valido
        if ( !$this->_formularioTreinamentos() ) {

            // seta os erros de validacao            
            $this->view->set( 'treinamento', $treinamento );
            $this->view->set( 'errors', validation_errors() );
            
            // carrega a view de adicionar
            $this->view->setTitle( 'Samsung - Adicionar treinamento' )->render( 'forms/treinamento' );
            return;
        }

        // verifica se o dado foi salvo
        if ( $treinamento->save() ) {
            flash( 'success', 'Item salvo com sucesso' );
        } else {
            flash( 'error', 'Erro ao salvar!' );
        }

        // Redireciona para a listagem
        redirect( site_url( 'treinamentos/index' ) );
    }
}

// End of file


