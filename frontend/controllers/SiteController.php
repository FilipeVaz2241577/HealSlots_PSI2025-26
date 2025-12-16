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
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    // NO SiteController.php, no array de behaviors, adicione 'cancelar-reserva':

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['login', 'error', 'signup', 'request-password-reset', 'reset-password', 'verify-email', 'resend-verification-email', 'suporte', 'reserva', 'cancelar-reserva', 'remove-equipamento', 'remove-all-equipamentos', 'solicitar-manutencao-sala', 'solicitar-manutencao-equipamento'], // ADICIONE AS NOVAS AÇÕES
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'contact', 'about', 'dashboard-tecnico', 'dashboard-manutencao', 'marcacoes', 'blocos', 'salas', 'tiposequipamento', 'equipamentos', 'recursos', 'manutencoes', 'detalhe-sala', 'detalhe-equipamento', 'reserva', 'cancelar-reserva', 'remove-equipamento', 'remove-all-equipamentos', 'solicitar-manutencao-sala', 'solicitar-manutencao-equipamento'], // ADICIONE AS NOVAS AÇÕES
                        'allow' => true,
                        'roles' => ['frontOfficeAccess'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                    'cancelar-reserva' => ['post'],
                    'remove-equipamento' => ['post'],
                    'remove-all-equipamentos' => ['post'],
                    'solicitar-manutencao' => ['post'],
                    'solicitar-manutencao-sala' => ['post'],
                    'solicitar-manutencao-equipamento' => ['post'],// ← ADICIONE AQUI
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::class,
            ],
            'captcha' => [
                'class' => \yii\captcha\CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage - Redireciona conforme o role
     *
     * @return mixed
     */
    public function actionIndex()
    {
        // Se o usuário não estiver logado, redireciona para login
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        // Se estiver logado, mostra a página index normal
        return $this->render('index');
    }

    /**
     * Displays suporte page.
     *
     * @return mixed
     */
    public function actionSuporte($assunto = null, $nserie = null)
    {
        $model = new ContactForm();

        // Pré-preencher o assunto se foi passado como parâmetro
        if ($assunto) {
            $model->subject = $assunto;
        }

        // Adicionar número de série ao corpo se foi passado
        if ($nserie) {
            $model->body = "Número de Série do equipamento: $nserie\n\n";
        }

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
     */
    public function actionDashboardTecnico()
    {
        if (!Yii::$app->user->can('frontOfficeAccess')) {
            throw new \yii\web\ForbiddenHttpException('Acesso negado. Apenas técnicos de saúde e administradores podem aceder.');
        }

        return $this->render('dashboard-tecnico');
    }

    /**
     * Dashboard para Assistentes de Manutenção
     */
    public function actionDashboardManutencao()
    {
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
        if (!Yii::$app->user->can('manageBookings')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para gerir marcações.');
        }

        return $this->render('marcacoes');
    }

    /**
     * Mostra as salas de um bloco específico
     */
    /**
     * Gestão de Blocos (TecnicoSaude e Admin)
     */

    public function actionBlocos()
    {
        if (!Yii::$app->user->can('manageRooms')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para gerir blocos.');
        }

        $search = Yii::$app->request->get('search');

        // Buscar blocos
        $query = \common\models\Bloco::find()
            ->with(['salas'])
            ->orderBy(['nome' => SORT_ASC]);

        if ($search) {
            $query->where(['like', 'nome', $search]);
        }

        $blocos = $query->all();

        // Calcular estatísticas usando os métodos do modelo
        $totalBlocos = count($blocos);
        $totalSalas = 0;
        $blocosAtivos = 0;
        $blocosManutencao = 0;
        $blocosDesativados = 0;
        $blocosUso = 0; // VARIÁVEL ADICIONADA AQUI

        foreach ($blocos as $bloco) {
            $totalSalas += $bloco->getSalas()->count();

            if ($bloco->isEstadoAtivo()) {
                $blocosAtivos++;
            } elseif ($bloco->isEstadoManutencao()) {
                $blocosManutencao++;
            } elseif ($bloco->isEstadoDesativado()) {
                $blocosDesativados++;
            } elseif ($bloco->isEstadoUso()) { // VERIFICAÇÃO ADICIONADA AQUI
                $blocosUso++;
            }
        }

        return $this->render('blocos', [
            'blocos' => $blocos,
            'search' => $search,
            'totalBlocos' => $totalBlocos,
            'totalSalas' => $totalSalas,
            'blocosAtivos' => $blocosAtivos,
            'blocosManutencao' => $blocosManutencao,
            'blocosDesativados' => $blocosDesativados,
            'blocosUso' => $blocosUso, // VARIÁVEL PASSADA PARA A VIEW AQUI
        ]);
    }

    /**
     * Mostra as salas de um bloco específico
     */
    public function actionSalas($bloco = null)
    {
        if (!Yii::$app->user->can('manageRooms')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para visualizar salas.');
        }

        // Se não especificar um bloco, mostrar todas as salas
        $blocoModel = $bloco ? \common\models\Bloco::findOne($bloco) : null;

        $query = \common\models\Sala::find()
            ->with(['bloco', 'equipamentos'])
            ->orderBy(['nome' => SORT_ASC]);

        if ($blocoModel) {
            $query->where(['bloco_id' => $bloco]);
        }

        $search = Yii::$app->request->get('search');
        if ($search) {
            $query->andWhere(['or',
                ['like', 'nome', $search],
            ]);
        }

        // Filtro por estado
        $estadoFiltro = Yii::$app->request->get('estado');
        if ($estadoFiltro && in_array($estadoFiltro, array_keys(\common\models\Sala::optsEstado()))) {
            $query->andWhere(['estado' => $estadoFiltro]);
        }

        $salas = $query->all();

        // Contar por estado para estatísticas
        $contagemPorEstado = [];
        $estados = array_keys(\common\models\Sala::optsEstado());

        foreach ($estados as $estado) {
            $queryCount = \common\models\Sala::find()
                ->where($blocoModel ? ['bloco_id' => $bloco] : [])
                ->andWhere(['estado' => $estado]);

            $contagemPorEstado[$estado] = $queryCount->count();
        }

        // Buscar todos os blocos para o dropdown
        $todosBlocos = \common\models\Bloco::find()
            ->orderBy(['nome' => SORT_ASC])
            ->all();

        return $this->render('salas', [
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
        if (!Yii::$app->user->can('manageRooms')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para visualizar detalhes das salas.');
        }

        $sala = \common\models\Sala::findOne($id);

        if (!$sala) {
            throw new \yii\web\NotFoundHttpException('Sala não encontrada.');
        }

        // Buscar equipamentos da sala usando a relação via SalaEquipamento
        $equipamentos = \common\models\Equipamento::find()
            ->joinWith(['tipoEquipamento'])
            ->innerJoin('sala_equipamento', 'equipamento.id = sala_equipamento.idEquipamento')
            ->where(['sala_equipamento.idSala' => $sala->id])
            ->all();

        return $this->render('detalheSala', [
            'sala' => $sala,
            'equipamentos' => $equipamentos,
        ]);
    }

    /**
     * Página de Tipos de Equipamento
     */
    /**
     * Página de Tipos de Equipamento
     */
    public function actionTiposequipamento()
    {
        if (!Yii::$app->user->can('updateEquipmentStatus')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para visualizar equipamentos.');
        }

        $search = Yii::$app->request->get('search');

        // Buscar tipos de equipamento usando o modelo do common
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
            ->orderBy(['tipoEquipamento.id' => SORT_ASC]); // <-- ORDENAÇÃO PELO ID

        if ($search) {
            $query->where(['like', 'tipoEquipamento.nome', $search]);
        }

        $tiposEquipamento = $query->all();

        return $this->render('tiposequipamento', [
            'tiposEquipamento' => $tiposEquipamento,
            'search' => $search,
        ]);
    }

    /**
     * Mostra os equipamentos de uma categoria específica
     */

    /**
     * Mostra os equipamentos de uma categoria específica
     */
    /**
     * Mostra os equipamentos de uma categoria específica
     */
    public function actionEquipamentos($tipo = null)
    {
        if (!Yii::$app->user->can('updateEquipmentStatus')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para visualizar equipamentos.');
        }

        // Buscar tipo de equipamento
        $tipoEquipamento = $tipo ? \common\models\TipoEquipamento::findOne($tipo) : null;

        if (!$tipoEquipamento) {
            throw new \yii\web\NotFoundHttpException('Tipo de equipamento não encontrado.');
        }

        // Buscar equipamentos deste tipo
        $query = \common\models\Equipamento::find()
            ->with(['tipoEquipamento', 'salas'])
            ->where(['tipoEquipamento_id' => $tipo]);

        // Filtro por estado
        $estadoFiltro = Yii::$app->request->get('estado');
        if ($estadoFiltro && in_array($estadoFiltro, ['Operacional', 'Em Manutenção', 'Em Uso'])) {
            $query->andWhere(['estado' => $estadoFiltro]);
        }

        // Filtro por pesquisa de texto
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

        // CORREÇÃO: Contar por estado para estatísticas - usando createCommand para garantir resultado correto
        $contagemPorEstado = [];

        // Primeiro, contar totais (sem filtros)
        $contagemQuery = \common\models\Equipamento::find()
            ->select(['estado', 'COUNT(*) as count'])
            ->where(['tipoEquipamento_id' => $tipo])
            ->groupBy(['estado']);

        // Usar createCommand para obter resultados brutos
        $command = $contagemQuery->createCommand();
        $resultados = $command->queryAll();

        // Transformar em array indexado por estado
        foreach ($resultados as $resultado) {
            $contagemPorEstado[$resultado['estado']] = (int) $resultado['count'];
        }

        // Garantir que todos os estados existam no array
        $estadosPossiveis = ['Operacional', 'Em Manutenção', 'Em Uso'];
        foreach ($estadosPossiveis as $estado) {
            if (!isset($contagemPorEstado[$estado])) {
                $contagemPorEstado[$estado] = 0;
            }
        }

        // Mapear tipos para categorias
        $mapeamentoTiposParaCategorias = [
            1 => 'moveis',           // Equipamentos Móveis
            2 => 'monitorizacao',    // Equipamentos de Monitorização
            3 => 'cirurgicos',       // Instrumentos Cirúrgicos
            4 => 'consumo'           // Materiais de Consumo
        ];

        $categoria = isset($mapeamentoTiposParaCategorias[$tipo])
            ? $mapeamentoTiposParaCategorias[$tipo]
            : null;

        // DEBUG: Adicionar temporariamente para verificar
        Yii::debug('contagemPorEstado: ' . print_r($contagemPorEstado, true), 'equipamentos');
        Yii::debug('equipamentos encontrados: ' . count($equipamentos), 'equipamentos');

        return $this->render('equipamentos', [
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
    /**
     * Mostra os detalhes de um equipamento específico
     */
    public function actionDetalheEquipamento($id)
    {
        if (!Yii::$app->user->can('updateEquipmentStatus')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para visualizar detalhes dos equipamentos.');
        }

        $equipamentoModel = \common\models\Equipamento::findOne($id);

        if (!$equipamentoModel) {
            throw new \yii\web\NotFoundHttpException('Equipamento não encontrado.');
        }

        // Buscar estatísticas reais
        $totalEquipamentosMesmoTipo = \common\models\Equipamento::find()
            ->where(['tipoEquipamento_id' => $equipamentoModel->tipoEquipamento_id])
            ->count();

        $estatisticas = \common\models\Equipamento::find()
            ->select(['estado', 'COUNT(*) as count'])
            ->where(['tipoEquipamento_id' => $equipamentoModel->tipoEquipamento_id])
            ->groupBy(['estado'])
            ->asArray()
            ->all();

        return $this->render('detalheEquipamento', [
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
        if (!Yii::$app->user->can('viewResources')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para visualizar recursos.');
        }

        return $this->render('recursos');
    }

    public function actionManutencoes()
    {
        if (!Yii::$app->user->can('manageMaintenance')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para gerir manutenções.');
        }

        return $this->render('manutencoes');
    }


    // NO SiteController.php, método actionReserva:

    /**
     * Página de requisição de sala
     */
    public function actionReserva($id)
    {
        if (!Yii::$app->user->can('frontOfficeAccess')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para requisitar salas.');
        }

        $sala = \common\models\Sala::findOne($id);

        if (!$sala) {
            throw new \yii\web\NotFoundHttpException('Sala não encontrada.');
        }

        // Buscar todos os equipamentos disponíveis (estado Operacional)
        $equipamentosDisponiveis = \common\models\Equipamento::find()
            ->where(['estado' => 'Operacional'])
            ->with(['tipoEquipamento'])
            ->all();

        // Buscar equipamentos já associados à sala
        $equipamentosSala = $sala->getEquipamentos()
            ->with(['tipoEquipamento'])
            ->all();

        // Se o formulário foi submetido via POST
        if (Yii::$app->request->isPost) {
            $selectedEquipamentos = Yii::$app->request->post('equipamentos', []);
            $dataReserva = Yii::$app->request->post('data_reserva'); // Data no formato YYYY-MM-DD
            $horaInicio = Yii::$app->request->post('hora_inicio');   // Hora no formato HH:MM
            $horaFim = Yii::$app->request->post('hora_fim');         // Hora no formato HH:MM
            $observacoes = Yii::$app->request->post('observacoes');

            // Validar dados
            $errors = [];

            if (empty($dataReserva)) {
                $errors[] = 'Por favor, selecione uma data.';
            } else {
                // Validar formato da data
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

            // Verificar se a sala está disponível na data/hora selecionada
            if (empty($errors) && $dataReserva && $horaInicio) {
                // Criar datetime para início
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

            if (empty($errors)) {
                // Verificar se a sala está disponível usando o novo método
                if (!$sala->isDisponivelParaReserva()) {
                    Yii::$app->session->setFlash('error', 'Esta sala não está disponível para requisição. Estado atual: ' . $sala->getEstadoLabel());
                    return $this->refresh();
                } else {
                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        // Criar requisição
                        $requisicao = new \common\models\Requisicao();
                        $requisicao->user_id = Yii::$app->user->id;
                        $requisicao->sala_id = $sala->id;
                        $requisicao->dataInicio = $dataReserva . ' ' . $horaInicio . ':00';
                        $requisicao->dataFim = $dataReserva . ' ' . $horaFim . ':00';
                        $requisicao->status = \common\models\Requisicao::STATUS_ATIVA;

                        if (!$requisicao->save()) {
                            throw new \Exception('Erro ao criar requisição: ' . implode(', ', $requisicao->getFirstErrors()));
                        }

                        // Associar equipamentos selecionados à requisição
                        if (!empty($selectedEquipamentos)) {
                            foreach ($selectedEquipamentos as $equipamentoId) {
                                $equipamento = \common\models\Equipamento::findOne($equipamentoId);
                                if ($equipamento) {
                                    // Verificar se o equipamento está realmente operacional
                                    if ($equipamento->estado !== 'Operacional') {
                                        throw new \Exception("O equipamento {$equipamento->equipamento} não está disponível. Estado atual: {$equipamento->estado}");
                                    }

                                    // Verificar se o equipamento já não está reservado para este horário
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

                                    // Associar equipamento à requisição
                                    $requisicaoEquipamento = new \common\models\RequisicaoEquipamento();
                                    $requisicaoEquipamento->idRequisicao = $requisicao->id;
                                    $requisicaoEquipamento->idEquipamento = $equipamentoId;

                                    if (!$requisicaoEquipamento->save()) {
                                        throw new \Exception('Erro ao associar equipamento à requisição.');
                                    }

                                    // Atualizar estado do equipamento para "Em Uso" usando a constante
                                    $equipamento->estado = \common\models\Equipamento::ESTADO_EM_USO;
                                    if (!$equipamento->save(false)) {
                                        throw new \Exception('Erro ao atualizar estado do equipamento.');
                                    }

                                    // Associar equipamento à sala (se ainda não estiver associado)
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

                        // ATUALIZAR ESTADO DA SALA PARA "Requisitada" - USANDO A CONSTANTE
                        // ADICIONE LOGS PARA DEBUG
                        Yii::info("=== ANTES DE ATUALIZAR ESTADO DA SALA ===");
                        Yii::info("Estado atual: " . $sala->estado);
                        Yii::info("Estado label: " . $sala->getEstadoLabel());

                        $sala->estado = \common\models\Sala::ESTADO_EM_USO;

                        Yii::info("Novo estado definido: " . $sala->estado);
                        Yii::info("Constante ESTADO_REQUISITADA: " . \common\models\Sala::ESTADO_EM_USO);

                        if (!$sala->save(false)) {
                            Yii::error("Erros ao salvar sala: " . print_r($sala->errors, true));
                            throw new \Exception('Erro ao atualizar estado da sala.');
                        }

                        Yii::info("=== DEPOIS DE SALVAR ===");
                        Yii::info("Estado salvo: " . $sala->estado);
                        Yii::info("Estado label após salvar: " . $sala->getEstadoLabel());

                        $transaction->commit();

                        Yii::$app->session->setFlash('success',
                            'Requisição criada com sucesso! ' .
                            'Código da requisição: #' . $requisicao->id . ' ' .
                            (empty($selectedEquipamentos) ? '' : 'Equipamentos requisitados: ' . count($selectedEquipamentos)));

                        return $this->redirect(['site/detalhe-sala', 'id' => $id]);

                    } catch (\Exception $e) {
                        $transaction->rollBack();
                        Yii::$app->session->setFlash('error', 'Erro ao processar a requisição: ' . $e->getMessage());
                        Yii::error("Erro na actionReserva: " . $e->getMessage());
                        Yii::error("Stack trace: " . $e->getTraceAsString());
                    }
                }
            } else {
                Yii::$app->session->setFlash('error', implode('<br>', $errors));
            }
        }

        return $this->render('reserva', [
            'sala' => $sala,
            'equipamentosDisponiveis' => $equipamentosDisponiveis,
            'equipamentosSala' => $equipamentosSala,
        ]);
    }

    // NO SiteController.php, adicione este método APÓS o actionReserva:

    /**
     * Cancela uma reserva ativa
     */
    // No SiteController.php ou onde está a ação cancelar-reserva
    // No SiteController.php
    public function actionCancelarReserva($id)
    {
        // As classes já devem estar importadas no topo do arquivo
        $sala = Sala::findOne($id);
        if (!$sala) {
            throw new NotFoundHttpException("Sala não encontrada.");
        }

        // Buscar todas as reservas ativas do usuário nesta sala
        $reservasAtivas = Requisicao::find()
            ->where([
                'user_id' => Yii::$app->user->id,
                'sala_id' => $sala->id,
            ])
            ->andWhere(['status' => 'Ativa']) // Só buscar as ativas
            ->all();

        if (empty($reservasAtivas)) {
            Yii::$app->session->setFlash('info', 'Não tem reservas ativas para cancelar nesta sala.');
            return $this->redirect(['site/detalhe-sala', 'id' => $sala->id]);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($reservasAtivas as $reserva) {
                // 1. Buscar equipamentos associados a esta reserva
                $equipamentosReserva = $reserva->getIdEquipamentos()->all();

                // 2. Devolver equipamentos ao estado "Operacional"
                foreach ($equipamentosReserva as $equipamento) {
                    $equipamento->estado = Equipamento::ESTADO_OPERACIONAL;
                    $equipamento->save(false);
                }

                // 3. Remover associação da tabela requisicao_equipamento
                RequisicaoEquipamento::deleteAll(['idRequisicao' => $reserva->id]);

                // 4. Cancelar a reserva
                $reserva->status = 'Cancelada';
                $reserva->save(false);
            }

            // 5. Verificar se há outras reservas ativas na sala
            $outrasReservasAtivas = Requisicao::find()
                ->where([
                    'sala_id' => $sala->id,
                    'status' => 'Ativa'
                ])
                ->exists();

            // 6. Atualizar sala para Livre se não houver outras reservas
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
        // Verificação simples - apenas usuários logados podem remover
        if (Yii::$app->user->isGuest) {
            Yii::$app->session->setFlash('error', 'Você precisa estar logado para remover equipamentos.');
            return $this->redirect(['site/login']);
        }

        // Lógica para remover o equipamento da sala
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
     * Solicita manutenção para um equipamento e atualiza estado da sala
     */
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

        // Verificar se a sala já está em manutenção
        if ($sala->estado === \common\models\Sala::ESTADO_MANUTENCAO) {
            Yii::$app->session->setFlash('info', 'Esta sala já está em manutenção.');
            return $this->redirect(['site/detalhe-sala', 'id' => $id]);
        }

        // Mudar estado da sala para Manutenção
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

        // Verificar se o equipamento já está em manutenção
        if ($equipamento->estado === \common\models\Equipamento::ESTADO_MANUTENCAO) {
            Yii::$app->session->setFlash('info', 'Este equipamento já está em manutenção.');
            return $this->redirect(['site/detalhe-equipamento', 'id' => $id]);
        }

        // Mudar estado do equipamento para Manutenção
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
     * Login action.
     *
     * @return string|Response
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $this->layout = 'login';

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            // Verificar se o user tem acesso ao frontend
            if (Yii::$app->user->can('frontOfficeAccess')) {
                return $this->goBack();
            } else {
                // Se não tiver acesso ao frontend (AssistenteManutencao), redirecionar para backend
                Yii::$app->user->logout();
                Yii::$app->session->setFlash('error', 'Não tem acesso ao frontend. Utilize o backend para aceder às suas funcionalidades.');
                return $this->refresh();
            }
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
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
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        // Definir layout sem navbar
        $this->layout = 'login';

        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            Yii::$app->session->setFlash('success', 'Registo efetuado com sucesso. Já pode fazer login.');
            return $this->goHome();
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
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
     * Resets password.
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
            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    /**
     * Verify email address
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
            return $this->goHome();
        }

        Yii::$app->session->setFlash('error', 'Não foi possível verificar a sua conta com o token fornecido.');
        return $this->goHome();
    }

    /**
     * Resend verification email
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