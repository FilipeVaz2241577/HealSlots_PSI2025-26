<?php

namespace backend\controllers;

use Yii;
use common\models\Sala;
use backend\models\SalaSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\Bloco;

/**
 * Controlador para gestão de Salas
 * Permite CRUD de salas hospitalares e visualização de estatísticas por estado
 */
class SalaController extends Controller
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
     * Lista todas as salas com pesquisa e estatísticas
     * Exibe estatísticas detalhadas por estado (Livre, EmUso, Manutencao, Desativada)
     */
    public function actionIndex()
    {
        // Modelo de pesquisa para filtragem de salas
        $searchModel = new SalaSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Estatísticas para as SmallBox - contagem por estado
        $totalSalas = Sala::find()->count(); // Total de salas

        // Contagem individual de salas por estado usando as constantes do modelo
        $salasLivresCount = Sala::find()->where(['estado' => 'Livre'])->count();        // Salas livres
        $salasEmUsoCount = Sala::find()->where(['estado' => 'EmUso'])->count();         // Salas em uso
        $salasManutencaoCount = Sala::find()->where(['estado' => 'Manutencao'])->count(); // Salas em manutenção
        $salasDesativadasCount = Sala::find()->where(['estado' => 'Desativada'])->count(); // Salas desativadas

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'totalSalasCount' => $totalSalas,
            'salasLivresCount' => $salasLivresCount,
            'salasEmUsoCount' => $salasEmUsoCount,
            'salasManutencaoCount' => $salasManutencaoCount,
            'salasDesativadasCount' => $salasDesativadasCount,
        ]);
    }

    /**
     * Exibe detalhes de uma sala específica
     * @param int $id ID da sala a visualizar
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Cria uma nova sala
     * Inclui dropdown de blocos disponíveis para associação
     */
    public function actionCreate()
    {
        $model = new Sala();

        // Processa os dados do formulário se enviados via POST
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Sala criada com sucesso!');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        // Obtém lista de blocos para dropdown no formato [id => nome]
        $blocosList = Bloco::find()
            ->select(['nome', 'id'])
            ->indexBy('id')  // Usa ID como chave do array
            ->column();      // Extrai coluna em formato array

        return $this->render('create', [
            'model' => $model,
            'blocos' => $blocosList, // Array formatado para dropdown
        ]);
    }

    /**
     * Atualiza uma sala existente
     * @param int $id ID da sala a atualizar
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // Processa os dados do formulário se enviados via POST
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Sala atualizada com sucesso!');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        // Obtém lista de blocos atualizada para dropdown
        $blocosList = Bloco::find()
            ->select(['nome', 'id'])
            ->indexBy('id')
            ->column();

        return $this->render('update', [
            'model' => $model,
            'blocos' => $blocosList,
        ]);
    }

    /**
     * Elimina uma sala existente
     * @param int $id ID da sala a eliminar
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();

        Yii::$app->session->setFlash('success', 'Sala eliminada com sucesso!');
        return $this->redirect(['index']);
    }

    /**
     * Encontra uma sala pelo seu ID
     * Lança exceção se a sala não for encontrada
     * @param int $id ID da sala
     * @return Sala modelo da sala encontrada
     * @throws NotFoundHttpException se a sala não existir
     */
    protected function findModel($id)
    {
        // Procura a sala pelo ID
        if (($model = Sala::findOne($id)) !== null) {
            return $model;
        }

        // Lança exceção se a sala não for encontrada
        throw new NotFoundHttpException('A sala solicitada não existe.');
    }
}