<?php

namespace backend\controllers;

use Yii;
use common\models\Sala;
use backend\models\SalaSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\Bloco;

class SalaController extends Controller
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
        $searchModel = new SalaSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Estatísticas para as SmallBox - CORRIGIDO
        $totalSalas = Sala::find()->count();

        // Contar salas por estado individualmente
        $salasLivresCount = Sala::find()->where(['estado' => 'Livre'])->count();
        $salasEmUsoCount = Sala::find()->where(['estado' => 'EmUso'])->count();
        $salasManutencaoCount = Sala::find()->where(['estado' => 'Manutencao'])->count();
        $salasDesativadasCount = Sala::find()->where(['estado' => 'Desativada'])->count();

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

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCreate()
    {
        $model = new Sala();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Sala criada com sucesso!');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        // CORREÇÃO: Usar indexBy para criar array [id => nome]
        $blocosList = Bloco::find()
            ->select(['nome', 'id'])
            ->indexBy('id')
            ->column();

        return $this->render('create', [
            'model' => $model,
            'blocos' => $blocosList, // Passar array formatada
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Sala atualizada com sucesso!');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'blocos' => Bloco::find()->select(['nome', 'id'])->indexBy('id')->column(),
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();

        Yii::$app->session->setFlash('success', 'Sala eliminada com sucesso!');
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = Sala::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('A sala solicitada não existe.');
    }
}