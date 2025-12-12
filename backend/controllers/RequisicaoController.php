<?php

namespace backend\controllers;

use Yii;
use common\models\Requisicao;
use backend\models\RequisicaoSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\Sala;
use yii\helpers\Json;

class RequisicaoController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'calendar',
                            'check-availability', 'marcar-concluida', 'marcar-cancelada'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'marcar-concluida' => ['POST'],
                    'marcar-cancelada' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new RequisicaoSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Estatísticas
        $totalRequisicoes = Requisicao::find()->count();
        $requisicoesAtivas = Requisicao::find()->where(['status' => 'Ativa'])->count();
        $requisicoesConcluidas = Requisicao::find()->where(['status' => 'Concluída'])->count();
        $requisicoesCanceladas = Requisicao::find()->where(['status' => 'Cancelada'])->count();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'totalRequisicoes' => $totalRequisicoes,
            'requisicoesAtivas' => $requisicoesAtivas,
            'requisicoesConcluidas' => $requisicoesConcluidas,
            'requisicoesCanceladas' => $requisicoesCanceladas,
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
        $model = new Requisicao();
        $model->user_id = Yii::$app->user->id;
        $model->status = 'Ativa';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Requisição criada com sucesso! A sala foi marcada como "Em Uso".');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Requisição atualizada com sucesso! O estado da sala foi atualizado.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->isAtiva()) {
            Yii::$app->session->setFlash('warning', 'Não pode eliminar uma requisição ativa. Cancele-a primeiro.');
            return $this->redirect(['view', 'id' => $id]);
        }

        $salaNome = $model->sala->nome ?? 'desconhecida';
        $model->delete();

        Yii::$app->session->setFlash('success', 'Requisição eliminada com sucesso! A sala "' . $salaNome . '" voltou ao estado "Livre".');
        return $this->redirect(['index']);
    }

    public function actionMarcarConcluida($id)
    {
        $model = $this->findModel($id);

        if ($model->marcarComoConcluida()) {
            Yii::$app->session->setFlash('success', 'Requisição marcada como concluída! A sala "' . ($model->sala->nome ?? '') . '" voltou ao estado "Livre".');
        } else {
            Yii::$app->session->setFlash('error', 'Erro ao marcar a requisição como concluída.');
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionMarcarCancelada($id)
    {
        $model = $this->findModel($id);

        if ($model->marcarComoCancelada()) {
            Yii::$app->session->setFlash('success', 'Requisição cancelada com sucesso! A sala "' . ($model->sala->nome ?? '') . '" voltou ao estado "Livre".');
        } else {
            Yii::$app->session->setFlash('error', 'Erro ao cancelar a requisição.');
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionCheckAvailability()
    {
        $sala_id = Yii::$app->request->post('sala_id');
        $dataInicio = Yii::$app->request->post('dataInicio');
        $dataFim = Yii::$app->request->post('dataFim');
        $requisicao_id = Yii::$app->request->post('requisicao_id');

        $disponivel = $this->verificarDisponibilidade($sala_id, $dataInicio, $dataFim, $requisicao_id);

        return Json::encode(['disponivel' => $disponivel]);
    }

    private function verificarDisponibilidade($sala_id, $dataInicio, $dataFim, $exclude_id = null)
    {
        // Converter datetime-local para formato MySQL
        $dataInicioMySQL = date('Y-m-d H:i:s', strtotime($dataInicio));
        $dataFimMySQL = $dataFim ? date('Y-m-d H:i:s', strtotime($dataFim)) : null;

        // Verificar se a sala existe e está livre
        $sala = Sala::findOne($sala_id);
        if (!$sala || $sala->estado !== 'Livre') {
            return false;
        }

        // Verificar se o bloco está ativo
        if (!$sala->bloco || $sala->bloco->estado !== 'ativo') {
            return false;
        }

        $query = Requisicao::find()
            ->where(['sala_id' => $sala_id])
            ->andWhere(['status' => 'Ativa'])
            ->andWhere(['or',
                ['between', 'dataInicio', $dataInicioMySQL, $dataFimMySQL],
                ['between', 'dataFim', $dataInicioMySQL, $dataFimMySQL],
                ['and',
                    ['<=', 'dataInicio', $dataInicioMySQL],
                    ['>=', 'dataFim', $dataFimMySQL]
                ],
                ['and',
                    ['>=', 'dataInicio', $dataInicioMySQL],
                    ['<=', 'dataFim', $dataFimMySQL]
                ]
            ]);

        if ($exclude_id) {
            $query->andWhere(['!=', 'id', $exclude_id]);
        }

        return $query->count() === 0;
    }

    public function actionCalendar()
    {
        $salas = Sala::find()->all();
        $requisicoes = Requisicao::find()->all();

        $events = [];
        foreach ($requisicoes as $requisicao) {
            $events[] = [
                'id' => $requisicao->id,
                'title' => $requisicao->sala->nome . ' (' . $requisicao->user->username . ')',
                'start' => $requisicao->dataInicio,
                'end' => $requisicao->dataFim,
                'color' => $requisicao->status === 'Ativa' ? '#28a745' :
                    ($requisicao->status === 'Concluída' ? '#6c757d' : '#dc3545'),
                'url' => Yii::$app->urlManager->createUrl(['requisicao/view', 'id' => $requisicao->id]),
            ];
        }

        return $this->render('calendar', [
            'salas' => $salas,
            'events' => Json::encode($events),
        ]);
    }

    protected function findModel($id)
    {
        if (($model = Requisicao::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('A requisição solicitada não existe.');
    }
}