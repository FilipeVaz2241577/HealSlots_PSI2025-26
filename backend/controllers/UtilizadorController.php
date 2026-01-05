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

/**
 * Controlador de Utilizadores
 * Implementa ações CRUD para o modelo User (gestão de utilizadores)
 */
class UtilizadorController extends Controller
{
    /**
     * Configura comportamentos do controlador
     * Controla acesso apenas a administradores e valida métodos HTTP
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
                            'roles' => ['Admin'], // Apenas administradores podem gerir utilizadores
                        ],
                    ],
                ],
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],   // Desativação requer POST
                        'restore' => ['POST'],  // Restauração requer POST
                    ],
                ],
            ]
        );
    }

    /**
     * Obtém lista de roles (funções/permissões) da tabela auth_item
     * Filtra apenas roles (type = 1) e não permissões individuais
     * @return array Mapeamento de [nome_role => nome_role] para dropdowns
     */
    private function getRolesList()
    {
        // Consulta direta à tabela auth_item para obter roles (type = 1)
        $roles = (new \yii\db\Query())
            ->select(['name'])
            ->from('auth_item')
            ->where(['type' => 1]) // Apenas roles (1 = role, 2 = permissão)
            ->orderBy('name')
            ->all();

        return ArrayHelper::map($roles, 'name', 'name');
    }

    /**
     * Lista todos os utilizadores
     * Inclui pesquisa, filtros e estatísticas sobre utilizadores e roles
     * @return string Renderização da vista de lista
     */
    public function actionIndex()
    {
        // Modelo de pesquisa para filtragem de utilizadores
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Estatísticas sobre utilizadores
        $activeUsersCount = User::find()->where(['status' => User::STATUS_ACTIVE])->count();    // Utilizadores ativos
        $inactiveUsersCount = User::find()->where(['status' => User::STATUS_INACTIVE])->count(); // Utilizadores inativos
        $totalUsersCount = User::find()->count();                                                // Total de utilizadores

        // Estatísticas sobre roles
        $rolesList = $this->getRolesList();
        $differentRolesCount = count($rolesList); // Número de roles diferentes disponíveis

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'activeUsersCount' => $activeUsersCount,
            'inactiveUsersCount' => $inactiveUsersCount,
            'totalUsersCount' => $totalUsersCount,
            'differentRolesCount' => $differentRolesCount,
            'rolesList' => $rolesList,
        ]);
    }

    /**
     * Exibe detalhes de um utilizador específico
     * @param int $id ID do utilizador a visualizar
     * @return string Renderização da vista de detalhes
     * @throws NotFoundHttpException se o utilizador não for encontrado
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Cria um novo utilizador
     * Se for bem-sucedido, redireciona para a lista de utilizadores
     * @return string|Response Renderização do formulário ou redirecionamento
     */
    public function actionCreate()
    {
        $model = new User();
        $model->scenario = User::SCENARIO_CREATE; // Usa cenário específico para criação
        $model->status = User::STATUS_ACTIVE;     // Estado inicial: ativo

        $rolesList = $this->getRolesList();

        // Validação AJAX para feedback em tempo real
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        // Processa submissão do formulário
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // Criptografa a password antes de guardar
            $model->setPassword($model->password);
            $model->generateAuthKey(); // Gera chave de autenticação

            if ($model->save()) {
                // Atribui role ao utilizador usando authManager
                if (!empty($model->role)) {
                    $auth = Yii::$app->authManager;
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

    /**
     * Atualiza um utilizador existente
     * Se for bem-sucedido, redireciona para a lista de utilizadores
     * @param int $id ID do utilizador a atualizar
     * @return string|Response Renderização do formulário ou redirecionamento
     * @throws NotFoundHttpException se o utilizador não for encontrado
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = User::SCENARIO_UPDATE; // Usa cenário específico para atualização

        $rolesList = $this->getRolesList();

        // Obtém a role atual do utilizador
        $auth = Yii::$app->authManager;
        $userRoles = $auth->getRolesByUser($id);
        if (!empty($userRoles)) {
            $model->role = array_keys($userRoles)[0]; // Assume um utilizador tem apenas uma role principal
        }

        // Processa submissão do formulário
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // Atualiza a role do utilizador
            $auth->revokeAll($id); // Remove todas as roles atuais
            if (!empty($model->role)) {
                $role = $auth->getRole($model->role);
                if ($role) {
                    $auth->assign($role, $id); // Atribui nova role
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

    /**
     * Desativação suave (soft delete) de um utilizador
     * Marca como inativo em vez de eliminar permanentemente
     * @param int $id ID do utilizador a desativar
     * @return Response Redirecionamento para a lista de utilizadores
     * @throws NotFoundHttpException se o utilizador não for encontrado
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        // Verifica se é o próprio utilizador (não pode desativar a sua própria conta)
        if ($model->id === Yii::$app->user->id) {
            Yii::$app->session->setFlash('error', 'Não pode desativar a sua própria conta!');
            return $this->redirect(['index']);
        }

        try {
            // Desativação suave (muda status para inativo)
            if ($model->softDelete()) {
                Yii::$app->session->setFlash('success', 'Utilizador desativado com sucesso!');
            } else {
                Yii::$app->session->setFlash('error', 'Erro ao desativar o utilizador.');
            }
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'Erro ao desativar utilizador: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Restaura um utilizador previamente desativado
     * Altera o status de inativo para ativo
     * @param int $id ID do utilizador a restaurar
     * @return Response Redirecionamento para a lista de utilizadores
     * @throws NotFoundHttpException se o utilizador não for encontrado
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
     * Encontra um utilizador pelo seu ID
     * Lança exceção se o utilizador não for encontrado
     * @param int $id ID do utilizador
     * @return User modelo do utilizador encontrado
     * @throws NotFoundHttpException se o utilizador não existir
     */
    protected function findModel($id)
    {
        // Procura o utilizador pelo ID
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        // Lança exceção se o utilizador não for encontrado
        throw new NotFoundHttpException('O utilizador solicitado não existe.');
    }
}