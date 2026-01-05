<?php

namespace frontend\controllers;

use frontend\models\ResendVerificationEmailForm;
use frontend\models\VerifyEmailForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use common\models\Sala;
use common\models\Requisicao;
use common\models\RequisicaoEquipamento;
use common\models\Equipamento;
use common\models\SalaEquipamento;

/**
 * Controlador principal do site (Frontend)
 * Responsável pelas ações públicas e autenticadas do frontend
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     * Define os comportamentos (filters) do controlador
     */
    public function behaviors()
    {
        return [
            'access' => [ // Controlo de acesso baseado em roles
                'class' => AccessControl::class,
                'rules' => [
                    [
                        // Ações permitidas para todos (incluindo não autenticados)
                        'actions' => ['login', 'error', 'signup', 'request-password-reset', 'reset-password', 'verify-email', 'resend-verification-email', 'suporte', 'reserva', 'cancelar-reserva', 'remove-equipamento', 'remove-all-equipamentos', 'solicitar-manutencao-sala', 'solicitar-manutencao-equipamento'],
                        'allow' => true,
                    ],
                    [
                        // Ações permitidas apenas para utilizadores com permissão frontOfficeAccess
                        'actions' => ['logout', 'index', 'contact', 'about', 'dashboard-tecnico', 'dashboard-manutencao', 'marcacoes', 'blocos', 'salas', 'tiposequipamento', 'equipamentos', 'recursos', 'manutencoes', 'detalhe-sala', 'detalhe-equipamento', 'reserva', 'cancelar-reserva', 'remove-equipamento', 'remove-all-equipamentos', 'solicitar-manutencao-sala', 'solicitar-manutencao-equipamento'],
                        'allow' => true,
                        'roles' => ['frontOfficeAccess'],
                    ],
                ],
            ],
            'verbs' => [ // Filtro de verbos HTTP (métodos permitidos)
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                    'cancelar-reserva' => ['post'],
                    'remove-equipamento' => ['post'],
                    'remove-all-equipamentos' => ['post'],
                    'solicitar-manutencao' => ['post'],
                    'solicitar-manutencao-sala' => ['post'],
                    'solicitar-manutencao-equipamento' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     * Define as ações padrão do controlador
     */
    public function actions()
    {
        return [
            'error' => [ // Ação para tratamento de erros
                'class' => \yii\web\ErrorAction::class,
            ],
            'captcha' => [ // Ação para captcha (se necessário)
                'class' => \yii\captcha\CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Exibe a página inicial - Redireciona conforme o role do utilizador
     *
     * @return mixed
     */
    public function actionIndex()
    {
        // Se o utilizador não estiver autenticado, redireciona para login
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        // Se estiver autenticado, redireciona para tiposequipamento
        return $this->redirect(['site/tiposequipamento']);
    }

    /**
     * Exibe a página de suporte
     *
     * @return mixed
     */
    public function actionSuporte($assunto = null, $nserie = null)
    {
        $model = new ContactForm();

        // Pré-preenche o assunto se fornecido
        if ($assunto) {
            $model->subject = $assunto;
        }

        // Pré-preenche o corpo se número de série fornecido
        if ($nserie) {
            $model->body = "Número de Série do equipamento: $nserie\n\n";
        }

        // Processa o formulário de suporte
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $supportEmail = Yii::$app->params['supportEmail'] ?? Yii::$app->params['adminEmail'];

            if ($model->sendEmail($supportEmail)) {
                Yii::$app->session->setFlash('success', 'Obrigado por contactar-nos. Responderemos assim que possível.');
            } else {
                Yii::$app->session->setFlash('error', 'Ocorreu um erro ao enviar a sua mensagem. Por favor tente novamente.');
            }
            return $this->refresh();
        }

        return $this->render('suporte', [
            'model' => $model,
        ]);
    }

    /**
     * Dashboard para Técnicos de Saúde
     * Apenas acessível por Técnicos de Saúde e Administradores
     */
    public function actionDashboardTecnico()
    {
        // Verifica se tem permissão para aceder ao frontend
        if (!Yii::$app->user->can('frontOfficeAccess')) {
            throw new \yii\web\ForbiddenHttpException('Acesso negado. Apenas técnicos de saúde e administradores podem aceder.');
        }

        return $this->render('dashboard-tecnico');
    }

    /**
     * Dashboard para Assistentes de Manutenção
     * Apenas acessível por Assistentes de Manutenção e Administradores
     */
    public function actionDashboardManutencao()
    {
        // Verifica se tem permissão para aceder ao backend (apenas admin tem backOfficeAccess no frontend)
        if (!Yii::$app->user->can('backOfficeAccess')) {
            throw new \yii\web\ForbiddenHttpException('Acesso negado. Apenas assistentes de manutenção e administradores podem aceder.');
        }

        return $this->render('dashboard-manutencao');
    }

    /**
     * Gestão de Marcações (TecnicoSaude e Admin)
     */
    public function actionMarcacoes()
    {
        // Verifica permissão específica para gerir marcações
        if (!Yii::$app->user->can('manageBookings')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para gerir marcações.');
        }

        return $this->render('marcacoes');
    }

    /**
     * Mostra os blocos disponíveis
     */
    public function actionBlocos()
    {
        // Verifica permissão específica para gerir salas
        if (!Yii::$app->user->can('manageRooms')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para gerir blocos.');
        }

        $search = Yii::$app->request->get('search');

        // Query para obter blocos com as suas salas
        $query = \common\models\Bloco::find()
            ->with(['salas'])
            ->orderBy(['nome' => SORT_ASC]);

        // Aplica filtro de pesquisa se fornecido
        if ($search) {
            $query->where(['like', 'nome', $search]);
        }

        $blocos = $query->all();

        // Calcula estatísticas
        $totalBlocos = count($blocos);
        $totalSalas = 0;
        $blocosAtivos = 0;
        $blocosDesativados = 0;
        $blocosUso = 0;

        foreach ($blocos as $bloco) {
            $totalSalas += $bloco->getSalas()->count();

            // Conta blocos por estado
            if ($bloco->isEstadoAtivo()) {
                $blocosAtivos++;
            } elseif ($bloco->isEstadoDesativado()) {
                $blocosDesativados++;
            } elseif ($bloco->isEstadoUso()) {
                $blocosUso++;
            }
        }

        return $this->render('@app/views/blocos/blocos', [
            'blocos' => $blocos,
            'search' => $search,
            'totalBlocos' => $totalBlocos,
            'totalSalas' => $totalSalas,
            'blocosAtivos' => $blocosAtivos,
            'blocosDesativados' => $blocosDesativados,
            'blocosUso' => $blocosUso,
        ]);
    }

    /**
     * Mostra as salas de um bloco específico
     */
    public function actionSalas($bloco = null)
    {
        // Verifica permissão específica para gerir salas
        if (!Yii::$app->user->can('manageRooms')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para visualizar salas.');
        }

        // Obtém o modelo do bloco se especificado
        $blocoModel = $bloco ? \common\models\Bloco::findOne($bloco) : null;

        // Query para obter salas
        $query = \common\models\Sala::find()
            ->with(['bloco', 'equipamentos'])
            ->orderBy(['nome' => SORT_ASC]);

        // Filtra por bloco se especificado
        if ($blocoModel) {
            $query->where(['bloco_id' => $bloco]);
        }

        // Aplica filtro de pesquisa
        $search = Yii::$app->request->get('search');
        if ($search) {
            $query->andWhere(['or',
                ['like', 'nome', $search],
            ]);
        }

        // Aplica filtro por estado
        $estadoFiltro = Yii::$app->request->get('estado');
        if ($estadoFiltro && in_array($estadoFiltro, array_keys(\common\models\Sala::optsEstado()))) {
            $query->andWhere(['estado' => $estadoFiltro]);
        }

        $salas = $query->all();

        // Calcula contagem por estado
        $contagemPorEstado = [];
        $estados = array_keys(\common\models\Sala::optsEstado());

        foreach ($estados as $estado) {
            $queryCount = \common\models\Sala::find()
                ->where($blocoModel ? ['bloco_id' => $bloco] : [])
                ->andWhere(['estado' => $estado]);

            $contagemPorEstado[$estado] = $queryCount->count();
        }

        // Obtém todos os blocos para o dropdown
        $todosBlocos = \common\models\Bloco::find()
            ->orderBy(['nome' => SORT_ASC])
            ->all();

        return $this->render('@app/views/salas/salas', [
            'blocoModel' => $blocoModel,
            'salas' => $salas,
            'search' => $search,
            'estadoFiltro' => $estadoFiltro,
            'contagemPorEstado' => $contagemPorEstado,
            'todosBlocos' => $todosBlocos,
        ]);
    }

    /**
     * Mostra os detalhes de uma sala específica
     */
    public function actionDetalheSala($id)
    {
        // Verifica permissão específica para visualizar salas
        if (!Yii::$app->user->can('manageRooms')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para visualizar detalhes das salas.');
        }

        $sala = \common\models\Sala::findOne($id);

        if (!$sala) {
            throw new \yii\web\NotFoundHttpException('Sala não encontrada.');
        }

        // Obtém os equipamentos associados a esta sala
        $equipamentos = \common\models\Equipamento::find()
            ->joinWith(['tipoEquipamento'])
            ->innerJoin('sala_equipamento', 'equipamento.id = sala_equipamento.idEquipamento')
            ->where(['sala_equipamento.idSala' => $sala->id])
            ->all();

        return $this->render('@app/views/salas/detalheSala', [
            'sala' => $sala,
            'equipamentos' => $equipamentos,
        ]);
    }

    /**
     * Página de Tipos de Equipamento
     */
    public function actionTiposequipamento()
    {
        // Verifica permissão específica para visualizar equipamentos
        if (!Yii::$app->user->can('updateEquipmentStatus')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para visualizar equipamentos.');
        }

        $search = Yii::$app->request->get('search');

        // Query para obter tipos de equipamento com estatísticas
        $query = \common\models\TipoEquipamento::find()
            ->select([
                'tipoEquipamento.*',
                'COUNT(equipamento.id) as quantidadeEquipamentos',
                'SUM(CASE WHEN equipamento.estado = "Operacional" THEN 1 ELSE 0 END) as operacionais',
                'SUM(CASE WHEN equipamento.estado = "Em Manutenção" THEN 1 ELSE 0 END) as em_manutencao',
                'SUM(CASE WHEN equipamento.estado = "Em Uso" THEN 1 ELSE 0 END) as em_uso'
            ])
            ->leftJoin('equipamento', 'equipamento.tipoEquipamento_id = tipoEquipamento.id')
            ->groupBy('tipoEquipamento.id')
            ->orderBy(['tipoEquipamento.id' => SORT_ASC]);

        // Aplica filtro de pesquisa
        if ($search) {
            $query->where(['like', 'tipoEquipamento.nome', $search]);
        }

        $tiposEquipamento = $query->all();

        // NOTA: A view index.php está na pasta equipamentos, não em site
        return $this->render('//equipamentos/index', [  // Note o '//' para ir para a raiz das views
            'tiposEquipamento' => $tiposEquipamento,
            'search' => $search,
        ]);
    }

    /**
     * Mostra os equipamentos de uma categoria específica
     */
    public function actionEquipamentos($tipo = null)
    {
        // Verifica permissão específica para visualizar equipamentos
        if (!Yii::$app->user->can('updateEquipmentStatus')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para visualizar equipamentos.');
        }

        // Obtém o tipo de equipamento
        $tipoEquipamento = $tipo ? \common\models\TipoEquipamento::findOne($tipo) : null;

        if (!$tipoEquipamento) {
            throw new \yii\web\NotFoundHttpException('Tipo de equipamento não encontrado.');
        }

        // Query para obter equipamentos do tipo especificado
        $query = \common\models\Equipamento::find()
            ->with(['tipoEquipamento', 'salas'])
            ->where(['tipoEquipamento_id' => $tipo]);

        // Filtro por estado
        $estadoFiltro = Yii::$app->request->get('estado');
        if ($estadoFiltro && in_array($estadoFiltro, ['Operacional', 'Em Manutenção', 'Em Uso'])) {
            $query->andWhere(['estado' => $estadoFiltro]);
        }

        // Filtro de pesquisa
        $search = Yii::$app->request->get('search');
        if ($search) {
            $query->andWhere(['or',
                ['like', 'equipamento', $search],
                ['like', 'numeroSerie', $search],
            ]);
        }

        // Ordenação
        $sort = Yii::$app->request->get('sort', 'equipamento');
        $order = Yii::$app->request->get('order', 'asc');

        $validSortColumns = ['equipamento', 'estado', 'numeroSerie'];
        $validOrder = in_array(strtolower($order), ['asc', 'desc']) ? strtolower($order) : 'asc';

        if (in_array($sort, $validSortColumns)) {
            $query->orderBy([$sort => $validOrder === 'asc' ? SORT_ASC : SORT_DESC]);
        } else {
            $query->orderBy(['equipamento' => SORT_ASC]);
        }

        $equipamentos = $query->all();

        // Calcula contagem por estado
        $contagemPorEstado = [];

        $contagemQuery = \common\models\Equipamento::find()
            ->select(['estado', 'COUNT(*) as count'])
            ->where(['tipoEquipamento_id' => $tipo])
            ->groupBy(['estado']);

        $command = $contagemQuery->createCommand();
        $resultados = $command->queryAll();

        foreach ($resultados as $resultado) {
            $contagemPorEstado[$resultado['estado']] = (int) $resultado['count'];
        }

        // Garante que todos os estados possíveis estão no array
        $estadosPossiveis = ['Operacional', 'Em Manutenção', 'Em Uso'];
        foreach ($estadosPossiveis as $estado) {
            if (!isset($contagemPorEstado[$estado])) {
                $contagemPorEstado[$estado] = 0;
            }
        }

        // Mapeamento de tipos para categorias (para URLs amigáveis)
        $mapeamentoTiposParaCategorias = [
            1 => 'moveis',
            2 => 'monitorizacao',
            3 => 'cirurgicos',
            4 => 'consumo'
        ];

        $categoria = isset($mapeamentoTiposParaCategorias[$tipo])
            ? $mapeamentoTiposParaCategorias[$tipo]
            : null;

        return $this->render('@app/views/equipamentos/equipamentos', [
            'tipoEquipamento' => $tipoEquipamento,
            'equipamentos' => $equipamentos,
            'search' => $search,
            'estadoFiltro' => $estadoFiltro,
            'contagemPorEstado' => $contagemPorEstado,
            'categoria' => $categoria,
            'sort' => $sort,
            'order' => $order,
        ]);
    }

    /**
     * Mostra os detalhes de um equipamento específico
     */
    public function actionDetalheEquipamento($id)
    {
        // Verifica permissão específica para visualizar equipamentos
        if (!Yii::$app->user->can('updateEquipmentStatus')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para visualizar detalhes dos equipamentos.');
        }

        $equipamentoModel = \common\models\Equipamento::findOne($id);

        if (!$equipamentoModel) {
            throw new \yii\web\NotFoundHttpException('Equipamento não encontrado.');
        }

        // Estatísticas para equipamentos do mesmo tipo
        $totalEquipamentosMesmoTipo = \common\models\Equipamento::find()
            ->where(['tipoEquipamento_id' => $equipamentoModel->tipoEquipamento_id])
            ->count();

        $estatisticas = \common\models\Equipamento::find()
            ->select(['estado', 'COUNT(*) as count'])
            ->where(['tipoEquipamento_id' => $equipamentoModel->tipoEquipamento_id])
            ->groupBy(['estado'])
            ->asArray()
            ->all();

        return $this->render('@app/views/equipamentos/detalheEquipamento', [
            'equipamentoModel' => $equipamentoModel,
            'totalEquipamentos' => $totalEquipamentosMesmoTipo,
            'estatisticas' => $estatisticas
        ]);
    }

    /**
     * Consulta de Recursos (Todos os roles autenticados)
     */
    public function actionRecursos()
    {
        // Verifica permissão específica para visualizar recursos
        if (!Yii::$app->user->can('viewResources')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para visualizar recursos.');
        }

        return $this->render('recursos');
    }

    /**
     * Gestão de Manutenções
     */
    public function actionManutencoes()
    {
        // Verifica permissão específica para gerir manutenções
        if (!Yii::$app->user->can('manageMaintenance')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para gerir manutenções.');
        }

        return $this->render('manutencoes');
    }

    /**
     * Página de requisição de sala
     */
    public function actionReserva($id)
    {
        // Verifica permissão geral para aceder ao frontend
        if (!Yii::$app->user->can('frontOfficeAccess')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para requisitar salas.');
        }

        $sala = \common\models\Sala::findOne($id);

        if (!$sala) {
            throw new \yii\web\NotFoundHttpException('Sala não encontrada.');
        }

        // Obtém equipamentos disponíveis para requisição
        $equipamentosDisponiveis = \common\models\Equipamento::find()
            ->where(['estado' => 'Operacional'])
            ->with(['tipoEquipamento'])
            ->all();

        // Obtém equipamentos já associados à sala
        $equipamentosSala = $sala->getEquipamentos()
            ->with(['tipoEquipamento'])
            ->all();

        // Processa o formulário de requisição
        if (Yii::$app->request->isPost) {
            $selectedEquipamentos = Yii::$app->request->post('equipamentos', []);
            $dataReserva = Yii::$app->request->post('data_reserva');
            $horaInicio = Yii::$app->request->post('hora_inicio');
            $horaFim = Yii::$app->request->post('hora_fim');
            $observacoes = Yii::$app->request->post('observacoes');

            $errors = [];

            // Validações
            if (empty($dataReserva)) {
                $errors[] = 'Por favor, selecione uma data.';
            } else {
                $dateObj = \DateTime::createFromFormat('Y-m-d', $dataReserva);
                if (!$dateObj || $dateObj->format('Y-m-d') !== $dataReserva) {
                    $errors[] = 'Formato de data inválido. Use YYYY-MM-DD.';
                } elseif ($dateObj < new \DateTime('today')) {
                    $errors[] = 'Não é possível requisitar para datas passadas.';
                }
            }

            if (empty($horaInicio)) {
                $errors[] = 'Por favor, selecione a hora de início.';
            }

            if (empty($horaFim)) {
                $errors[] = 'Por favor, selecione a hora de fim.';
            }

            if ($horaInicio && $horaFim && $horaInicio >= $horaFim) {
                $errors[] = 'A hora de fim deve ser posterior à hora de início.';
            }

            // Verifica conflitos de horário
            if (empty($errors) && $dataReserva && $horaInicio) {
                $dataInicio = $dataReserva . ' ' . $horaInicio . ':00';
                $dataFim = $dataReserva . ' ' . $horaFim . ':00';

                $conflictingRequisicao = \common\models\Requisicao::find()
                    ->where(['sala_id' => $sala->id])
                    ->andWhere(['status' => 'Ativa'])
                    ->andWhere(['or',
                        ['and',
                            ['<=', 'dataInicio', $dataInicio],
                            ['>=', 'dataFim', $dataInicio]
                        ],
                        ['and',
                            ['<=', 'dataInicio', $dataFim],
                            ['>=', 'dataFim', $dataFim]
                        ],
                        ['and',
                            ['>=', 'dataInicio', $dataInicio],
                            ['<=', 'dataFim', $dataFim]
                        ]
                    ])
                    ->exists();

                if ($conflictingRequisicao) {
                    $errors[] = 'Esta sala já está requisitada para o horário selecionado.';
                }
            }

            // Se não há erros, tenta criar a requisição
            if (empty($errors)) {
                if (!$sala->isDisponivelParaReserva()) {
                    Yii::$app->session->setFlash('error', 'Esta sala não está disponível para requisição. Estado atual: ' . $sala->getEstadoLabel());
                    return $this->refresh();
                } else {
                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        // Cria a requisição
                        $requisicao = new \common\models\Requisicao();
                        $requisicao->user_id = Yii::$app->user->id;
                        $requisicao->sala_id = $sala->id;
                        $requisicao->dataInicio = $dataReserva . ' ' . $horaInicio . ':00';
                        $requisicao->dataFim = $dataReserva . ' ' . $horaFim . ':00';
                        $requisicao->status = \common\models\Requisicao::STATUS_ATIVA;

                        if (!$requisicao->save()) {
                            throw new \Exception('Erro ao criar requisição: ' . implode(', ', $requisicao->getFirstErrors()));
                        }

                        // Associa equipamentos à requisição
                        if (!empty($selectedEquipamentos)) {
                            foreach ($selectedEquipamentos as $equipamentoId) {
                                $equipamento = \common\models\Equipamento::findOne($equipamentoId);
                                if ($equipamento) {
                                    // Verifica se o equipamento está disponível
                                    if ($equipamento->estado !== 'Operacional') {
                                        throw new \Exception("O equipamento {$equipamento->equipamento} não está disponível. Estado atual: {$equipamento->estado}");
                                    }

                                    // Verifica conflitos de equipamento
                                    $existingRequisicaoEquipamento = \common\models\RequisicaoEquipamento::find()
                                        ->joinWith('idRequisicao0')
                                        ->where(['idEquipamento' => $equipamentoId])
                                        ->andWhere(['requisicao.status' => 'Ativa'])
                                        ->andWhere(['or',
                                            ['and',
                                                ['<=', 'requisicao.dataInicio', $requisicao->dataInicio],
                                                ['>=', 'requisicao.dataFim', $requisicao->dataInicio]
                                            ],
                                            ['and',
                                                ['<=', 'requisicao.dataInicio', $requisicao->dataFim],
                                                ['>=', 'requisicao.dataFim', $requisicao->dataFim]
                                            ],
                                            ['and',
                                                ['>=', 'requisicao.dataInicio', $requisicao->dataInicio],
                                                ['<=', 'requisicao.dataFim', $requisicao->dataFim]
                                            ]
                                        ])
                                        ->exists();

                                    if ($existingRequisicaoEquipamento) {
                                        throw new \Exception("O equipamento {$equipamento->equipamento} já está requisitado para este horário.");
                                    }

                                    // Cria associação equipamento-requisição
                                    $requisicaoEquipamento = new \common\models\RequisicaoEquipamento();
                                    $requisicaoEquipamento->idRequisicao = $requisicao->id;
                                    $requisicaoEquipamento->idEquipamento = $equipamentoId;

                                    if (!$requisicaoEquipamento->save()) {
                                        throw new \Exception('Erro ao associar equipamento à requisição.');
                                    }

                                    // Atualiza estado do equipamento para "Em Uso"
                                    $equipamento->estado = \common\models\Equipamento::ESTADO_EM_USO;
                                    if (!$equipamento->save(false)) {
                                        throw new \Exception('Erro ao atualizar estado do equipamento.');
                                    }

                                    // Associa equipamento à sala se ainda não estiver associado
                                    $salaEquipamento = \common\models\SalaEquipamento::find()
                                        ->where(['idSala' => $sala->id, 'idEquipamento' => $equipamentoId])
                                        ->exists();

                                    if (!$salaEquipamento) {
                                        $novaAssociacao = new \common\models\SalaEquipamento();
                                        $novaAssociacao->idSala = $sala->id;
                                        $novaAssociacao->idEquipamento = $equipamentoId;
                                        if (!$novaAssociacao->save(false)) {
                                            throw new \Exception('Erro ao associar equipamento à sala.');
                                        }
                                    }
                                }
                            }
                        }

                        // Atualiza estado da sala para "Em Uso"
                        $sala->estado = \common\models\Sala::ESTADO_EM_USO;

                        if (!$sala->save(false)) {
                            throw new \Exception('Erro ao atualizar estado da sala.');
                        }

                        $transaction->commit();

                        Yii::$app->session->setFlash('success',
                            'Requisição criada com sucesso! ' .
                            'Código da requisição: #' . $requisicao->id . ' ' .
                            (empty($selectedEquipamentos) ? '' : 'Equipamentos requisitados: ' . count($selectedEquipamentos)));

                        return $this->redirect(['site/detalhe-sala', 'id' => $id]);

                    } catch (\Exception $e) {
                        $transaction->rollBack();
                        Yii::$app->session->setFlash('error', 'Erro ao processar a requisição: ' . $e->getMessage());
                    }
                }
            } else {
                Yii::$app->session->setFlash('error', implode('<br>', $errors));
            }
        }

        return $this->render('@app/views/salas/reserva', [
            'sala' => $sala,
            'equipamentosDisponiveis' => $equipamentosDisponiveis,
            'equipamentosSala' => $equipamentosSala,
        ]);
    }

    /**
     * Cancela uma reserva ativa
     */
    public function actionCancelarReserva($id)
    {
        $sala = Sala::findOne($id);
        if (!$sala) {
            throw new \yii\web\NotFoundHttpException("Sala não encontrada.");
        }

        // Encontra reservas ativas do utilizador para esta sala
        $reservasAtivas = Requisicao::find()
            ->where([
                'user_id' => Yii::$app->user->id,
                'sala_id' => $sala->id,
            ])
            ->andWhere(['status' => 'Ativa'])
            ->all();

        if (empty($reservasAtivas)) {
            Yii::$app->session->setFlash('info', 'Não tem reservas ativas para cancelar nesta sala.');
            return $this->redirect(['site/detalhe-sala', 'id' => $sala->id]);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($reservasAtivas as $reserva) {
                // Obtém equipamentos da reserva
                $equipamentosReserva = $reserva->getIdEquipamentos()->all();

                // Devolve equipamentos ao estado operacional
                foreach ($equipamentosReserva as $equipamento) {
                    $equipamento->estado = Equipamento::ESTADO_OPERACIONAL;
                    $equipamento->save(false);
                }

                // Remove associações equipamento-requisição
                RequisicaoEquipamento::deleteAll(['idRequisicao' => $reserva->id]);

                // Marca reserva como cancelada
                $reserva->status = 'Cancelada';
                $reserva->save(false);
            }

            // Verifica se ainda há outras reservas ativas para a sala
            $outrasReservasAtivas = Requisicao::find()
                ->where([
                    'sala_id' => $sala->id,
                    'status' => 'Ativa'
                ])
                ->exists();

            // Se não houver mais reservas ativas, define sala como livre
            if (!$outrasReservasAtivas) {
                $sala->estado = \common\models\Sala::ESTADO_LIVRE;
                $sala->save(false);
            }

            $transaction->commit();

            Yii::$app->session->setFlash('success', 'Reserva(s) cancelada(s) com sucesso! Equipamentos devolvidos ao estado operacional.');

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'Erro: ' . $e->getMessage());
        }

        return $this->redirect(['site/detalhe-sala', 'id' => $sala->id]);
    }

    /**
     * Remove um equipamento específico de uma sala
     */
    public function actionRemoveEquipamento($sala_id, $equipamento_id)
    {
        if (Yii::$app->user->isGuest) {
            Yii::$app->session->setFlash('error', 'Você precisa estar logado para remover equipamentos.');
            return $this->redirect(['site/login']);
        }

        $salaEquipamento = SalaEquipamento::findOne([
            'idSala' => $sala_id,
            'idEquipamento' => $equipamento_id
        ]);

        if ($salaEquipamento) {
            if ($salaEquipamento->delete()) {
                Yii::$app->session->setFlash('success', 'Equipamento removido da sala com sucesso.');
            } else {
                Yii::$app->session->setFlash('error', 'Erro ao remover equipamento.');
            }
        } else {
            Yii::$app->session->setFlash('error', 'Equipamento não encontrado nesta sala.');
        }

        return $this->redirect(['site/detalhe-sala', 'id' => $sala_id]);
    }

    /**
     * Solicitar manutenção para uma sala
     */
    public function actionSolicitarManutencaoSala($id)
    {
        if (Yii::$app->user->isGuest) {
            Yii::$app->session->setFlash('error', 'Você precisa estar logado para solicitar manutenção.');
            return $this->redirect(['site/login']);
        }

        $sala = \common\models\Sala::findOne($id);

        if (!$sala) {
            throw new \yii\web\NotFoundHttpException('Sala não encontrada.');
        }

        // Verifica se a sala já está em manutenção
        if ($sala->estado === \common\models\Sala::ESTADO_MANUTENCAO) {
            Yii::$app->session->setFlash('info', 'Esta sala já está em manutenção.');
            return $this->redirect(['site/detalhe-sala', 'id' => $id]);
        }

        // Define sala como em manutenção
        $sala->estado = \common\models\Sala::ESTADO_MANUTENCAO;

        if ($sala->save(false)) {
            Yii::$app->session->setFlash('success',
                'Solicitação de manutenção enviada para a sala <strong>' . $sala->nome . '</strong>. ' .
                'O estado da sala foi alterado para "Em Manutenção".');
        } else {
            Yii::$app->session->setFlash('error', 'Erro ao solicitar manutenção.');
        }

        return $this->redirect(['site/detalhe-sala', 'id' => $id]);
    }

    /**
     * Solicitar manutenção para um equipamento
     */
    public function actionSolicitarManutencaoEquipamento($id)
    {
        if (Yii::$app->user->isGuest) {
            Yii::$app->session->setFlash('error', 'Você precisa estar logado para solicitar manutenção.');
            return $this->redirect(['site/login']);
        }

        $equipamento = \common\models\Equipamento::findOne($id);

        if (!$equipamento) {
            throw new \yii\web\NotFoundHttpException('Equipamento não encontrado.');
        }

        // Verifica se o equipamento já está em manutenção
        if ($equipamento->estado === \common\models\Equipamento::ESTADO_MANUTENCAO) {
            Yii::$app->session->setFlash('info', 'Este equipamento já está em manutenção.');
            return $this->redirect(['site/detalhe-equipamento', 'id' => $id]);
        }

        // Define equipamento como em manutenção
        $equipamento->estado = \common\models\Equipamento::ESTADO_MANUTENCAO;

        if ($equipamento->save(false)) {
            Yii::$app->session->setFlash('success',
                'Solicitação de manutenção enviada para o equipamento <strong>' . $equipamento->equipamento . '</strong>. ' .
                'O estado do equipamento foi alterado para "Em Manutenção".');
        } else {
            Yii::$app->session->setFlash('error', 'Erro ao solicitar manutenção.');
        }

        return $this->redirect(['site/detalhe-equipamento', 'id' => $id]);
    }

    /**
     * Ação de login
     *
     * @return string|Response
     */
    public function actionLogin()
    {
        // Se já estiver autenticado, redireciona para tiposequipamento
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['site/tiposequipamento']);
        }

        $this->layout = 'login'; // Usa layout específico para login

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            // Verificar se o utilizador tem acesso ao frontend
            if (Yii::$app->user->can('frontOfficeAccess')) {
                // Redirecionar para tiposequipamento após login bem-sucedido
                return $this->redirect(['site/tiposequipamento']);
            } else {
                // Se não tiver acesso ao frontend (AssistenteManutencao), redirecionar para backend
                Yii::$app->user->logout();
                Yii::$app->session->setFlash('error', 'Não tem acesso ao frontend. Utilize o backend para aceder às suas funcionalidades.');
                return $this->refresh();
            }
        }

        $model->password = ''; // Limpa a password por segurança

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Ação de logout
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->redirect(['site/login']);
    }

    /**
     * Exibe a página de contacto
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Obrigado por contactar-nos. Responderemos assim que possível.');
            } else {
                Yii::$app->session->setFlash('error', 'Ocorreu um erro ao enviar a sua mensagem.');
            }

            return $this->refresh();
        }

        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Exibe a página "sobre"
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Regista um novo utilizador
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $this->layout = 'login'; // Usa layout específico para registo

        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            Yii::$app->session->setFlash('success', 'Registo efetuado com sucesso. Já pode fazer login.');
            // Após registo, redireciona para login (e depois para tiposequipamento após login)
            return $this->redirect(['site/login']);
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Solicita redefinição de password
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Verifique o seu email para mais instruções.');
                return $this->goHome();
            }

            Yii::$app->session->setFlash('error', 'Não foi possível redefinir a password para o email fornecido.');
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Redefine a password
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'Nova password guardada.');
            // Após reset de senha, redireciona para login (e depois para tiposequipamento)
            return $this->redirect(['site/login']);
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    /**
     * Verifica endereço de email
     *
     * @param string $token
     * @throws BadRequestHttpException
     * @return yii\web\Response
     */
    public function actionVerifyEmail($token)
    {
        try {
            $model = new VerifyEmailForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if ($model->verifyEmail()) {
            Yii::$app->session->setFlash('success', 'O seu email foi confirmado!');
            return $this->redirect(['site/login']);
        }

        Yii::$app->session->setFlash('error', 'Não foi possível verificar a sua conta com o token fornecido.');
        return $this->redirect(['site/login']);
    }

    /**
     * Reenvia email de verificação
     *
     * @return mixed
     */
    public function actionResendVerificationEmail()
    {
        $model = new ResendVerificationEmailForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Verifique o seu email para mais instruções.');
                return $this->goHome();
            }
            Yii::$app->session->setFlash('error', 'Não foi possível reenviar o email de verificação.');
        }

        return $this->render('resendVerificationEmail', [
            'model' => $model
        ]);
    }
}