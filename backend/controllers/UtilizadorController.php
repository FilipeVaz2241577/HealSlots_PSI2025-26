<?php

namespace backend\controllers;

use Yii;
use common\models\User;
use backend\models\UserSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;

class UtilizadorController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['Admin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Estatísticas
        $activeUsersCount = User::find()->where(['status' => 10])->count();
        $inactiveUsersCount = User::find()->where(['status' => 9])->count();

        $auth = Yii::$app->authManager;
        $roles = $auth->getRoles();
        $differentRolesCount = count($roles);
        $rolesList = ArrayHelper::map($roles, 'name', 'name');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'activeUsersCount' => $activeUsersCount,
            'inactiveUsersCount' => $inactiveUsersCount,
            'differentRolesCount' => $differentRolesCount,
            'rolesList' => $rolesList,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCreate()
    {
        $model = new User();
        $model->scenario = 'create';
        $model->status = User::STATUS_ACTIVE; // ← DEFINIR STATUS ATIVO AUTOMATICAMENTE

        $auth = Yii::$app->authManager;
        $roles = $auth->getRoles();
        $rolesList = ArrayHelper::map($roles, 'name', 'name');

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->setPassword($model->password);
            $model->generateAuthKey();

            if ($model->save()) {
                // Atribuir role
                if (!empty($model->role)) {
                    $role = $auth->getRole($model->role);
                    if ($role) {
                        $auth->assign($role, $model->id);
                    }
                }

                Yii::$app->session->setFlash('success', 'Utilizador criado com sucesso!');
                return $this->redirect(['index']);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'rolesList' => $rolesList,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'update';

        $auth = Yii::$app->authManager;
        $roles = $auth->getRoles();
        $rolesList = ArrayHelper::map($roles, 'name', 'name');

        // Obter role atual do utilizador
        $userRoles = $auth->getRolesByUser($id);
        if (!empty($userRoles)) {
            $model->role = array_keys($userRoles)[0];
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // Atualizar role
            $auth->revokeAll($id);
            if (!empty($model->role)) {
                $role = $auth->getRole($model->role);
                if ($role) {
                    $auth->assign($role, $id);
                }
            }

            Yii::$app->session->setFlash('success', 'Utilizador atualizado com sucesso!');
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
            'rolesList' => $rolesList,
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        // Verificar se é o próprio utilizador a tentar eliminar-se
        if ($model->id === Yii::$app->user->id) {
            Yii::$app->session->setFlash('error', 'Não pode eliminar a sua própria conta!');
            return $this->redirect(['index']);
        }

        try {
            // Remover roles primeiro (importante para constraints do RBAC)
            $auth = Yii::$app->authManager;
            $auth->revokeAll($id);

            // Eliminar permanentemente da base de dados
            if ($model->delete()) {
                Yii::$app->session->setFlash('success', 'Utilizador eliminado permanentemente com sucesso!');
            } else {
                Yii::$app->session->setFlash('error', 'Erro ao eliminar o utilizador.');
            }
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'Erro ao eliminar utilizador: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('O utilizador solicitado não existe.');
    }
}