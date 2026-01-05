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

/**
 * Controlador para gestão de Manutenções
 * Permite CRUD de manutenções de equipamentos e salas, com fluxo de estados
 */
class ManutencaoController extends Controller
{
    /**
     * Configura comportamentos do controlador
     * Controla acesso por roles e valida métodos HTTP
     */
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
                    'iniciar' => ['POST'],
                    'concluir' => ['POST'],
                    'iniciar-manutencao' => ['POST'],
                    'concluir-manutencao' => ['POST'],
                    'cancelar' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Obtém lista de técnicos (assistentes de manutenção)
     * @return array Lista de utilizadores com role 'AssistenteManutencao'
     */
    private function getTecnicos()
    {
        $auth = Yii::$app->authManager;
        $userIds = $auth->getUserIdsByRole('AssistenteManutencao');

        return User::find()
            ->where(['id' => $userIds, 'status' => User::STATUS_ACTIVE])
            ->all();
    }

    /**
     * Lista todas as manutenções com pesquisa e estatísticas
     * Inclui estatísticas por estado e itens em manutenção sem registo formal
     */
    public function actionIndex()
    {
        // Modelo de pesquisa para filtragem de manutenções
        $searchModel = new ManutencaoSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Estatísticas gerais de manutenções
        $totalManutencoes = Manutencao::find()->count();                        // Total de manutenções
        $manutencoesPendentes = Manutencao::find()->where(['status' => Manutencao::STATUS_PENDENTE])->count();    // Pendentes
        $manutencoesCurso = Manutencao::find()->where(['status' => Manutencao::STATUS_EM_CURSO])->count();        // Em curso
        $manutencoesConcluidas = Manutencao::find()->where(['status' => Manutencao::STATUS_CONCLUIDA])->count();  // Concluídas

        // Deteção de itens em estado de manutenção sem registo formal
        $equipamentosSemManutencao = Equipamento::getEquipamentosManutencaoSemRegisto(); // Equipamentos em manutenção sem registo
        $salasSemManutencao = Sala::getSalasManutencaoSemRegisto();                     // Salas em manutenção sem registo
        $countEquipamentos = count($equipamentosSemManutencao);                         // Contagem de equipamentos sem registo
        $countSalas = count($salasSemManutencao);                                       // Contagem de salas sem registo

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

    /**
     * Exibe detalhes de uma manutenção específica
     * @param int $id ID da manutenção a visualizar
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Cria uma nova manutenção
     * Pode ser criada a partir de um equipamento ou sala específica
     * @param int|null $equipamento_id ID do equipamento (opcional)
     * @param int|null $sala_id ID da sala (opcional)
     */
    public function actionCreate($equipamento_id = null, $sala_id = null)
    {
        $model = new Manutencao();

        // Preenche automaticamente se criar a partir de um equipamento
        if ($equipamento_id) {
            $equipamento = Equipamento::findOne($equipamento_id);
            if ($equipamento) {
                $model->equipamento_id = $equipamento_id;

                // Verifica se o equipamento já está em manutenção ativa
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

                // Preenche a data de início e define estado inicial
                $model->dataInicio = date('Y-m-d H:i:s');
                $model->status = Manutencao::STATUS_PENDENTE;
            }
        }

        // Preenche automaticamente se criar a partir de uma sala
        if ($sala_id) {
            $sala = Sala::findOne($sala_id);
            if ($sala) {
                $model->sala_id = $sala_id;

                // Verifica se a sala já está em manutenção ativa
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

        // Obtém listas para dropdowns - apenas itens disponíveis
        $tecnicos = $this->getTecnicos();
        $tecnicosList = ArrayHelper::map($tecnicos, 'id', 'username');

        $equipamentosDisponiveis = Manutencao::getEquipamentosDisponiveis(); // Equipamentos não em manutenção
        $equipamentosList = ArrayHelper::map($equipamentosDisponiveis, 'id', 'equipamento');

        $salasDisponiveis = Manutencao::getSalasDisponiveis(); // Salas não em manutenção
        $salasList = ArrayHelper::map($salasDisponiveis, 'id', 'nome');

        // Validação AJAX
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        // Processa submissão do formulário
        if ($model->load(Yii::$app->request->post())) {
            // Verificação dupla antes de guardar (validação no cliente e servidor)
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

            // Guarda o modelo se não houver erros
            if (!$model->hasErrors() && $model->save()) {
                Yii::$app->session->setFlash('success', 'Manutenção criada com sucesso!');

                // Atualiza o estado do equipamento para "Em Manutenção"
                if ($model->equipamento_id) {
                    $equipamento = Equipamento::findOne($model->equipamento_id);
                    if ($equipamento) {
                        $equipamento->estado = Equipamento::ESTADO_MANUTENCAO;
                        $equipamento->save(false);
                    }
                }

                // Atualiza o estado da sala para "Manutencao"
                if ($model->sala_id) {
                    $sala = Sala::findOne($model->sala_id);
                    if ($sala) {
                        $sala->estado = Sala::ESTADO_MANUTENCAO;
                        $sala->save(false);
                    }
                }

                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'tecnicosList' => $tecnicosList,
            'equipamentosList' => $equipamentosList,
            'salasList' => $salasList,
            'equipamento_id' => $equipamento_id,
            'sala_id' => $sala_id,
        ]);
    }

    /**
     * Atualiza uma manutenção existente
     * @param int $id ID da manutenção a atualizar
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // Obtém listas para dropdowns - itens disponíveis + item atual
        $tecnicos = $this->getTecnicos();
        $tecnicosList = ArrayHelper::map($tecnicos, 'id', 'username');

        $equipamentosDisponiveis = Manutencao::getEquipamentosDisponiveis();
        $equipamentosList = ArrayHelper::map($equipamentosDisponiveis, 'id', 'equipamento');

        // Inclui o equipamento atual na lista mesmo se estiver em manutenção
        if ($model->equipamento_id && !isset($equipamentosList[$model->equipamento_id])) {
            $equipamentoAtual = Equipamento::findOne($model->equipamento_id);
            if ($equipamentoAtual) {
                $equipamentosList[$model->equipamento_id] = $equipamentoAtual->equipamento . ' (atual)';
            }
        }

        $salasDisponiveis = Manutencao::getSalasDisponiveis();
        $salasList = ArrayHelper::map($salasDisponiveis, 'id', 'nome');

        // Inclui a sala atual na lista mesmo se estiver em manutenção
        if ($model->sala_id && !isset($salasList[$model->sala_id])) {
            $salaAtual = Sala::findOne($model->sala_id);
            if ($salaAtual) {
                $salasList[$model->sala_id] = $salaAtual->nome . ' (atual)';
            }
        }

        // Validação AJAX
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        // Processa submissão do formulário
        if ($model->load(Yii::$app->request->post())) {
            // Verifica se está a mudar para um equipamento que já está em manutenção
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

            // Verifica se está a mudar para uma sala que já está em manutenção
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

            // Guarda o modelo se não houver erros
            if (!$model->hasErrors() && $model->save()) {
                Yii::$app->session->setFlash('success', 'Manutenção atualizada com sucesso!');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
            'tecnicosList' => $tecnicosList,
            'equipamentosList' => $equipamentosList,
            'salasList' => $salasList,
            'equipamento_id' => null,
            'sala_id' => null,
        ]);
    }

    /**
     * Elimina uma manutenção existente
     * Reverte os estados dos equipamentos/salas se necessário
     * @param int $id ID da manutenção a eliminar
     */
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

            // Elimina a manutenção
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
     * Inicia uma manutenção (muda estado para "Em Curso")
     * @param int $id ID da manutenção a iniciar
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
     * Nome alternativo para actionIniciar
     * @param int $id ID da manutenção a iniciar
     */
    public function actionIniciarManutencao($id)
    {
        return $this->actionIniciar($id);
    }

    /**
     * Conclui uma manutenção (muda estado para "Concluída")
     * Reverte os estados dos equipamentos/salas para operacional/livre
     * @param int $id ID da manutenção a concluir
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
     * Nome alternativo para actionConcluir
     * @param int $id ID da manutenção a concluir
     */
    public function actionConcluirManutencao($id)
    {
        return $this->actionConcluir($id);
    }

    /**
     * Cancela uma manutenção (retorna para "Pendente")
     * @param int $id ID da manutenção a cancelar
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
     * Obtém informações detalhadas de um equipamento (AJAX)
     * @param int $id ID do equipamento
     * @return array Informações em formato JSON
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
     * Obtém a sala atual de um equipamento (AJAX)
     * @param int $id ID do equipamento
     * @return array Sala ID em formato JSON
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

    /**
     * Encontra uma manutenção pelo seu ID
     * Lança exceção se a manutenção não for encontrada
     * @param int $id ID da manutenção
     * @return Manutencao modelo da manutenção encontrada
     * @throws NotFoundHttpException se a manutenção não existir
     */
    protected function findModel($id)
    {
        // Procura a manutenção pelo ID
        if (($model = Manutencao::findOne($id)) !== null) {
            return $model;
        }

        // Lança exceção se a manutenção não for encontrada
        throw new NotFoundHttpException('A manutenção solicitada não existe.');
    }
}