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
                        'actions' => ['login', 'error', 'signup', 'request-password-reset', 'reset-password', 'verify-email', 'resend-verification-email', 'suporte'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'contact', 'about', 'dashboard-tecnico', 'dashboard-manutencao', 'marcacoes', 'blocos', 'salas', 'tiposequipamento', 'equipamentos', 'recursos', 'manutencoes', 'detalhe-sala', 'detalhe-equipamento'],
                        'allow' => true,
                        'roles' => ['frontOfficeAccess'], // Apenas TecnicoSaude e Admin
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
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
     * Gestão de Blocos (TecnicoSaude e Admin)
     */
    public function actionBlocos()
    {
        if (!Yii::$app->user->can('manageRooms')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para gerir blocos.');
        }

        return $this->render('blocos');
    }

    /**
     * Mostra as salas do Bloco A
     */
    public function actionSalas()
    {
        if (!Yii::$app->user->can('manageRooms')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para visualizar salas.');
        }

        return $this->render('salas');
    }

    /**
     * Mostra os detalhes de uma sala específica
     */
    public function actionDetalheSala($sala)
    {
        if (!Yii::$app->user->can('manageRooms')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para visualizar detalhes das salas.');
        }

        return $this->render('detalheSala', [
            'sala' => $sala
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