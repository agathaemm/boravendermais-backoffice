<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Funcionarios extends MY_Controller {

    /**
     * Indica se um controller é público ou não
     *
     * @var boolean
     */
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
        $this->load->finder( [ 'LojasFinder', 'FuncionariosFinder', 'EstadosFinder', 'CidadesFinder' ] );
        
        // chama o modulo
        $this->view->module( 'navbar' )->module( 'aside' )->module( 'jquery-mask' );
    }

   /**
    * _formularioEstados
    *
    * valida o formulario de estados
    *
    */
    private function _formularioFuncionario() {

        // seta as regras
        $rules = [
            [
                'field' => 'loja',
                'label' => 'Loja',
                'rules' => 'trim'
            ], [
                'field' => 'cpf',
                'label' => 'CPF',
                'rules' => 'required|trim'
            ], [
                'field' => 'nome',
                'label' => 'Nome',
                'rules' => 'min_length[3]|trim'
            ], [
                'field' => 'cargo',
                'label' => 'Cargo',
                'rules' => 'required'
            ], [
                'field' => 'pontos',
                'label' => 'Pontos',
                'rules' => 'numeric'
            ],  [
                'field' => 'endereco',
                'label' => 'Endereco',
                'rules' => 'min_length[3]|trim'
            ], [
                'field' => 'numero',
                'label' => 'Numero',
                'rules' => 'min_length[1]|trim'
            ], [
                'field' => 'complemento',
                'label' => 'Complemento',
                'rules' => 'min_length[3]|trim'
            ], [
                'field' => 'cep',
                'label' => 'Bairro',
                'rules' => 'min_length[9]|trim'
            ], [
                'field' => 'cidade',
                'label' => 'Cidade',
                'rules' => 'min_length[1]|trim'
            ], [
                'field' => 'estado',
                'label' => 'Estado',
                'rules' => 'min_length[1]|trim'
            ], [
                'field' => 'neoCode',
                'label' => 'neoCode',
                'rules' => 'required'
            ], [
                'field' => 'uid',
                'label' => 'UID',
                'rules' => 'trim'
            ]
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

        // carrega os categorias
        $lojas = $this->LojasFinder->filtro();

        // faz a paginacao
		$this->FuncionariosFinder->clean()->grid()

		// seta os filtros
        ->addFilter( 'CPF', 'text' )
        ->addFilter( 'NeoCode', 'text' )
        ->addFilter( 'UID', 'text' )
        ->addFilter( 'Nome', 'text', false, 'f' )
        ->addFilter( 'CodLoja', 'select', $lojas, 'f' )
		->filter( true )
		->order()
		->paginate( 0, 20 )

		// seta as funcoes nas colunas
		->onApply( 'Ações', function( $row, $key ) {
			echo '<a href="'.site_url( 'funcionarios/alterar/'.$row[$key] ).'" class="margin btn btn-xs btn-info"><span class="glyphicon glyphicon-pencil"></span></a>';
			echo '<a href="'.site_url( 'funcionarios/excluir/'.$row[$key] ).'" class="margin btn btn-xs btn-danger"><span class="glyphicon glyphicon-trash"></span></a>';            
		})

        // formata o Cnpj para exibicao
        ->onApply( 'CPF', function( $row, $key ) {
			echo mascara_cpf( $row[$key] );        
		})
        ->onApply( 'Pontos', function( $row, $key ) {
            if ( empty( $row[$key] ) ) {
                echo '0';
            } else echo $row[$key];
        })
        // formata o Cnpj para exibicao
        ->onApply( 'PontosVendas', function( $row, $key ) {
            $f = $this->FuncionariosFinder->getFuncionario();
            $pt = $f->getPontosVendas( $row[$key] );
			echo $pt;        
		})
        ->onApply( 'PontosQuiz', function( $row, $key ) {
            $f = $this->FuncionariosFinder->getFuncionario();
            $pt = $f->getPontosQuiz( $row[$key] );
			echo $pt;        
		})

		// renderiza o grid
		->render( site_url( 'funcionarios/index' ) );
		
        // seta a url para adiciona
        $this->view->set( 'add_url', site_url( 'funcionarios/adicionar' ) )
        ->set( 'import_url', site_url( 'funcionarios/importar_planilha' ) )
        ->set( 'example_url', site_url( 'exemplos/funcionarios' ) )
        ->set( 'export_url', site_url( 'funcionarios/exportar_planilha' ) );

		// seta o titulo da pagina
		$this->view->setTitle( 'Funcionários - listagem' )->render( 'grid' );
    }

   /**
    * exportar_planilha
    *
    * exportar a planilha
    *
    */
    public function exportar_planilha() {

        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=FuncionariosExportação".date( 'H:i d-m-Y', time() ).".xls" );

        // faz a paginacao
		$this->FuncionariosFinder->clean()->exportar()
        ->paginate( 1, 0, false, false )

        ->onApply( '*', function( $row, $key ) {
            echo strtoupper( mb_convert_encoding( $row[$key], 'UTF-16LE', 'UTF-8' ) );
        })

        // formata o Cnpj para exibicao
        ->onApply( 'PontosVendas', function( $row, $key ) {
            $f = $this->FuncionariosFinder->getFuncionario();
            $pt = $f->getPontosVendas( $row[$key] );
			echo $pt;        
		})
        ->onApply( 'PontosQuiz', function( $row, $key ) {
            $f = $this->FuncionariosFinder->getFuncionario();
            $pt = $f->getPontosQuiz( $row[$key] );
			echo $pt;        
		})
        

		// renderiza o grid
		->render( site_url( 'funcionarios/index' ) );

		// seta o titulo da pagina
		$html = $this->view->component( 'table' );
    }

   /**
    * adicionar
    *
    * mostra o formulario de adicao
    *
    */
    public function adicionar() {

        // carrega o jquery mask
        $this->view->module( 'jquery-mask' );
        
        // carrega os estados
        $estados = $this->EstadosFinder->get();
        $this->view->set( 'estados', $estados );

        // carrega os lojas
        $lojas = $this->LojasFinder->get();
        $this->view->set( 'lojas', $lojas );

        // carrega a view de adicionar
        $this->view->setTitle( 'Samsung - Adicionar funcionário' )->render( 'forms/funcionario' );
    }

   /**
    * alterar
    *
    * mostra o formulario de edicao
    *
    */
    public function alterar( $key ) {

         // carrega o jquery mask
        $this->view->module( 'jquery-mask' );

        // carrega o classificacao
        $funcionario = $this->FuncionariosFinder->key( $key )->get( true );
        
        // carrega os estados
        $estados = $this->EstadosFinder->get();
        $this->view->set( 'estados', $estados );

        // carrega os lojas
        $lojas = $this->LojasFinder->get();
        $this->view->set( 'lojas', $lojas );

        // verifica se o mesmo existe
        if ( !$funcionario ) {
            redirect( 'funcionarios/index' );
            exit();
        }

        if( $funcionario->estado ) {
        
            // carrega as cidades
            $cidades = $this->CidadesFinder->clean()->porEstado( $funcionario->estado )->get();
            $this->view->set( 'cidades', $cidades );
        }

        // salva na view
        $this->view->set( 'funcionario', $funcionario );

        // carrega a view de adicionar
        $this->view->setTitle( 'Samsung - Alterar funcionário' )->render( 'forms/funcionario' );
    }

   /**
    * excluir
    *
    * exclui um item
    *
    */
    public function excluir( $key ) {
        $funcionario = $this->FuncionariosFinder->getFuncionario();
        $funcionario->setCod( $key );
        $funcionario->delete();
        $this->index();
    }

   /**
    * salvar
    *
    * salva os dados
    *
    */
    public function salvar() {
        
        // carrega os lojas
        $lojas = $this->LojasFinder->get();
        $this->view->set( 'lojas', $lojas );

        // Obtem os dados
        $search  = array('.','/','-','(',')',' ');
        $cpf     = str_replace ( $search , '' , $this->input->post( 'cpf' ) );
        $cep     = str_replace ( $search , '' , $this->input->post( 'cep' ) );
        $celular = str_replace ( $search , '' , $this->input->post( 'celular' ) );
        $rg      = str_replace ( $search , '' , $this->input->post( 'rg' ) );

        // instancia um novo objeto classificacao
        if( $this->input->post( 'cod' ) ) {
            $funcionario = $this->FuncionariosFinder->clean()->key( $this->input->post( 'cod' ) )->get( true );
        } else {
            $funcionario = $this->FuncionariosFinder->getFuncionario();
        }

        // Seta os dados do funcionário
        $funcionario->setLoja( $this->input->post( 'loja' ) );
        $funcionario->setCpf( $cpf );
        $funcionario->setEmail( $this->input->post( 'email' ) );
        $funcionario->setNeoCode( $this->input->post( 'neoCode' ) );
        $funcionario->setUid( $this->input->post( 'uid' ) );
        $funcionario->setNome( $this->input->post( 'nome' ) );
        $funcionario->setCargo( $this->input->post( 'cargo' ) );
        $funcionario->setPontos( $this->input->post( 'pontos' ) );
        $funcionario->setEndereco( $this->input->post( 'endereco' ) );
        $funcionario->setNumero( $this->input->post( 'numero' ) );
        $funcionario->setComplemento( $this->input->post( 'complemento' ) );
        $funcionario->setCep( $cep );
        $funcionario->setCidade( $this->input->post( 'cidade' ) );
        $funcionario->setEstado( $this->input->post( 'estado' ) );
        $funcionario->setCelular( $celular );
        $funcionario->setRg( $rg );
        $funcionario->setNascimento( $this->input->post( 'nascimento' ) );

        // verifica se o formulario é valido
        if ( !$this->_formularioFuncionario() ) {

            // seta os erros de validacao            
            $this->view->set( 'funcionario', $funcionario );
            $this->view->set( 'errors', validation_errors() );
            
            // carrega a view de adicionar
            $this->view->setTitle( 'Samsung - Adicionar funcionário' )->render( 'forms/funcionario' );
            return;
        }

        // verifica se o dado foi salvo
        if ( $funcionario->save() ) {
            flash( 'success', 'Item salvo com sucesso!' );
        } else {
            flash( 'error', 'Erro ao salvar o item, tente novamente mais tarde.' );
        }

        redirect( site_url( 'funcionarios/index' ) );
    }

    /**
    * verificaEntidade
    *
    * verifica se um entidade existe no banco
    *
    */
    public function verificaEntidade( $finder, $method, $dado, $nome, $planilha, $linha, $attr, $status ) {

        // carrega o finder de logs
        $this->load->finder( 'LogsFinder' );

        // verifica se nao esta vazio
        if ( in_cell( $dado ) ) {

            // carrega o finder
            $this->load->finder( $finder );

            // pega a entidade
            if ( $entidade = $this->$finder->clean()->$method( $dado )->get( true ) ) {
                return $entidade->$attr;
            } else {

                // grava o log
                $this->LogsFinder->getLog()
                ->setEntidade( $planilha )
                ->setPlanilha( $this->planilhas->filename )
                ->setMensagem( 'O campo '.$nome.' com valor '.$dado.' nao esta gravado no banco - linha '.$linha )
                ->setData( date( 'Y-m-d H:i:s', time() ) )
                ->setStatus( $status )
                ->save();

                // retorna falso
                return null;
            }
        } else {

            // grava o log
            $this->LogsFinder->getLog()
            ->setEntidade( $planilha )
            ->setPlanilha( $this->planilhas->filename )
            ->setMensagem( 'Nenhum '.$nome.' encontrado - linha '.$linha )
            ->setData( date( 'Y-m-d H:i:s', time() ) )
            ->setStatus( $status )            
            ->save();

            // retorna falso
            return null;
        }
    }

   /**
    * importar_linha
    *
    * importa a linha
    *
    */
    public function importar_linha( $row, $num ) {
        $linha = lower_case_keys( $row );
        $this->load->finder( 'LogsFinder' );

        // Verifica se o funcionário já está cadastrado
        $f = $this->FuncionariosFinder->clean()->neoCode( $linha['neocode'] )->get( true );
        if( $f ) {

            // grava o log
            $this->import->insertLine( $linha, 'NEOCODE JA CADASTRADO' );
            return;
        }

        // percorre todos os campos
        foreach( $linha as $chave => $coluna ) {
            $linha[$chave] = in_cell( $linha[$chave] ) ? $linha[$chave] : null;
        }

        // Pega todas as lojas
        $lojas   = $this->LojasFinder->clean()->get();
        $percent = 0;
        $CodLoja = null;

        // Verifica a similaridade entre o nome da loja e os itens do banck
        foreach ( $lojas as $key => $loja ) {
            $p = $this->similarity( $linha['loja'], $loja->nome );
            if( $p > $percent ) {
                $percent = $p;
                $CodLoja = $loja->CodLoja;
            }
        }

        // Adiciona os zeros na frente do cpf
        if ( !in_cell( $linha['cpf'] ) ) {

            // grava o log
            $this->import->insertLine( $linha, 'SEM CPF INFORMADO' );
            return;
        }
        if( strlen( $linha['cpf'] ) != 11 && $linha['cpf'] > 0 ) {
            $sub = "";
            for ($i=0; $i < 11 - strlen( $linha['cpf'] ) ; $i++) { 
                $sub .= "0";
            }
            $linha['cpf'] = $sub .$linha['cpf'];
        }

        // Obtem o funcionário
        $func = $this->FuncionariosFinder->getFuncionario();

        // preenche os dados
        $func->setNeoCode( $linha['neocode'] );
        $func->setCargo( in_cell( $linha['cargo'] ) ? $linha['cargo']  : 'vendedor' );
        $func->setNome( $linha['nome'] );
        $func->setCpf( $linha['cpf'] );
        $func->setLoja( $CodLoja );

        // tenta salvar a loja
        if ( !$func->save() ) {

            // grava o log
            $this->import->insertLine( $linha, 'ERRO NÃO IDENTIFICADO' );
            return;
        }
    }

   /**
     * Faz a importação da planilha
     *
     * @return void
     */
    public function importar_planilha() {

        // importa a planilha
        $this->load->library( 'Planilhas' );

        // faz o upload da planilha
        $planilha = $this->planilhas->upload();

        // tenta fazer o upload
        if ( !$planilha ) {

            // seta os erros
            $this->view->set( 'errors', $this->planilhas->errors );
        } else {

            // Verifica se o header é válido
            if ( $missing = $this->schema->invalid( $this->planilhas->getHeader(), 'funcionarios' ) ) {
                
                // seta os erros
                array_unshift( $missing, '<br><b>Os campos faltantes são:</b>' );
                array_unshift( $missing, 'Caso tenha duvidas de como importar, clique no botão <b>Visualizar exemplo de Importação</b> logo abaixo.' );
                array_unshift( $missing, 'A planilha que você tentou importar não possui todos os campos requeridos.' );
                $this->view->set( 'errors', $missing );
            } else {
                $this->import->cleanTable();
                $this->planilhas->apply( function( $a, $b ) {
                    $this->importar_linha( $a , $b );
                });
                if ( $this->import->hasNoImportedLines() ) {
                    // seta os erros
                    $this->view->set( 'warnings', [
                        'A importação foi finalizada, porém algumas linhas não foram importadas.',
                        '<b>Para fazer o download de todas as linhas não importadas clique no link abaixo:</b>',
                        '<a href="'.site_url( 'exemplos/export_import_errors').'" target="blank">Clique aqui para baixar a planilha</a>'
                    ]);
                } else {
                    flash( 'success', 'Todas as linhas foram importadas com sucesso!' );
                }
            }

            $planilha->excluir();
        }

        // Abre a index
        $this->index();
    }

    /**
    * similarity
    *
    * calcula a similaridade entre duas strings
    *
    */
    public function similarity($str1, $str2) {
        $len1 = strlen($str1);
        $len2 = strlen($str2);
        
        $max = max($len1, $len2);
        $similarity = $i = $j = 0;
        
        while (($i < $len1) && isset($str2[$j])) {
            if ($str1[$i] == $str2[$j]) {
                $similarity++;
                $i++;
                $j++;
            } elseif ($len1 < $len2) {
                $len1++;
                $j++;
            } elseif ($len1 > $len2) {
                $i++;
                $len1--;
            } else {
                $i++;
                $j++;
            }
        }

        return round($similarity / $max, 2);
    }

   /**
    * importar_linha_neocode
    *
    * importa a linha adicionando o neocode na planilha
    *
    */
    public function importar_linha_neocode( $linha, $num ) {

        // verifica se tem cluster
        if ( !in_cell( $linha['CLUSTER'] ) ) return;

        // carrega o finder do funcionario
        $this->load->finder( [ 'FuncionariosFinder', 'LojasFinder' ] );

        // tenta carregar o funcionario pelo nome
        $funcs = $this->FuncionariosFinder->clean()->nome( strtoupper( $linha['NOMENEO'] ) )->get();

        // verifica quantos funcionarios foram encontrados
        $cont = count( $funcs );

        // faz o switch
        switch( $cont ) {
            case 0:
            break;
            case 1:

                // prepara os dados
                $dados = [
                    'neo'     => str_replace(  '-', '', $linha['COD_NEO'] ),
                    'func_id' => $funcs[0]->CodFuncionario,
                ];

                // insere
                $this->db->insert( 'temp_table', $dados );

            break;
            default:
                foreach( $funcs as $func ) {
                    
                    // pega a loja
                    $loja = $this->LojasFinder->clean()->key( $func->loja )->get( true );

                    // verifica a similiraridade no nome da loja
                    $percent = $this->similarity( $linha['PDV'], $loja->nome );

                    if ( $percent > 0.3 ) {
                        
                        // prepara os dados
                        $dados = [
                            'neo'           => str_replace( '-', '', $linha['COD_NEO'] ),
                            'func_id'       => $func->CodFuncionario,
                        ];

                        // insere
                        $this->db->insert( 'temp_table', $dados );
                    };
                }
            break;
        };
    }

    /**
     * Atualiza os pontos do funcionario
     *
     * @return void
     */
    public function atualizar() {

        $funcs = $this->FuncionariosFinder->get();

        foreach( $funcs as $func ) {
            $func->pontos = $func->getPontosQuiz() + $func->getPontosVendas();
            $func->save();
        }
    }
}

// End of file

