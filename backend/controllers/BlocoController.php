<?php

namespace backend\controllers;

use Yii;
use common\models\Bloco;
use backend\models\BlocoSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\Sala;

/**
 * Controlador para gestão de Blocos
 * Permite CRUD de blocos hospitalares e visualização de estatísticas
 */
class BlocoController extends Controller
{
    /**
     * Configura comportamentos do controlador
     * Define as ações que requerem metodo POST para segurança
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lista todos os blocos com pesquisa e estatísticas
     * Exibe estatísticas em formato SmallBox (cards informativos)
     */
    public function actionIndex()
    {
        // Modelo de pesquisa para filtragem de blocos
        $searchModel = new BlocoSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Cálculo de estatísticas para exibição em cards
        $totalBlocos = Bloco::find()->count();                     // Total de blocos
        $activeBlocosCount = Bloco::find()->where(['estado' => 'ativo'])->count();    // Blocos ativos
        $inactiveBlocosCount = Bloco::find()->where(['estado' => 'inativo'])->count(); // Blocos inativos
        $totalSalasCount = Sala::find()->count();                  // Total de salas em todos os blocos

        // Renderiza a vista index com dados e estatísticas
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'totalBlocosCount' => $totalBlocos,
            'activeBlocosCount' => $activeBlocosCount,
            'inactiveBlocosCount' => $inactiveBlocosCount,
            'totalSalasCount' => $totalSalasCount,
        ]);
    }

    /**
     * Exibe detalhes de um bloco específico
     * @param int $id ID do bloco a visualizar
     */
    public function actionView($id)
    {
        // Busca e renderiza o modelo do bloco
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Cria um novo bloco
     * Processa o formulário e valida os dados antes de guardar
     */
    public function actionCreate()
    {
        $model = new Bloco(); // Nova instância de bloco

        // Processa os dados do formulário se enviados via POST
        if ($model->load(Yii::$app->request->post())) {
            // Valida e guarda o modelo
            if ($model->validate() && $model->save()) {
                // Mensagem de sucesso e redirecionamento
                Yii::$app->session->setFlash('success', 'Bloco criado com sucesso!');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                // Se houver erro de unicidade (nome duplicado), mostra mensagem específica
                if ($model->hasErrors('nome')) {
                    Yii::$app->session->setFlash('error', $model->getFirstError('nome'));
                }
            }
        }

        // Renderiza o formulário de criação
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Atualiza um bloco existente
     * @param int $id ID do bloco a atualizar
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id); // Busca o bloco pelo ID

        // Processa os dados do formulário se enviados via POST
        if ($model->load(Yii::$app->request->post())) {
            // Valida e guarda o modelo
            if ($model->validate() && $model->save()) {
                // Mensagem de sucesso e redirecionamento
                Yii::$app->session->setFlash('success', 'Bloco atualizado com sucesso!');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                // Se houver erro de unicidade (nome duplicado), mostra mensagem específica
                if ($model->hasErrors('nome')) {
                    Yii::$app->session->setFlash('error', $model->getFirstError('nome'));
                }
            }
        }

        // Renderiza o formulário de atualização
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Elimina um bloco existente
     * Verifica primeiro se existem salas associadas para evitar inconsistências
     * @param int $id ID do bloco a eliminar
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        // Verificação de integridade referencial: não eliminar blocos com salas associadas
        if ($model->salas && count($model->salas) > 0) {
            Yii::$app->session->setFlash('error', 'Não pode eliminar o bloco porque tem salas associadas!');
            return $this->redirect(['index']);
        }

        // Elimina o bloco
        $model->delete();

        // Mensagem de sucesso e redirecionamento
        Yii::$app->session->setFlash('success', 'Bloco eliminado com sucesso!');
        return $this->redirect(['index']);
    }

    /**
     * Encontra um bloco pelo seu ID
     * Lança exceção se o bloco não for encontrado
     * @param int $id ID do bloco
     * @return Bloco modelo do bloco encontrado
     * @throws NotFoundHttpException se o bloco não existir
     */
    protected function findModel($id)
    {
        // Procura o bloco pelo ID
        if (($model = Bloco::findOne($id)) !== null) {
            return $model;
        }

        // Lança exceção se o bloco não for encontrado
        throw new NotFoundHttpException('O bloco solicitado não existe.');
    }
}