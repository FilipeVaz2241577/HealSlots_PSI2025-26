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
                        'actions' => ['index', 'logout', 'contact', 'about', 'dashboard-tecnico', 'dashboard-manutencao', 'marcacoes', 'blocos', 'salas', 'tiposequipamento', 'equipamentos', 'recursos', 'manutencoes'],
                        'allow' => true,
                        'roles' => ['@'],
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
    public function actionSuporte()
    {
        $model = new ContactForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // Tentar enviar para email de suporte, senão para admin
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
            throw new \yii\web\ForbiddenHttpException('Acesso negado.');
        }

        return $this->render('dashboard-tecnico');
    }

    /**
     * Dashboard para Assistentes de Manutenção
     */
    public function actionDashboardManutencao()
    {
        if (!Yii::$app->user->can('backOfficeAccess')) {
            throw new \yii\web\ForbiddenHttpException('Acesso negado.');
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
     * Página de Tipos de Equipamento
     */
    public function actionTiposequipamento()
    {
        if (!Yii::$app->user->can('updateEquipmentStatus')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para visualizar equipamentos.');
        }

        return $this->render('tiposequipamento');
    }

    /**
     * Mostra os equipamentos de uma categoria específica
     */
    public function actionEquipamentos($categoria = null)
    {
        if (!Yii::$app->user->can('updateEquipmentStatus')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para visualizar equipamentos.');
        }

        return $this->render('equipamentos', [
            'categoria' => $categoria
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
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        // Definir layout sem navbar
        $this->layout = 'login';

        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
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