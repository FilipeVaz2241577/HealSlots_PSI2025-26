<?php
namespace backend\modules\api\controllers;

use yii\rest\ActiveController;
use common\models\User;
use Yii;

class UserController extends ActiveController
{
    public $modelClass = 'common\models\User';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Configurar CORS
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => null,
                'Access-Control-Max-Age' => 86400,
                'Access-Control-Expose-Headers' => [],
            ],
        ];

        return $behaviors;
    }

    /**
     * Sobrescrever ações
     */
    public function actions()
    {
        $actions = parent::actions();

        // Personalizar actionUpdate
        $actions['update'] = [
            'class' => 'yii\rest\UpdateAction',
            'modelClass' => $this->modelClass,
            'checkAccess' => null, // Remover verificação
        ];

        return $actions;
    }

    /**
     * Ação personalizada para atualizar email
     * PUT /api/user/{id}
     */
    public function actionUpdate($id = null)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if ($id === null) {
            $id = Yii::$app->request->get('id');
        }

        $user = User::findOne($id);
        if (!$user) {
            throw new \yii\web\NotFoundHttpException('Utilizador não encontrado.');
        }

        // Obter dados do PUT
        $rawBody = Yii::$app->request->getRawBody();
        $data = json_decode($rawBody, true);

        Yii::error("PUT Data recebido: " . print_r($data, true));

        if (!$data) {
            return [
                'success' => false,
                'message' => 'Nenhum dado JSON recebido.'
            ];
        }

        // Campos que podem ser atualizados (baseado na sua tabela)
        $allowedFields = ['email', 'username', 'status'];

        $updated = false;
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $user->$field = $data[$field];
                $updated = true;
                Yii::error("Campo '$field' atualizado para: " . $data[$field]);
            }
        }

        if (!$updated) {
            return [
                'success' => false,
                'message' => 'Nenhum campo permitido para atualizar. Campos permitidos: ' . implode(', ', $allowedFields),
                'data_recebido' => $data
            ];
        }

        // Atualizar timestamp
        $user->updated_at = time();

        // Salvar
        if ($user->save()) {
            Yii::error("Utilizador salvo com sucesso!");
            return [
                'success' => true,
                'message' => 'Utilizador atualizado com sucesso!',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'status' => $user->status,
                    'updated_at' => date('Y-m-d H:i:s', $user->updated_at)
                ]
            ];
        } else {
            Yii::error("Erro ao salvar: " . print_r($user->errors, true));
            return [
                'success' => false,
                'message' => 'Erro ao atualizar utilizador',
                'errors' => $user->errors
            ];
        }
    }

    /**
     * Test endpoint para ver estrutura do user
     * GET /api/user/teste/{id}
     */
    public function actionTeste($id)
    {
        $user = User::findOne($id);
        return [
            'campos_disponiveis' => array_keys($user->attributes),
            'valores_atuais' => $user->attributes,
            'pode_atualizar' => ['email', 'username', 'status']
        ];
    }

    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new \yii\web\NotFoundHttpException('Utilizador não encontrado.');
    }
}