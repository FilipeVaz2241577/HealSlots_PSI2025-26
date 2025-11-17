<?php

namespace backend\controllers;

use Yii;
use common\models\Equipamento;
use backend\models\EquipamentoSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\TipoEquipamento;

class EquipamentoController extends Controller
{
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

    public function actionIndex()
    {
        $searchModel = new EquipamentoSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Estatísticas para as SmallBox
        $estatisticas = Equipamento::getCountByEstado();
        $totalEquipamentos = Equipamento::find()->count();
        $operacionaisCount = $estatisticas[Equipamento::ESTADO_OPERACIONAL] ?? 0;
        $manutencaoCount = $estatisticas[Equipamento::ESTADO_MANUTENCAO] ?? 0;
        $emUsoCount = $estatisticas[Equipamento::ESTADO_EM_USO] ?? 0;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'totalEquipamentosCount' => $totalEquipamentos,
            'operacionaisCount' => $operacionaisCount,
            'manutencaoCount' => $manutencaoCount,
            'emUsoCount' => $emUsoCount,
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
        $model = new Equipamento();

        // Obter tipos de equipamento
        $tiposEquipamento = TipoEquipamento::find()->select(['nome', 'id'])->indexBy('id')->column();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Equipamento criado com sucesso!');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'tiposEquipamento' => $tiposEquipamento,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // Obter tipos de equipamento
        $tiposEquipamento = TipoEquipamento::find()->select(['nome', 'id'])->indexBy('id')->column();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Equipamento atualizado com sucesso!');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'tiposEquipamento' => $tiposEquipamento,
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();

        Yii::$app->session->setFlash('success', 'Equipamento eliminado com sucesso!');
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = Equipamento::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('O equipamento solicitado não existe.');
    }
}