<?php

namespace backend\controllers;

use common\models\LoginForm;
use Yii;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

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
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
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
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        // Redirecionar conforme o role
        if (Yii::$app->user->can('manageUsers')) {
            // Admin - Dashboard completo
            return $this->render('index.php');
        } elseif (Yii::$app->user->can('manageMaintenance')) {
            // AssistenteManutencao - Dashboard de manutenção
            return $this->render('manutencao-index');
        }

        return $this->render('index');
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

        $this->layout = 'blank';

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post())) {
            // DEBUG: Log da tentativa de login
            Yii::info("Tentativa de login para usuário: {$model->username}", 'login');

            if ($model->login()) {
                // DEBUG: Login bem-sucedido no modelo
                Yii::info("LoginModel bem-sucedido para usuário: {$model->username}", 'login');

                // Verificar se o user tem acesso ao backend
                if (Yii::$app->user->can('backOfficeAccess')) {
                    // DEBUG: Usuário tem acesso ao backend
                    Yii::info("Usuário {$model->username} tem backOfficeAccess", 'login');

                    // LOGIN BEM-SUCEDIDO - Publicar no MQTT
                    Yii::info("Publicando evento de login bem-sucedido no MQTT", 'login');
                    $this->publishLoginEvent($model, true);

                    Yii::info("Redirecionando para home", 'login');
                    return $this->goBack();
                } else {
                    // DEBUG: Usuário NÃO tem acesso ao backend
                    Yii::warning("Usuário {$model->username} NÃO tem backOfficeAccess", 'login');

                    // Se não tiver acesso ao backend - PUBLICAR EVENTO DE FALHA
                    $this->publishLoginEvent($model, false, 'no_backend_access');

                    Yii::$app->user->logout();
                    Yii::$app->session->setFlash('error', 'Não tem acesso ao backend.');

                    Yii::warning("Usuário {$model->username} deslogado por falta de acesso ao backend", 'login');
                    return $this->refresh();
                }
            } else {
                // DEBUG: Login falhou no modelo
                Yii::warning("LoginModel falhou para usuário: {$model->username}", 'login');

                // LOGIN FALHADO - Publicar no MQTT
                Yii::info("Publicando evento de login falhado no MQTT", 'login');
                $this->publishLoginEvent($model, false, 'invalid_credentials');

                Yii::$app->session->setFlash('error', 'Username ou password incorretos.');
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
        // Publicar evento de logout ANTES de deslogar
        $this->publishLogoutEvent();

        Yii::$app->user->logout();

        return $this->goHome();
    }

    // ==============================================
    // MÉTODOS MQTT PARA LOGIN/LOGOUT
    // ==============================================

    /**
     * Publica evento de login no MQTT
     */
    private function publishLoginEvent($model, $success, $reason = null)
    {
        try {
            Yii::info("Iniciando publishLoginEvent - Success: " . ($success ? 'true' : 'false') . ", Reason: $reason", 'mqtt');

            $phpMQTTPath = Yii::getAlias('@backend') . '/mosquitto/phpMQTT.php';

            if (!file_exists($phpMQTTPath)) {
                Yii::error("Arquivo MQTT não encontrado: $phpMQTTPath", 'mqtt');
                return;
            }

            require_once $phpMQTTPath;

            $server = "127.0.0.1";
            $port = 1883;
            $client_id = "yii_login_" . uniqid();

            Yii::info("Conectando ao MQTT - Server: $server, Port: $port, Client: $client_id", 'mqtt');

            $mqtt = new \backend\mosquitto\phpMQTT($server, $port, $client_id);

            if ($mqtt->connect(true, null, null, null, 5)) {
                Yii::info("Conexão MQTT bem-sucedida", 'mqtt');

                $data = [
                    'username' => $model->username,
                    'success' => $success,
                    'reason' => $reason,
                    'ip_address' => Yii::$app->request->userIP,
                    'user_agent' => substr(Yii::$app->request->userAgent, 0, 200),
                    'timestamp' => date('Y-m-d H:i:s'),
                    'location' => 'backend'
                ];

                if ($success) {
                    $user = $model->getUser();
                    if ($user) {
                        $data['user_id'] = $user->id;
                        $data['email'] = $user->email;

                        // Obter roles
                        $auth = Yii::$app->authManager;
                        $userRoles = $auth->getRolesByUser($user->id);
                        $rolesArray = [];
                        foreach ($userRoles as $roleName => $role) {
                            $rolesArray[] = $roleName;
                        }
                        $data['roles'] = $rolesArray;

                        Yii::info("User encontrado: ID={$user->id}, Username={$user->username}, Roles: " . implode(', ', $rolesArray), 'mqtt');
                    } else {
                        Yii::error("getUser() retornou null para username: {$model->username}", 'mqtt');
                    }

                    $topic = "USER_LOGIN_BACKEND";
                    Yii::info("Usando tópico para login bem-sucedido: $topic", 'mqtt');
                } else {
                    $topic = "LOGIN_FAILED_BACKEND";
                    Yii::info("Usando tópico para login falhado: $topic, Razão: $reason", 'mqtt');
                }

                $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
                Yii::info("JSON a ser publicado no tópico $topic: " . $jsonData, 'mqtt');

                $result = $mqtt->publish($topic, $jsonData, 0);

                if ($result) {
                    Yii::info("Publicação no MQTT bem-sucedida no tópico: $topic", 'mqtt');
                } else {
                    Yii::error("Falha na publicação no MQTT no tópico: $topic", 'mqtt');
                }

                $mqtt->close();

                // Escrever no arquivo de log
                $logPath = Yii::getAlias('@backend') . '/mqtt_auth.log';
                $logEntry = date('Y-m-d H:i:s') . " | $topic | " . $jsonData . "\n";

                if (file_put_contents($logPath, $logEntry, FILE_APPEND)) {
                    Yii::info("Log escrito no arquivo: $logPath", 'mqtt');
                } else {
                    Yii::error("Falha ao escrever no arquivo de log: $logPath", 'mqtt');
                }

            } else {
                Yii::error("Falha na conexão MQTT com o servidor", 'mqtt');
            }

        } catch (\Exception $e) {
            Yii::error("Exceção no publishLoginEvent: " . $e->getMessage() . "\n" . $e->getTraceAsString(), 'mqtt');
        }
    }

    /**
     * Publica evento de logout no MQTT
     */
    private function publishLogoutEvent()
    {
        try {
            $user = Yii::$app->user->identity;
            if (!$user) {
                Yii::warning("Tentativa de publicar logout sem usuário autenticado", 'mqtt');
                return;
            }

            Yii::info("Iniciando publishLogoutEvent para usuário: {$user->username}", 'mqtt');

            $phpMQTTPath = Yii::getAlias('@backend') . '/mosquitto/phpMQTT.php';

            if (!file_exists($phpMQTTPath)) {
                Yii::error("Arquivo MQTT não encontrado: $phpMQTTPath", 'mqtt');
                return;
            }

            require_once $phpMQTTPath;

            $server = "127.0.0.1";
            $port = 1883;
            $client_id = "yii_logout_" . uniqid();

            Yii::info("Conectando ao MQTT para logout - Server: $server, Port: $port, Client: $client_id", 'mqtt');

            $mqtt = new \backend\mosquitto\phpMQTT($server, $port, $client_id);

            if ($mqtt->connect(true, null, null, null, 5)) {
                Yii::info("Conexão MQTT para logout bem-sucedida", 'mqtt');

                $data = [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'ip_address' => Yii::$app->request->userIP,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'location' => 'backend'
                ];

                // Obter roles
                $auth = Yii::$app->authManager;
                $userRoles = $auth->getRolesByUser($user->id);
                $rolesArray = [];
                foreach ($userRoles as $roleName => $role) {
                    $rolesArray[] = $roleName;
                }
                $data['roles'] = $rolesArray;

                Yii::info("Roles do usuário para logout: " . implode(', ', $rolesArray), 'mqtt');

                $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
                Yii::info("JSON a ser publicado para logout: " . $jsonData, 'mqtt');

                $result = $mqtt->publish("USER_LOGOUT_BACKEND", $jsonData, 0);

                if ($result) {
                    Yii::info("Publicação de logout no MQTT bem-sucedida", 'mqtt');
                } else {
                    Yii::error("Falha na publicação de logout no MQTT", 'mqtt');
                }

                $mqtt->close();

                // Escrever no arquivo de log
                $logPath = Yii::getAlias('@backend') . '/mqtt_auth.log';
                $logEntry = date('Y-m-d H:i:s') . " | USER_LOGOUT_BACKEND | " . $jsonData . "\n";

                if (file_put_contents($logPath, $logEntry, FILE_APPEND)) {
                    Yii::info("Log de logout escrito no arquivo: $logPath", 'mqtt');
                } else {
                    Yii::error("Falha ao escrever log de logout no arquivo: $logPath", 'mqtt');
                }

            } else {
                Yii::error("Falha na conexão MQTT para logout", 'mqtt');
            }

        } catch (\Exception $e) {
            Yii::error("Exceção no publishLogoutEvent: " . $e->getMessage() . "\n" . $e->getTraceAsString(), 'mqtt');
        }
    }
}