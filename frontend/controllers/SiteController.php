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
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['login', 'error', 'signup', 'request-password-reset', 'reset-password', 'verify-email', 'resend-verification-email', 'suporte', 'reserva', 'cancelar-reserva', 'remove-equipamento', 'remove-all-equipamentos', 'solicitar-manutencao-sala', 'solicitar-manutencao-equipamento'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'contact', 'about', 'dashboard-tecnico', 'dashboard-manutencao', 'marcacoes', 'blocos', 'salas', 'tiposequipamento', 'equipamentos', 'recursos', 'manutencoes', 'detalhe-sala', 'detalhe-equipamento', 'reserva', 'cancelar-reserva', 'remove-equipamento', 'remove-all-equipamentos', 'solicitar-manutencao-sala', 'solicitar-manutencao-equipamento'],
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
                    'solicitar-manutencao-equipamento' => ['post'],
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

        // Se estiver logado, redireciona para tiposequipamento
        return $this->redirect(['site/tiposequipamento']);
    }

    /**
     * Displays suporte page.
     *
     * @return mixed
     */
    public function actionSuporte($assunto = null, $nserie = null)
    {
        $model = new ContactForm();

        if ($assunto) {
            $model->subject = $assunto;
        }

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
    public function actionBlocos()
    {
        if (!Yii::$app->user->can('manageRooms')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para gerir blocos.');
        }

        $search = Yii::$app->request->get('search');

        $query = \common\models\Bloco::find()
            ->with(['salas'])
            ->orderBy(['nome' => SORT_ASC]);

        if ($search) {
            $query->where(['like', 'nome', $search]);
        }

        $blocos = $query->all();

        $totalBlocos = count($blocos);
        $totalSalas = 0;
        $blocosAtivos = 0;
        $blocosManutencao = 0;
        $blocosDesativados = 0;
        $blocosUso = 0;

        foreach ($blocos as $bloco) {
            $totalSalas += $bloco->getSalas()->count();

            if ($bloco->isEstadoAtivo()) {
                $blocosAtivos++;
            } elseif ($bloco->isEstadoManutencao()) {
                $blocosManutencao++;
            } elseif ($bloco->isEstadoDesativado()) {
                $blocosDesativados++;
            } elseif ($bloco->isEstadoUso()) {
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
            'blocosUso' => $blocosUso,
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

        $estadoFiltro = Yii::$app->request->get('estado');
        if ($estadoFiltro && in_array($estadoFiltro, array_keys(\common\models\Sala::optsEstado()))) {
            $query->andWhere(['estado' => $estadoFiltro]);
        }

        $salas = $query->all();

        $contagemPorEstado = [];
        $estados = array_keys(\common\models\Sala::optsEstado());

        foreach ($estados as $estado) {
            $queryCount = \common\models\Sala::find()
                ->where($blocoModel ? ['bloco_id' => $bloco] : [])
                ->andWhere(['estado' => $estado]);

            $contagemPorEstado[$estado] = $queryCount->count();
        }

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
    public function actionTiposequipamento()
    {
        if (!Yii::$app->user->can('updateEquipmentStatus')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para visualizar equipamentos.');
        }

        $search = Yii::$app->request->get('search');

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
    public function actionEquipamentos($tipo = null)
    {
        if (!Yii::$app->user->can('updateEquipmentStatus')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para visualizar equipamentos.');
        }

        $tipoEquipamento = $tipo ? \common\models\TipoEquipamento::findOne($tipo) : null;

        if (!$tipoEquipamento) {
            throw new \yii\web\NotFoundHttpException('Tipo de equipamento não encontrado.');
        }

        $query = \common\models\Equipamento::find()
            ->with(['tipoEquipamento', 'salas'])
            ->where(['tipoEquipamento_id' => $tipo]);

        $estadoFiltro = Yii::$app->request->get('estado');
        if ($estadoFiltro && in_array($estadoFiltro, ['Operacional', 'Em Manutenção', 'Em Uso'])) {
            $query->andWhere(['estado' => $estadoFiltro]);
        }

        $search = Yii::$app->request->get('search');
        if ($search) {
            $query->andWhere(['or',
                ['like', 'equipamento', $search],
                ['like', 'numeroSerie', $search],
            ]);
        }

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

        $estadosPossiveis = ['Operacional', 'Em Manutenção', 'Em Uso'];
        foreach ($estadosPossiveis as $estado) {
            if (!isset($contagemPorEstado[$estado])) {
                $contagemPorEstado[$estado] = 0;
            }
        }

        $mapeamentoTiposParaCategorias = [
            1 => 'moveis',
            2 => 'monitorizacao',
            3 => 'cirurgicos',
            4 => 'consumo'
        ];

        $categoria = isset($mapeamentoTiposParaCategorias[$tipo])
            ? $mapeamentoTiposParaCategorias[$tipo]
            : null;

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
    public function actionDetalheEquipamento($id)
    {
        if (!Yii::$app->user->can('updateEquipmentStatus')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para visualizar detalhes dos equipamentos.');
        }

        $equipamentoModel = \common\models\Equipamento::findOne($id);

        if (!$equipamentoModel) {
            throw new \yii\web\NotFoundHttpException('Equipamento não encontrado.');
        }

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

        $equipamentosDisponiveis = \common\models\Equipamento::find()
            ->where(['estado' => 'Operacional'])
            ->with(['tipoEquipamento'])
            ->all();

        $equipamentosSala = $sala->getEquipamentos()
            ->with(['tipoEquipamento'])
            ->all();

        if (Yii::$app->request->isPost) {
            $selectedEquipamentos = Yii::$app->request->post('equipamentos', []);
            $dataReserva = Yii::$app->request->post('data_reserva');
            $horaInicio = Yii::$app->request->post('hora_inicio');
            $horaFim = Yii::$app->request->post('hora_fim');
            $observacoes = Yii::$app->request->post('observacoes');

            $errors = [];

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

            if (empty($errors)) {
                if (!$sala->isDisponivelParaReserva()) {
                    Yii::$app->session->setFlash('error', 'Esta sala não está disponível para requisição. Estado atual: ' . $sala->getEstadoLabel());
                    return $this->refresh();
                } else {
                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        $requisicao = new \common\models\Requisicao();
                        $requisicao->user_id = Yii::$app->user->id;
                        $requisicao->sala_id = $sala->id;
                        $requisicao->dataInicio = $dataReserva . ' ' . $horaInicio . ':00';
                        $requisicao->dataFim = $dataReserva . ' ' . $horaFim . ':00';
                        $requisicao->status = \common\models\Requisicao::STATUS_ATIVA;

                        if (!$requisicao->save()) {
                            throw new \Exception('Erro ao criar requisição: ' . implode(', ', $requisicao->getFirstErrors()));
                        }

                        if (!empty($selectedEquipamentos)) {
                            foreach ($selectedEquipamentos as $equipamentoId) {
                                $equipamento = \common\models\Equipamento::findOne($equipamentoId);
                                if ($equipamento) {
                                    if ($equipamento->estado !== 'Operacional') {
                                        throw new \Exception("O equipamento {$equipamento->equipamento} não está disponível. Estado atual: {$equipamento->estado}");
                                    }

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

                                    $requisicaoEquipamento = new \common\models\RequisicaoEquipamento();
                                    $requisicaoEquipamento->idRequisicao = $requisicao->id;
                                    $requisicaoEquipamento->idEquipamento = $equipamentoId;

                                    if (!$requisicaoEquipamento->save()) {
                                        throw new \Exception('Erro ao associar equipamento à requisição.');
                                    }

                                    $equipamento->estado = \common\models\Equipamento::ESTADO_EM_USO;
                                    if (!$equipamento->save(false)) {
                                        throw new \Exception('Erro ao atualizar estado do equipamento.');
                                    }

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

        return $this->render('reserva', [
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
                $equipamentosReserva = $reserva->getIdEquipamentos()->all();

                foreach ($equipamentosReserva as $equipamento) {
                    $equipamento->estado = Equipamento::ESTADO_OPERACIONAL;
                    $equipamento->save(false);
                }

                RequisicaoEquipamento::deleteAll(['idRequisicao' => $reserva->id]);

                $reserva->status = 'Cancelada';
                $reserva->save(false);
            }

            $outrasReservasAtivas = Requisicao::find()
                ->where([
                    'sala_id' => $sala->id,
                    'status' => 'Ativa'
                ])
                ->exists();

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

        if ($sala->estado === \common\models\Sala::ESTADO_MANUTENCAO) {
            Yii::$app->session->setFlash('info', 'Esta sala já está em manutenção.');
            return $this->redirect(['site/detalhe-sala', 'id' => $id]);
        }

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

        if ($equipamento->estado === \common\models\Equipamento::ESTADO_MANUTENCAO) {
            Yii::$app->session->setFlash('info', 'Este equipamento já está em manutenção.');
            return $this->redirect(['site/detalhe-equipamento', 'id' => $id]);
        }

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
            // Se já estiver logado, redireciona para tiposequipamento
            return $this->redirect(['site/tiposequipamento']);
        }

        $this->layout = 'login';

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            // Verificar se o user tem acesso ao frontend
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

        return $this->redirect(['site/login']);
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
        $this->layout = 'login';

        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            Yii::$app->session->setFlash('success', 'Registo efetuado com sucesso. Já pode fazer login.');
            // Após registro, redireciona para login (e depois para tiposequipamento após login)
            return $this->redirect(['site/login']);
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
            // Após reset de senha, redireciona para login (e depois para tiposequipamento)
            return $this->redirect(['site/login']);
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
            return $this->redirect(['site/login']);
        }

        Yii::$app->session->setFlash('error', 'Não foi possível verificar a sua conta com o token fornecido.');
        return $this->redirect(['site/login']);
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