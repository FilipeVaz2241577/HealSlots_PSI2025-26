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
use common\models\SalaEquipamento;

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

    /**
     * Método auxiliar para obter técnicos
     */
    private function getTecnicos()
    {
        $auth = Yii::$app->authManager;
        $userIds = $auth->getUserIdsByRole('AssistenteManutencao');

        return User::find()
            ->where(['id' => $userIds, 'status' => User::STATUS_ACTIVE])
            ->all();
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

    public function actionCreate($equipamento_id = null)
    {
        $model = new Manutencao();

        // Preenche automaticamente se vier de um dos botões
        if ($equipamento_id) {
            $equipamento = Equipamento::findOne($equipamento_id);


            if ($equipamento) {
                $model->equipamento_id = $equipamento_id;

                //$equipamento->salaEquipamento;

                // Busca a sala onde o equipamento está atualmente (se existir)
                $salaEquipamento = SalaEquipamento::find()
                    ->where(['idEquipamento' => $equipamento_id])
                    ->one();

                if ($salaEquipamento) {
                    $model->sala_id = $salaEquipamento->idSala;
                }

                // Preenche a data de início com a data/hora atual
                $model->dataInicio = date('Y-m-d H:i:s');

                // Define status como Pendente
                $model->status = Manutencao::STATUS_PENDENTE;
            }
        }

        // Obter listas para dropdowns
        $tecnicos = $this->getTecnicos();
        $tecnicosList = ArrayHelper::map($tecnicos, 'id', 'username');

        $equipamentos = Equipamento::find()->all();
        $equipamentosList = ArrayHelper::map($equipamentos, 'id', 'equipamento');

        $salas = Sala::find()->all();
        $salasList = ArrayHelper::map($salas, 'id', 'nome');

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Manutenção criada com sucesso!');

            // Atualiza o estado do equipamento para "Em Manutenção"
            if ($model->equipamento_id) {
                //$equipamento = Equipamento::findOne($model->equipamento_id);
                if ($model->equipamento) {
                    $model->equipamento->estado = Equipamento::ESTADO_MANUTENCAO;

                    $model->equipamento->save(false);
                }
            }

            // Atualiza o estado da sala para "Manutencao" se a sala foi especificada
            if ($model->sala->id) {
                $sala = Sala::findOne($model->sala_id);
                if ($sala) {
                    $model->sala->estado = Sala::ESTADO_MANUTENCAO;
                    $sala->save(false);
                }
            }

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'tecnicosList' => $tecnicosList,
            'equipamentosList' => $equipamentosList,
            'salasList' => $salasList,
            'equipamento_id' => $equipamento_id,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // Obter listas para dropdowns
        $tecnicos = $this->getTecnicos();
        $tecnicosList = ArrayHelper::map($tecnicos, 'id', 'username');

        $equipamentos = Equipamento::find()->all();
        $equipamentosList = ArrayHelper::map($equipamentos, 'id', 'equipamento');

        $salas = Sala::find()->all();
        $salasList = ArrayHelper::map($salas, 'id', 'nome');

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Manutenção atualizada com sucesso!');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'tecnicosList' => $tecnicosList,
            'equipamentosList' => $equipamentosList,
            'salasList' => $salasList,
            'equipamento_id' => null, // Adiciona esta linha
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        try {
            // Antes de eliminar, verifica se precisa reverter o estado do equipamento
            if ($model->equipamento_id) {
                $equipamento = Equipamento::findOne($model->equipamento_id);
                if ($equipamento && $equipamento->estado === Equipamento::ESTADO_MANUTENCAO) {
                    // Verifica se não há outras manutenções ativas para este equipamento
                    $outrasManutencoes = Manutencao::find()
                        ->where(['equipamento_id' => $model->equipamento_id])
                        ->andWhere(['!=', 'id', $model->id])
                        ->andWhere(['status' => [Manutencao::STATUS_PENDENTE, Manutencao::STATUS_EM_CURSO]])
                        ->exists();

                    if (!$outrasManutencoes) {
                        $equipamento->estado = Equipamento::ESTADO_OPERACIONAL;
                        $equipamento->save(false);
                    }
                }
            }

            // Antes de eliminar, verifica se precisa reverter o estado da sala
            if ($model->sala_id) {
                $sala = Sala::findOne($model->sala_id);
                if ($sala && $sala->estado === Sala::ESTADO_MANUTENCAO) {
                    // Verifica se não há outras manutenções ativas para esta sala
                    $outrasManutencoes = Manutencao::find()
                        ->where(['sala_id' => $model->sala_id])
                        ->andWhere(['!=', 'id', $model->id])
                        ->andWhere(['status' => [Manutencao::STATUS_PENDENTE, Manutencao::STATUS_EM_CURSO]])
                        ->exists();

                    if (!$outrasManutencoes) {
                        $sala->estado = Sala::ESTADO_LIVRE;
                        $sala->save(false);
                    }
                }
            }

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

    /**
     * Action para iniciar manutenção
     */
    public function actionIniciar($id)
    {
        $model = $this->findModel($id);

        if ($model->status === Manutencao::STATUS_PENDENTE) {
            $model->status = Manutencao::STATUS_EM_CURSO;
            $model->dataInicio = date('Y-m-d H:i:s');

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Manutenção iniciada com sucesso!');
            } else {
                Yii::$app->session->setFlash('error', 'Erro ao iniciar manutenção.');
            }
        } else {
            Yii::$app->session->setFlash('warning', 'Esta manutenção já está em curso ou concluída.');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Action para concluir manutenção
     */
    public function actionConcluir($id)
    {
        $model = $this->findModel($id);

        if ($model->status === Manutencao::STATUS_EM_CURSO) {
            $model->status = Manutencao::STATUS_CONCLUIDA;
            $model->dataFim = date('Y-m-d H:i:s');

            if ($model->save()) {
                // Atualiza o estado do equipamento para "Operacional"
                if ($model->equipamento_id) {
                    $equipamento = Equipamento::findOne($model->equipamento_id);
                    if ($equipamento) {
                        $equipamento->estado = Equipamento::ESTADO_OPERACIONAL;
                        $equipamento->save(false);
                    }
                }

                // Atualiza o estado da sala para "Livre"
                if ($model->sala_id) {
                    $sala = Sala::findOne($model->sala_id);
                    if ($sala) {
                        $sala->estado = Sala::ESTADO_LIVRE;
                        $sala->save(false);
                    }
                }

                Yii::$app->session->setFlash('success', 'Manutenção concluída com sucesso!');
            } else {
                Yii::$app->session->setFlash('error', 'Erro ao concluir manutenção.');
            }
        } else {
            Yii::$app->session->setFlash('warning', 'Apenas manutenções em curso podem ser concluídas.');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Action para cancelar manutenção
     */
    public function actionCancelar($id)
    {
        $model = $this->findModel($id);

        if ($model->status !== Manutencao::STATUS_CONCLUIDA) {
            $model->status = Manutencao::STATUS_PENDENTE;
            $model->dataFim = null;

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Manutenção cancelada/retornada para pendente!');
            } else {
                Yii::$app->session->setFlash('error', 'Erro ao cancelar manutenção.');
            }
        } else {
            Yii::$app->session->setFlash('warning', 'Manutenções concluídas não podem ser canceladas.');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Action AJAX para obter informações do equipamento
     */
    public function actionGetEquipamentoInfo($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $equipamento = Equipamento::findOne($id);
        if (!$equipamento) {
            return ['success' => false, 'message' => 'Equipamento não encontrado'];
        }

        $salaEquipamento = SalaEquipamento::find()
            ->where(['idEquipamento' => $id])
            ->one();

        $sala = null;
        $sala_id = null;
        if ($salaEquipamento) {
            $sala = Sala::findOne($salaEquipamento->idSala);
            $sala_id = $salaEquipamento->idSala;
        }

        return [
            'success' => true,
            'equipamento' => [
                'nome' => $equipamento->equipamento,
                'numeroSerie' => $equipamento->numeroSerie,
                'tipo' => $equipamento->tipoEquipamento ? $equipamento->tipoEquipamento->nome : 'N/A',
                'estado' => $equipamento->estado,
            ],
            'sala' => $sala ? [
                'id' => $sala_id,
                'nome' => $sala->nome,
                'bloco' => $sala->bloco ? $sala->bloco->nome : 'N/A',
            ] : null,
        ];
    }

    /**
     * Action AJAX para obter a sala atual do equipamento
     */
    public function actionGetEquipamentoSala($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $salaEquipamento = SalaEquipamento::find()
            ->where(['idEquipamento' => $id])
            ->one();

        if ($salaEquipamento) {
            return [
                'success' => true,
                'sala_id' => $salaEquipamento->idSala,
            ];
        }

        return ['success' => false];
    }

    protected function findModel($id)
    {
        if (($model = Manutencao::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('A manutenção solicitada não existe.');
    }
}