<?php

namespace backend\controllers;

use Yii;
use common\models\Manutencao;
use backend\models\ManutencaoSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;
use common\models\User;
use common\models\Equipamento;
use common\models\Sala;

class ManutencaoController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['Admin', 'AssistenteManutencao'],
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
        $searchModel = new ManutencaoSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Estatísticas
        $totalManutencoes = Manutencao::find()->count();
        $manutencoesPendentes = Manutencao::find()->where(['status' => Manutencao::STATUS_PENDENTE])->count();
        $manutencoesCurso = Manutencao::find()->where(['status' => Manutencao::STATUS_EM_CURSO])->count();
        $manutencoesConcluidas = Manutencao::find()->where(['status' => Manutencao::STATUS_CONCLUIDA])->count();

        // Itens em manutenção sem registo formal
        $equipamentosSemManutencao = Equipamento::getEquipamentosManutencaoSemRegisto();
        $salasSemManutencao = Sala::getSalasManutencaoSemRegisto();
        $countEquipamentos = count($equipamentosSemManutencao);
        $countSalas = count($salasSemManutencao);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'totalManutencoes' => $totalManutencoes,
            'manutencoesPendentes' => $manutencoesPendentes,
            'manutencoesCurso' => $manutencoesCurso,
            'manutencoesConcluidas' => $manutencoesConcluidas,
            'equipamentosSemManutencao' => $equipamentosSemManutencao,
            'salasSemManutencao' => $salasSemManutencao,
            'countEquipamentos' => $countEquipamentos,
            'countSalas' => $countSalas,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCreate($equipamento_id = null, $sala_id = null)
    {
        $model = new Manutencao();

        // Preenche automaticamente se vier de um dos botões
        if ($equipamento_id) {
            $model->equipamento_id = $equipamento_id;
        }
        if ($sala_id) {
            $model->sala_id = $sala_id;
        }

        // Obter listas para dropdowns
        $tecnicos = User::find()->joinWith('roles')->where(['auth_assignment.item_name' => 'AssistenteManutencao'])->all();
        $tecnicosList = ArrayHelper::map($tecnicos, 'id', 'username');

        $equipamentos = Equipamento::find()->all();
        $equipamentosList = ArrayHelper::map($equipamentos, 'id', 'nome');

        $salas = Sala::find()->all();
        $salasList = ArrayHelper::map($salas, 'id', 'nome');

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Manutenção criada com sucesso!');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'tecnicosList' => $tecnicosList,
            'equipamentosList' => $equipamentosList,
            'salasList' => $salasList,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // Obter listas para dropdowns
        $tecnicos = User::find()->joinWith('roles')->where(['auth_assignment.item_name' => 'AssistenteManutencao'])->all();
        $tecnicosList = ArrayHelper::map($tecnicos, 'id', 'username');

        $equipamentos = Equipamento::find()->all();
        $equipamentosList = ArrayHelper::map($equipamentos, 'id', 'nome');

        $salas = Sala::find()->all();
        $salasList = ArrayHelper::map($salas, 'id', 'nome');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Manutenção atualizada com sucesso!');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'tecnicosList' => $tecnicosList,
            'equipamentosList' => $equipamentosList,
            'salasList' => $salasList,
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        try {
            if ($model->delete()) {
                Yii::$app->session->setFlash('success', 'Manutenção eliminada permanentemente com sucesso!');
            } else {
                Yii::$app->session->setFlash('error', 'Erro ao eliminar a manutenção.');
            }
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'Erro ao eliminar manutenção: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = Manutencao::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('A manutenção solicitada não existe.');
    }
}