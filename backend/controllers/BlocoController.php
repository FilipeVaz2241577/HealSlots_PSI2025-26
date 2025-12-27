<?php

namespace backend\controllers;

use Yii;
use common\models\Bloco;
use backend\models\BlocoSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\Sala;

class BlocoController extends Controller
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
        $searchModel = new BlocoSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Estatísticas para as SmallBox
        $totalBlocos = Bloco::find()->count();
        $activeBlocosCount = Bloco::find()->where(['estado' => 'ativo'])->count();
        $inactiveBlocosCount = Bloco::find()->where(['estado' => 'inativo'])->count();
        $totalSalasCount = Sala::find()->count();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'totalBlocosCount' => $totalBlocos,
            'activeBlocosCount' => $activeBlocosCount,
            'inactiveBlocosCount' => $inactiveBlocosCount,
            'totalSalasCount' => $totalSalasCount,
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
        $model = new Bloco();

<<<<<<< HEAD
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Bloco criado com sucesso!');
            return $this->redirect(['view', 'id' => $model->id]);
=======
        if ($model->load(Yii::$app->request->post())) {
            // Validar antes de salvar
            if ($model->validate() && $model->save()) {
                Yii::$app->session->setFlash('success', 'Bloco criado com sucesso!');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                // Se houver erro de unicidade, mostrar mensagem
                if ($model->hasErrors('nome')) {
                    Yii::$app->session->setFlash('error', $model->getFirstError('nome'));
                }
            }
>>>>>>> origin/filipe
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

<<<<<<< HEAD
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Bloco atualizado com sucesso!');
            return $this->redirect(['view', 'id' => $model->id]);
=======
        if ($model->load(Yii::$app->request->post())) {
            // Validar antes de salvar
            if ($model->validate() && $model->save()) {
                Yii::$app->session->setFlash('success', 'Bloco atualizado com sucesso!');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                // Se houver erro de unicidade, mostrar mensagem
                if ($model->hasErrors('nome')) {
                    Yii::$app->session->setFlash('error', $model->getFirstError('nome'));
                }
            }
>>>>>>> origin/filipe
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        // Verificar se tem salas associadas
        if ($model->salas && count($model->salas) > 0) {
            Yii::$app->session->setFlash('error', 'Não pode eliminar o bloco porque tem salas associadas!');
            return $this->redirect(['index']);
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'Bloco eliminado com sucesso!');
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = Bloco::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('O bloco solicitado não existe.');
    }
}