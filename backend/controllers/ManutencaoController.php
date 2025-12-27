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
<<<<<<< HEAD
=======
use common\models\SalaEquipamento;
>>>>>>> origin/filipe

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
<<<<<<< HEAD
=======
                    'iniciar' => ['POST'],
                    'concluir' => ['POST'],
                    'iniciar-manutencao' => ['POST'],
                    'concluir-manutencao' => ['POST'],
                    'cancelar' => ['POST'],
>>>>>>> origin/filipe
                ],
            ],
        ];
    }

<<<<<<< HEAD
=======
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

>>>>>>> origin/filipe
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
<<<<<<< HEAD
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
=======
            $equipamento = Equipamento::findOne($equipamento_id);
            if ($equipamento) {
                $model->equipamento_id = $equipamento_id;

                // Verificar se o equipamento já está em manutenção
                $manutencaoAtiva = Manutencao::find()
                    ->where(['equipamento_id' => $equipamento_id])
                    ->andWhere(['status' => [Manutencao::STATUS_PENDENTE, Manutencao::STATUS_EM_CURSO]])
                    ->exists();

                if ($manutencaoAtiva) {
                    Yii::$app->session->setFlash('error', 'Este equipamento já está em manutenção ativa!');
                    return $this->redirect(['index']);
                }

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

        // Preenche automaticamente se vier de uma sala
        if ($sala_id) {
            $sala = Sala::findOne($sala_id);
            if ($sala) {
                $model->sala_id = $sala_id;

                // Verificar se a sala já está em manutenção
                $manutencaoAtiva = Manutencao::find()
                    ->where(['sala_id' => $sala_id])
                    ->andWhere(['status' => [Manutencao::STATUS_PENDENTE, Manutencao::STATUS_EM_CURSO]])
                    ->exists();

                if ($manutencaoAtiva) {
                    Yii::$app->session->setFlash('error', 'Esta sala já está em manutenção ativa!');
                    return $this->redirect(['index']);
                }

                $model->dataInicio = date('Y-m-d H:i:s');
                $model->status = Manutencao::STATUS_PENDENTE;
            }
        }

        // Obter listas para dropdowns - APENAS ITENS DISPONÍVEIS
        $tecnicos = $this->getTecnicos();
        $tecnicosList = ArrayHelper::map($tecnicos, 'id', 'username');

        // Usar o método novo para obter apenas equipamentos disponíveis
        $equipamentosDisponiveis = Manutencao::getEquipamentosDisponiveis();
        $equipamentosList = ArrayHelper::map($equipamentosDisponiveis, 'id', 'equipamento');

        // Usar o método novo para obter apenas salas disponíveis
        $salasDisponiveis = Manutencao::getSalasDisponiveis();
        $salasList = ArrayHelper::map($salasDisponiveis, 'id', 'nome');
>>>>>>> origin/filipe

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

<<<<<<< HEAD
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Manutenção criada com sucesso!');
            return $this->redirect(['view', 'id' => $model->id]);
=======
        if ($model->load(Yii::$app->request->post())) {
            // Verificar novamente antes de salvar
            if ($model->equipamento_id) {
                $manutencaoAtiva = Manutencao::find()
                    ->where(['equipamento_id' => $model->equipamento_id])
                    ->andWhere(['status' => [Manutencao::STATUS_PENDENTE, Manutencao::STATUS_EM_CURSO]])
                    ->andWhere(['not', ['id' => $model->id]])
                    ->exists();

                if ($manutencaoAtiva) {
                    $model->addError('equipamento_id', 'Este equipamento já está em manutenção ativa!');
                }
            }

            if ($model->sala_id) {
                $manutencaoAtiva = Manutencao::find()
                    ->where(['sala_id' => $model->sala_id])
                    ->andWhere(['status' => [Manutencao::STATUS_PENDENTE, Manutencao::STATUS_EM_CURSO]])
                    ->andWhere(['not', ['id' => $model->id]])
                    ->exists();

                if ($manutencaoAtiva) {
                    $model->addError('sala_id', 'Esta sala já está em manutenção ativa!');
                }
            }

            if (!$model->hasErrors() && $model->save()) {
                Yii::$app->session->setFlash('success', 'Manutenção criada com sucesso!');

                // Atualiza o estado do equipamento para "Em Manutenção" (se tiver equipamento)
                if ($model->equipamento_id) {
                    $equipamento = Equipamento::findOne($model->equipamento_id);
                    if ($equipamento) {
                        $equipamento->estado = Equipamento::ESTADO_MANUTENCAO;
                        $equipamento->save(false);
                    }
                }

                // Atualiza o estado da sala para "Manutencao" (se tiver sala)
                if ($model->sala_id) {
                    $sala = Sala::findOne($model->sala_id);
                    if ($sala) {
                        $sala->estado = Sala::ESTADO_MANUTENCAO;
                        $sala->save(false);
                    }
                }

                return $this->redirect(['view', 'id' => $model->id]);
            }
>>>>>>> origin/filipe
        }

        return $this->render('create', [
            'model' => $model,
            'tecnicosList' => $tecnicosList,
            'equipamentosList' => $equipamentosList,
            'salasList' => $salasList,
<<<<<<< HEAD
=======
            'equipamento_id' => $equipamento_id,
            'sala_id' => $sala_id,
>>>>>>> origin/filipe
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

<<<<<<< HEAD
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
=======
        // Obter listas para dropdowns - APENAS ITENS DISPONÍVEIS + o item atual
        $tecnicos = $this->getTecnicos();
        $tecnicosList = ArrayHelper::map($tecnicos, 'id', 'username');

        // Para update, precisamos incluir o equipamento atual mesmo se estiver em manutenção
        $equipamentosDisponiveis = Manutencao::getEquipamentosDisponiveis();
        $equipamentosList = ArrayHelper::map($equipamentosDisponiveis, 'id', 'equipamento');

        // Adicionar o equipamento atual à lista se não estiver já
        if ($model->equipamento_id && !isset($equipamentosList[$model->equipamento_id])) {
            $equipamentoAtual = Equipamento::findOne($model->equipamento_id);
            if ($equipamentoAtual) {
                $equipamentosList[$model->equipamento_id] = $equipamentoAtual->equipamento . ' (atual)';
            }
        }

        // Para update, precisamos incluir a sala atual mesmo se estiver em manutenção
        $salasDisponiveis = Manutencao::getSalasDisponiveis();
        $salasList = ArrayHelper::map($salasDisponiveis, 'id', 'nome');

        // Adicionar a sala atual à lista se não estiver já
        if ($model->sala_id && !isset($salasList[$model->sala_id])) {
            $salaAtual = Sala::findOne($model->sala_id);
            if ($salaAtual) {
                $salasList[$model->sala_id] = $salaAtual->nome . ' (atual)';
            }
        }

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            // Verificar se está a tentar mudar para um equipamento que já está em manutenção
            if ($model->equipamento_id && $model->equipamento_id != $model->getOldAttribute('equipamento_id')) {
                $manutencaoAtiva = Manutencao::find()
                    ->where(['equipamento_id' => $model->equipamento_id])
                    ->andWhere(['status' => [Manutencao::STATUS_PENDENTE, Manutencao::STATUS_EM_CURSO]])
                    ->andWhere(['not', ['id' => $model->id]])
                    ->exists();

                if ($manutencaoAtiva) {
                    $model->addError('equipamento_id', 'Este equipamento já está em manutenção ativa!');
                }
            }

            // Verificar se está a tentar mudar para uma sala que já está em manutenção
            if ($model->sala_id && $model->sala_id != $model->getOldAttribute('sala_id')) {
                $manutencaoAtiva = Manutencao::find()
                    ->where(['sala_id' => $model->sala_id])
                    ->andWhere(['status' => [Manutencao::STATUS_PENDENTE, Manutencao::STATUS_EM_CURSO]])
                    ->andWhere(['not', ['id' => $model->id]])
                    ->exists();

                if ($manutencaoAtiva) {
                    $model->addError('sala_id', 'Esta sala já está em manutenção ativa!');
                }
            }

            if (!$model->hasErrors() && $model->save()) {
                Yii::$app->session->setFlash('success', 'Manutenção atualizada com sucesso!');
                return $this->redirect(['view', 'id' => $model->id]);
            }
>>>>>>> origin/filipe
        }

        return $this->render('update', [
            'model' => $model,
            'tecnicosList' => $tecnicosList,
            'equipamentosList' => $equipamentosList,
            'salasList' => $salasList,
<<<<<<< HEAD
=======
            'equipamento_id' => null,
            'sala_id' => null,
>>>>>>> origin/filipe
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        try {
<<<<<<< HEAD
=======
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

>>>>>>> origin/filipe
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

<<<<<<< HEAD
=======
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
     * Action para iniciar manutenção (nome alternativo)
     */
    public function actionIniciarManutencao($id)
    {
        return $this->actionIniciar($id);
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
                // Atualiza o estado do equipamento para "Operacional" (se tiver equipamento)
                if ($model->equipamento_id) {
                    $equipamento = Equipamento::findOne($model->equipamento_id);
                    if ($equipamento) {
                        $equipamento->estado = Equipamento::ESTADO_OPERACIONAL;
                        $equipamento->save(false);
                    }
                }

                // Atualiza o estado da sala para "Livre" (se tiver sala)
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
     * Action para concluir manutenção (nome alternativo)
     */
    public function actionConcluirManutencao($id)
    {
        return $this->actionConcluir($id);
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

>>>>>>> origin/filipe
    protected function findModel($id)
    {
        if (($model = Manutencao::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('A manutenção solicitada não existe.');
    }
}