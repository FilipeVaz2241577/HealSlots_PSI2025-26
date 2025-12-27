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

<<<<<<< HEAD
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

=======
/**
 * UtilizadorController implements the CRUD actions for User model.
 */
class UtilizadorController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
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
                        'restore' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Get roles from auth_item table where type = 1 (roles)
     */
    private function getRolesList()
    {
        // Buscar diretamente da tabela auth_item onde type = 1 (roles)
        $roles = (new \yii\db\Query())
            ->select(['name'])
            ->from('auth_item')
            ->where(['type' => 1]) // Apenas roles
            ->orderBy('name')
            ->all();

        return ArrayHelper::map($roles, 'name', 'name');
    }

    /**
     * Lists all User models.
     *
     * @return string
     */
>>>>>>> origin/filipe
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Estatísticas
<<<<<<< HEAD
        $activeUsersCount = User::find()->where(['status' => 10])->count();
        $inactiveUsersCount = User::find()->where(['status' => 9])->count();

        $auth = Yii::$app->authManager;
        $roles = $auth->getRoles();
        $differentRolesCount = count($roles);
        $rolesList = ArrayHelper::map($roles, 'name', 'name');
=======
        $activeUsersCount = User::find()->where(['status' => User::STATUS_ACTIVE])->count();
        $inactiveUsersCount = User::find()->where(['status' => User::STATUS_INACTIVE])->count();
        $totalUsersCount = User::find()->count();

        $rolesList = $this->getRolesList();
        $differentRolesCount = count($rolesList);
>>>>>>> origin/filipe

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'activeUsersCount' => $activeUsersCount,
            'inactiveUsersCount' => $inactiveUsersCount,
<<<<<<< HEAD
=======
            'totalUsersCount' => $totalUsersCount,
>>>>>>> origin/filipe
            'differentRolesCount' => $differentRolesCount,
            'rolesList' => $rolesList,
        ]);
    }

<<<<<<< HEAD
=======
    /**
     * Displays a single User model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
>>>>>>> origin/filipe
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

<<<<<<< HEAD
    public function actionCreate()
    {
        $model = new User();
        $model->scenario = 'create';
        $model->status = User::STATUS_ACTIVE; // ← DEFINIR STATUS ATIVO AUTOMATICAMENTE

        $auth = Yii::$app->authManager;
        $roles = $auth->getRoles();
        $rolesList = ArrayHelper::map($roles, 'name', 'name');
=======
    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new User();
        $model->scenario = User::SCENARIO_CREATE;
        $model->status = User::STATUS_ACTIVE;

        $rolesList = $this->getRolesList();
>>>>>>> origin/filipe

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->setPassword($model->password);
            $model->generateAuthKey();

            if ($model->save()) {
<<<<<<< HEAD
                // Atribuir role
                if (!empty($model->role)) {
=======
                // Atribuir role usando authManager
                if (!empty($model->role)) {
                    $auth = Yii::$app->authManager;
>>>>>>> origin/filipe
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

<<<<<<< HEAD
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'update';

        $auth = Yii::$app->authManager;
        $roles = $auth->getRoles();
        $rolesList = ArrayHelper::map($roles, 'name', 'name');

        // Obter role atual do utilizador
=======
    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = User::SCENARIO_UPDATE;

        $rolesList = $this->getRolesList();

        // Obter role atual do utilizador
        $auth = Yii::$app->authManager;
>>>>>>> origin/filipe
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

<<<<<<< HEAD
=======
    /**
     * Soft delete - marca como inativo
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
>>>>>>> origin/filipe
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

<<<<<<< HEAD
        // Verificar se é o próprio utilizador a tentar eliminar-se
        if ($model->id === Yii::$app->user->id) {
            Yii::$app->session->setFlash('error', 'Não pode eliminar a sua própria conta!');
=======
        // Verificar se é o próprio utilizador
        if ($model->id === Yii::$app->user->id) {
            Yii::$app->session->setFlash('error', 'Não pode desativar a sua própria conta!');
>>>>>>> origin/filipe
            return $this->redirect(['index']);
        }

        try {
<<<<<<< HEAD
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
=======
            // Soft delete - marcar como inativo em vez de eliminar
            if ($model->softDelete()) {
                Yii::$app->session->setFlash('success', 'Utilizador desativado com sucesso!');
            } else {
                Yii::$app->session->setFlash('error', 'Erro ao desativar o utilizador.');
            }
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'Erro ao desativar utilizador: ' . $e->getMessage());
>>>>>>> origin/filipe
        }

        return $this->redirect(['index']);
    }

<<<<<<< HEAD
=======
    /**
     * Restaurar utilizador inativo
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionRestore($id)
    {
        $model = $this->findModel($id);

        try {
            if ($model->restore()) {
                Yii::$app->session->setFlash('success', 'Utilizador restaurado com sucesso!');
            } else {
                Yii::$app->session->setFlash('error', 'Erro ao restaurar o utilizador.');
            }
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'Erro ao restaurar utilizador: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
>>>>>>> origin/filipe
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('O utilizador solicitado não existe.');
    }
}