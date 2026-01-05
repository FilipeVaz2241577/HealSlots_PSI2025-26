<?php

namespace backend\controllers;

use common\models\LoginForm;
use Yii;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

/**
 * Controlador do Site (Backend)
 * Gere autenticação, dashboard e redirecionamentos baseados em roles
 */
class SiteController extends Controller
{
    /**
     * Configura comportamentos do controlador
     * Controla acesso por roles e valida métodos HTTP
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
                        'roles' => ['backOfficeAccess'],
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
     * Configura ações personalizadas
     * Define o manipulador de erros
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
     * Exibe a página inicial (dashboard)
     * Redireciona para diferentes dashboards conforme o role do utilizador
     * @return string Renderização da vista apropriada
     */
    public function actionIndex()
    {
        // Redireciona conforme o role do utilizador
        if (Yii::$app->user->can('manageUsers')) {
            // Administrador - Dashboard completo com todas as funcionalidades
            return $this->render('index.php');
        } elseif (Yii::$app->user->can('manageMaintenance')) {
            // Assistente de Manutenção - Dashboard específico para manutenções
            return $this->render('manutencao-index');
        }

        // Vista padrão para outros roles
        return $this->render('index');
    }

    /**
     * Ação de login
     * Gere autenticação de utilizadores e publica eventos MQTT
     * @return string|Response Renderização do formulário ou redirecionamento
     */
    public function actionLogin()
    {
        // Se o utilizador já está autenticado, redireciona para a página inicial
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        // Usa layout em branco para a página de login
        $this->layout = 'blank';

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post())) {
            // Log da tentativa de login
            Yii::info("Tentativa de login para utilizador: {$model->username}", 'login');

            if ($model->login()) {
                // Login bem-sucedido no modelo
                Yii::info("LoginModel bem-sucedido para utilizador: {$model->username}", 'login');

                // Verifica se o utilizador tem acesso ao backend
                if (Yii::$app->user->can('backOfficeAccess')) {
                    // Utilizador tem acesso ao backend
                    Yii::info("Utilizador {$model->username} tem backOfficeAccess", 'login');

                    // Publica evento de login bem-sucedido no MQTT
                    Yii::info("Publicando evento de login bem-sucedido no MQTT", 'login');
                    $this->publishLoginEvent($model, true);

                    Yii::info("Redirecionando para home", 'login');
                    return $this->goBack();
                } else {
                    // Utilizador NÃO tem acesso ao backend
                    Yii::warning("Utilizador {$model->username} NÃO tem backOfficeAccess", 'login');

                    // Publica evento de falha de login no MQTT
                    $this->publishLoginEvent($model, false, 'no_backend_access');

                    // Remove autenticação e mostra mensagem de erro
                    Yii::$app->user->logout();
                    Yii::$app->session->setFlash('error', 'Não tem acesso ao backend.');

                    Yii::warning("Utilizador {$model->username} deslogado por falta de acesso ao backend", 'login');
                    return $this->refresh();
                }
            } else {
                // Login falhou no modelo
                Yii::warning("LoginModel falhou para utilizador: {$model->username}", 'login');

                // Publica evento de login falhado no MQTT
                Yii::info("Publicando evento de login falhado no MQTT", 'login');
                $this->publishLoginEvent($model, false, 'invalid_credentials');

                Yii::$app->session->setFlash('error', 'Username ou password incorretos.');
            }
        }

        // Limpa o campo de password para segurança
        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Ação de logout
     * Remove autenticação e publica evento MQTT
     * @return Response Redirecionamento para a página inicial
     */
    public function actionLogout()
    {
        // Publica evento de logout ANTES de desautenticar
        $this->publishLogoutEvent();

        // Remove autenticação do utilizador
        Yii::$app->user->logout();

        return $this->goHome();
    }

    // ==============================================
    // MÉTODOS MQTT PARA LOGIN/LOGOUT
    // ==============================================

    /**
     * Publica evento de login no MQTT
     * Regista sucesso/falha de autenticação para monitorização
     * @param LoginForm $model Modelo do formulário de login
     * @param bool $success Se o login foi bem-sucedido
     * @param string|null $reason Razão da falha (se aplicável)
     */
    private function publishLoginEvent($model, $success, $reason = null)
    {
        try {
            Yii::info("Iniciando publishLoginEvent - Success: " . ($success ? 'true' : 'false') . ", Reason: $reason", 'mqtt');

            $phpMQTTPath = Yii::getAlias('@backend') . '/mosquitto/phpMQTT.php';

            if (!file_exists($phpMQTTPath)) {
                Yii::error("Ficheiro MQTT não encontrado: $phpMQTTPath", 'mqtt');
                return;
            }

            require_once $phpMQTTPath;

            $server = "127.0.0.1";
            $port = 1883;
            $client_id = "yii_login_" . uniqid();

            Yii::info("A ligar ao MQTT - Server: $server, Port: $port, Client: $client_id", 'mqtt');

            $mqtt = new \backend\mosquitto\phpMQTT($server, $port, $client_id);

            if ($mqtt->connect(true, null, null, null, 5)) {
                Yii::info("Ligação MQTT bem-sucedida", 'mqtt');

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

                        // Obtém roles do utilizador
                        $auth = Yii::$app->authManager;
                        $userRoles = $auth->getRolesByUser($user->id);
                        $rolesArray = [];
                        foreach ($userRoles as $roleName => $role) {
                            $rolesArray[] = $roleName;
                        }
                        $data['roles'] = $rolesArray;

                        Yii::info("Utilizador encontrado: ID={$user->id}, Username={$user->username}, Roles: " . implode(', ', $rolesArray), 'mqtt');
                    } else {
                        Yii::error("getUser() retornou null para username: {$model->username}", 'mqtt');
                    }

                    $topic = "USER_LOGIN_BACKEND";
                    Yii::info("A usar tópico para login bem-sucedido: $topic", 'mqtt');
                } else {
                    $topic = "LOGIN_FAILED_BACKEND";
                    Yii::info("A usar tópico para login falhado: $topic, Razão: $reason", 'mqtt');
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

                // Escreve no ficheiro de log
                $logPath = Yii::getAlias('@backend') . '/mqtt_auth.log';
                $logEntry = date('Y-m-d H:i:s') . " | $topic | " . $jsonData . "\n";

                if (file_put_contents($logPath, $logEntry, FILE_APPEND)) {
                    Yii::info("Log escrito no ficheiro: $logPath", 'mqtt');
                } else {
                    Yii::error("Falha ao escrever no ficheiro de log: $logPath", 'mqtt');
                }

            } else {
                Yii::error("Falha na ligação MQTT com o servidor", 'mqtt');
            }

        } catch (\Exception $e) {
            Yii::error("Exceção no publishLoginEvent: " . $e->getMessage() . "\n" . $e->getTraceAsString(), 'mqtt');
        }
    }

    /**
     * Publica evento de logout no MQTT
     * Regista ação de logout para auditoria e monitorização
     */
    private function publishLogoutEvent()
    {
        try {
            $user = Yii::$app->user->identity;
            if (!$user) {
                Yii::warning("Tentativa de publicar logout sem utilizador autenticado", 'mqtt');
                return;
            }

            Yii::info("Iniciando publishLogoutEvent para utilizador: {$user->username}", 'mqtt');

            $phpMQTTPath = Yii::getAlias('@backend') . '/mosquitto/phpMQTT.php';

            if (!file_exists($phpMQTTPath)) {
                Yii::error("Ficheiro MQTT não encontrado: $phpMQTTPath", 'mqtt');
                return;
            }

            require_once $phpMQTTPath;

            $server = "127.0.0.1";
            $port = 1883;
            $client_id = "yii_logout_" . uniqid();

            Yii::info("A ligar ao MQTT para logout - Server: $server, Port: $port, Client: $client_id", 'mqtt');

            $mqtt = new \backend\mosquitto\phpMQTT($server, $port, $client_id);

            if ($mqtt->connect(true, null, null, null, 5)) {
                Yii::info("Ligação MQTT para logout bem-sucedida", 'mqtt');

                $data = [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'ip_address' => Yii::$app->request->userIP,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'location' => 'backend'
                ];

                // Obtém roles do utilizador
                $auth = Yii::$app->authManager;
                $userRoles = $auth->getRolesByUser($user->id);
                $rolesArray = [];
                foreach ($userRoles as $roleName => $role) {
                    $rolesArray[] = $roleName;
                }
                $data['roles'] = $rolesArray;

                Yii::info("Roles do utilizador para logout: " . implode(', ', $rolesArray), 'mqtt');

                $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
                Yii::info("JSON a ser publicado para logout: " . $jsonData, 'mqtt');

                $result = $mqtt->publish("USER_LOGOUT_BACKEND", $jsonData, 0);

                if ($result) {
                    Yii::info("Publicação de logout no MQTT bem-sucedida", 'mqtt');
                } else {
                    Yii::error("Falha na publicação de logout no MQTT", 'mqtt');
                }

                $mqtt->close();

                // Escreve no ficheiro de log
                $logPath = Yii::getAlias('@backend') . '/mqtt_auth.log';
                $logEntry = date('Y-m-d H:i:s') . " | USER_LOGOUT_BACKEND | " . $jsonData . "\n";

                if (file_put_contents($logPath, $logEntry, FILE_APPEND)) {
                    Yii::info("Log de logout escrito no ficheiro: $logPath", 'mqtt');
                } else {
                    Yii::error("Falha ao escrever log de logout no ficheiro: $logPath", 'mqtt');
                }

            } else {
                Yii::error("Falha na ligação MQTT para logout", 'mqtt');
            }

        } catch (\Exception $e) {
            Yii::error("Exceção no publishLogoutEvent: " . $e->getMessage() . "\n" . $e->getTraceAsString(), 'mqtt');
        }
    }
}