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
                        'actions' => ['login', 'error', 'signup', 'request-password-reset', 'reset-password', 'verify-email', 'resend-verification-email'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'contact', 'about'],
                        'allow' => true,
                        'roles' => ['@'], // Qualquer utilizador autenticado
                    ],
                    [
                        'actions' => ['index', 'marcacoes', 'salas', 'recursos'],
                        'allow' => true,
                        'roles' => ['frontOfficeAccess'], // Admin e TecnicoSaude
                    ],
                    [
                        'actions' => ['index', 'equipamentos', 'manutencoes'],
                        'allow' => true,
                        'roles' => ['backOfficeAccess'], // Admin e AssistenteManutencao
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
        if (Yii::$app->user->isGuest) {
            return $this->render('index'); // Página pública
        }

        // Redirecionar conforme o role do utilizador
        if (Yii::$app->user->can('frontOfficeAccess')) {
            return $this->redirect(['dashboard-tecnico']);
        } elseif (Yii::$app->user->can('backOfficeAccess')) {
            return $this->redirect(['dashboard-manutencao']);
        }

        return $this->render('index');
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
     * Gestão de Salas (TecnicoSaude e Admin)
     */
    public function actionSalas()
    {
        if (!Yii::$app->user->can('manageRooms')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para gerir salas.');
        }

        return $this->render('salas');
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

    /**
     * Gestão de Equipamentos (AssistenteManutencao e Admin)
     */
    public function actionEquipamentos()
    {
        if (!Yii::$app->user->can('updateEquipmentStatus')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para gerir equipamentos.');
        }

        return $this->render('equipamentos');
    }

    /**
     * Gestão de Manutenções (AssistenteManutencao e Admin)
     */
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
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            // Redirecionar conforme o role após login
            if (Yii::$app->user->can('frontOfficeAccess')) {
                return $this->redirect(['dashboard-tecnico']);
            } elseif (Yii::$app->user->can('backOfficeAccess')) {
                return $this->redirect(['dashboard-manutencao']);
            }

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
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            // Alterar a mensagem de sucesso
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