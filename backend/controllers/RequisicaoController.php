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

/**
 * Controlador para gestão de Requisições (Reservas)
 * Permite CRUD de requisições de salas, com controlo de disponibilidade e calendário
 */
class RequisicaoController extends Controller
{
    /**
     * Configura comportamentos do controlador
     * Controla acesso autenticado e valida métodos HTTP
     */
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

    /**
     * Lista todas as requisições com pesquisa e estatísticas
     * Exibe estatísticas por estado (ativa, concluída, cancelada)
     */
    public function actionIndex()
    {
        // Modelo de pesquisa para filtragem de requisições
        $searchModel = new RequisicaoSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Estatísticas de requisições por estado
        $totalRequisicoes = Requisicao::find()->count();                            // Total de requisições
        $requisicoesAtivas = Requisicao::find()->where(['status' => 'Ativa'])->count();      // Requisições ativas
        $requisicoesConcluidas = Requisicao::find()->where(['status' => 'Concluída'])->count(); // Requisições concluídas
        $requisicoesCanceladas = Requisicao::find()->where(['status' => 'Cancelada'])->count();  // Requisições canceladas

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'totalRequisicoes' => $totalRequisicoes,
            'requisicoesAtivas' => $requisicoesAtivas,
            'requisicoesConcluidas' => $requisicoesConcluidas,
            'requisicoesCanceladas' => $requisicoesCanceladas,
        ]);
    }

    /**
     * Exibe detalhes de uma requisição específica
     * @param int $id ID da requisição a visualizar
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Cria uma nova requisição
     * Define automaticamente o utilizador atual e estado inicial
     */
    public function actionCreate()
    {
        $model = new Requisicao();
        $model->user_id = Yii::$app->user->id;  // Utilizador atual
        $model->status = 'Ativa';               // Estado inicial

        // Processa os dados do formulário se enviados via POST
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Requisição criada com sucesso! A sala foi marcada como "Em Uso".');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Atualiza uma requisição existente
     * @param int $id ID da requisição a atualizar
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // Processa os dados do formulário se enviados via POST
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Requisição atualizada com sucesso! O estado da sala foi atualizado.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Elimina uma requisição existente
     * Verifica se a requisição está ativa antes de permitir eliminação
     * @param int $id ID da requisição a eliminar
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        // Verifica se a requisição está ativa (não pode eliminar requisições ativas)
        if ($model->isAtiva()) {
            Yii::$app->session->setFlash('warning', 'Não pode eliminar uma requisição ativa. Cancele-a primeiro.');
            return $this->redirect(['view', 'id' => $id]);
        }

        $salaNome = $model->sala->nome ?? 'desconhecida';
        $model->delete();

        Yii::$app->session->setFlash('success', 'Requisição eliminada com sucesso! A sala "' . $salaNome . '" voltou ao estado "Livre".');
        return $this->redirect(['index']);
    }

    /**
     * Marca uma requisição como concluída
     * Atualiza o estado da sala associada para "Livre"
     * @param int $id ID da requisição a marcar como concluída
     */
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

    /**
     * Marca uma requisição como cancelada
     * Atualiza o estado da sala associada para "Livre"
     * @param int $id ID da requisição a marcar como cancelada
     */
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

    /**
     * Verifica disponibilidade de uma sala num determinado período (AJAX)
     * Usado para validação em tempo real no formulário de criação/atualização
     * @return string JSON com resultado da verificação
     */
    public function actionCheckAvailability()
    {
        $sala_id = Yii::$app->request->post('sala_id');
        $dataInicio = Yii::$app->request->post('dataInicio');
        $dataFim = Yii::$app->request->post('dataFim');
        $requisicao_id = Yii::$app->request->post('requisicao_id');

        $disponivel = $this->verificarDisponibilidade($sala_id, $dataInicio, $dataFim, $requisicao_id);

        return Json::encode(['disponivel' => $disponivel]);
    }

    /**
     * Verifica a disponibilidade de uma sala num período específico
     * Realiza verificações complexas de sobreposição de horários
     * @param int $sala_id ID da sala
     * @param string $dataInicio Data/hora de início no formato datetime-local
     * @param string $dataFim Data/hora de fim no formato datetime-local
     * @param int|null $exclude_id ID da requisição a excluir (para atualizações)
     * @return bool True se a sala estiver disponível, False caso contrário
     */
    private function verificarDisponibilidade($sala_id, $dataInicio, $dataFim, $exclude_id = null)
    {
        // Converte formato datetime-local para formato MySQL
        $dataInicioMySQL = date('Y-m-d H:i:s', strtotime($dataInicio));
        $dataFimMySQL = $dataFim ? date('Y-m-d H:i:s', strtotime($dataFim)) : null;

        // Verifica se a sala existe e está livre
        $sala = Sala::findOne($sala_id);
        if (!$sala || $sala->estado !== 'Livre') {
            return false;
        }

        // Verifica se o bloco da sala está ativo
        if (!$sala->bloco || $sala->bloco->estado !== 'ativo') {
            return false;
        }

        // Consulta complexa para verificar sobreposições de horário
        $query = Requisicao::find()
            ->where(['sala_id' => $sala_id])
            ->andWhere(['status' => 'Ativa'])  // Apenas requisições ativas
            ->andWhere(['or',
                // Sobreposição: início dentro do período
                ['between', 'dataInicio', $dataInicioMySQL, $dataFimMySQL],
                // Sobreposição: fim dentro do período
                ['between', 'dataFim', $dataInicioMySQL, $dataFimMySQL],
                // Sobreposição: período completamente dentro do existente
                ['and',
                    ['<=', 'dataInicio', $dataInicioMySQL],
                    ['>=', 'dataFim', $dataFimMySQL]
                ],
                // Sobreposição: período existente completamente dentro do novo
                ['and',
                    ['>=', 'dataInicio', $dataInicioMySQL],
                    ['<=', 'dataFim', $dataFimMySQL]
                ]
            ]);

        // Exclui a própria requisição durante uma atualização
        if ($exclude_id) {
            $query->andWhere(['!=', 'id', $exclude_id]);
        }

        // Retorna true se não houver sobreposições
        return $query->count() === 0;
    }

    /**
     * Exibe calendário com todas as requisições
     * Visualização gráfica das reservas ao longo do tempo
     */
    public function actionCalendar()
    {
        // Obtém todas as salas e requisições
        $salas = Sala::find()->all();
        $requisicoes = Requisicao::find()->all();

        // Prepara eventos para o calendário FullCalendar
        $events = [];
        foreach ($requisicoes as $requisicao) {
            $events[] = [
                'id' => $requisicao->id,
                'title' => $requisicao->sala->nome . ' (' . $requisicao->user->username . ')',
                'start' => $requisicao->dataInicio,  // Data/hora de início
                'end' => $requisicao->dataFim,       // Data/hora de fim
                'color' => $requisicao->status === 'Ativa' ? '#28a745' :     // Verde para ativas
                    ($requisicao->status === 'Concluída' ? '#6c757d' :      // Cinza para concluídas
                        '#dc3545'),                                            // Vermelho para canceladas
                'url' => Yii::$app->urlManager->createUrl(['requisicao/view', 'id' => $requisicao->id]), // Link para detalhes
            ];
        }

        return $this->render('calendar', [
            'salas' => $salas,
            'events' => Json::encode($events), // Codifica eventos para JavaScript
        ]);
    }

    /**
     * Encontra uma requisição pelo seu ID
     * Lança exceção se a requisição não for encontrada
     * @param int $id ID da requisição
     * @return Requisicao modelo da requisição encontrada
     * @throws NotFoundHttpException se a requisição não existir
     */
    protected function findModel($id)
    {
        // Procura a requisição pelo ID
        if (($model = Requisicao::findOne($id)) !== null) {
            return $model;
        }

        // Lança exceção se a requisição não for encontrada
        throw new NotFoundHttpException('A requisição solicitada não existe.');
    }
}