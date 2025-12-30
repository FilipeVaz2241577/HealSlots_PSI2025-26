<?php
namespace backend\modules\api\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use common\models\User;

class AuthController extends Controller
{
    public $enableCsrfValidation = false;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Remove autenticador para ações públicas
        unset($behaviors['authenticator']);

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    /**
     * Endpoint para testar a API
     * GET /api/auth/test
     */
    public function actionTest()
    {
        return [
            'success' => true,
            'message' => 'API de autenticação está funcionando',
            'endpoints' => [
                'POST /api/auth/login' => 'Autenticação de utilizador',
                'POST /api/auth/verify' => 'Verificar token',
                'POST /api/auth/logout' => 'Logout',
                'GET /api/auth/test' => 'Teste de conexão',
                'GET /api/auth/user-info' => 'Informações do utilizador'
            ],
            'timestamp' => time(),
            'version' => '1.0'
        ];
    }

    /**
     * Endpoint de login
     * POST /api/auth/login
     */
    public function actionLogin()
    {
        try {
            $request = Yii::$app->getRequest();

            // Suporta tanto form-data quanto JSON
            $username = $request->post('username');
            $password = $request->post('password');

            // Se não vier por POST, tenta ler JSON
            if (empty($username) || empty($password)) {
                $rawData = file_get_contents("php://input");
                $jsonData = json_decode($rawData, true);

                if ($jsonData) {
                    $username = $jsonData['username'] ?? null;
                    $password = $jsonData['password'] ?? null;
                }
            }

            // Validação
            if (empty($username) || empty($password)) {
                Yii::$app->response->statusCode = 400;
                return [
                    'success' => false,
                    'message' => 'Username e password são obrigatórios',
                    'error' => 'missing_credentials'
                ];
            }

            // Busca usuário
            $user = User::findByUsername($username);

            if (!$user) {
                Yii::$app->response->statusCode = 401;
                return [
                    'success' => false,
                    'message' => 'Utilizador não encontrado',
                    'error' => 'user_not_found'
                ];
            }

            // Verifica password
            if (!$user->validatePassword($password)) {
                Yii::$app->response->statusCode = 401;
                return [
                    'success' => false,
                    'message' => 'Password incorreta',
                    'error' => 'invalid_password'
                ];
            }

            // Verifica se usuário está ativo (se o campo existir)
            if (property_exists($user, 'status') && $user->status != User::STATUS_ACTIVE) {
                Yii::$app->response->statusCode = 403;
                return [
                    'success' => false,
                    'message' => 'Conta inativa. Contacte o administrador.',
                    'error' => 'account_inactive'
                ];
            }

            // Garante que tem auth_key (token)
            if (empty($user->auth_key)) {
                $user->generateAuthKey();
                $user->save(false);
            }

            // Atualiza último login (se o campo existir)
            if (property_exists($user, 'last_login_at')) {
                $user->last_login_at = date('Y-m-d H:i:s');
                $user->save(false);
            }

            // Prepara dados do usuário para resposta
            $userData = [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
            ];

            // Adiciona campos opcionais se existirem
            if (property_exists($user, 'nome') && !empty($user->nome)) {
                $userData['nome'] = $user->nome;
            }

            if (property_exists($user, 'role')) {
                $userData['role'] = $user->role;
            }

            // Login bem-sucedido
            return [
                'success' => true,
                'message' => 'Login bem-sucedido',
                'user' => $userData,
                'token' => $user->auth_key,
                'token_type' => 'Bearer',
                'expires_in' => 3600 * 24 * 30, // 30 dias em segundos
                'timestamp' => time()
            ];

        } catch (\Exception $e) {
            Yii::$app->response->statusCode = 500;
            Yii::error('Login error: ' . $e->getMessage(), 'auth');
            return [
                'success' => false,
                'message' => 'Erro interno do servidor',
                'error' => 'server_error'
            ];
        }
    }

    /**
     * Endpoint para verificar token
     * POST /api/auth/verify
     */
    public function actionVerify()
    {
        $request = Yii::$app->getRequest();
        $token = $request->post('token');

        // Tenta ler JSON se não vier por POST
        if (empty($token)) {
            $rawData = file_get_contents("php://input");
            $jsonData = json_decode($rawData, true);
            $token = $jsonData['token'] ?? null;
        }

        if (empty($token)) {
            Yii::$app->response->statusCode = 400;
            return [
                'success' => false,
                'message' => 'Token é obrigatório',
                'valid' => false
            ];
        }

        // Busca usuário pelo token (auth_key)
        $user = User::find()->where(['auth_key' => $token])->one();

        if (!$user) {
            Yii::$app->response->statusCode = 401;
            return [
                'success' => false,
                'message' => 'Token inválido ou expirado',
                'valid' => false
            ];
        }

        // Verifica se conta está ativa
        if (property_exists($user, 'status') && $user->status != User::STATUS_ACTIVE) {
            Yii::$app->response->statusCode = 403;
            return [
                'success' => false,
                'message' => 'Conta inativa',
                'valid' => false
            ];
        }

        // Prepara dados do usuário
        $userData = [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
        ];

        if (property_exists($user, 'nome') && !empty($user->nome)) {
            $userData['nome'] = $user->nome;
        }

        return [
            'success' => true,
            'message' => 'Token válido',
            'valid' => true,
            'user' => $userData
        ];
    }

    /**
     * Endpoint para logout
     * POST /api/auth/logout
     */
    public function actionLogout()
    {
        $request = Yii::$app->getRequest();
        $token = $request->post('token');

        // Tenta ler JSON
        if (empty($token)) {
            $rawData = file_get_contents("php://input");
            $jsonData = json_decode($rawData, true);
            $token = $jsonData['token'] ?? null;
        }

        if ($token) {
            // Invalida o token (remove auth_key)
            $user = User::find()->where(['auth_key' => $token])->one();
            if ($user) {
                $user->auth_key = null;
                $user->save(false);
            }
        }

        return [
            'success' => true,
            'message' => 'Logout realizado com sucesso'
        ];
    }

    /**
     * Informações do utilizador pelo token
     * GET /api/auth/user-info?token=xxx
     */
    public function actionUserInfo()
    {
        $token = Yii::$app->request->get('token');

        if (empty($token)) {
            Yii::$app->response->statusCode = 400;
            return [
                'success' => false,
                'message' => 'Token é obrigatório'
            ];
        }

        $user = User::find()->where(['auth_key' => $token])->one();

        if (!$user) {
            Yii::$app->response->statusCode = 401;
            return [
                'success' => false,
                'message' => 'Token inválido'
            ];
        }

        $userData = [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
        ];

        if (property_exists($user, 'nome') && !empty($user->nome)) {
            $userData['nome'] = $user->nome;
        }

        if (property_exists($user, 'role')) {
            $userData['role'] = $user->role;
        }

        if (property_exists($user, 'status')) {
            $userData['status'] = $user->status;
        }

        return [
            'success' => true,
            'user' => $userData
        ];
    }
}