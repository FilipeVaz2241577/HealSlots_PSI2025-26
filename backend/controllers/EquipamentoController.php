<?php

namespace backend\controllers;

use Yii;
use common\models\Equipamento;
use backend\models\EquipamentoSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\TipoEquipamento;

/**
 * Controlador para gestão de Equipamentos
 * Permite CRUD de equipamentos médicos e visualização de estatísticas
 */
class EquipamentoController extends Controller
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
     * Lista todos os equipamentos com pesquisa e estatísticas
     * Exibe estatísticas por estado (operacional, manutenção, em uso)
     */
    public function actionIndex()
    {
        // Modelo de pesquisa para filtragem de equipamentos
        $searchModel = new EquipamentoSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Obtém estatísticas agrupadas por estado
        $estatisticas = Equipamento::getCountByEstado();

        // Cálculo de estatísticas para exibição
        $totalEquipamentos = Equipamento::find()->count();                     // Total de equipamentos
        $operacionaisCount = $estatisticas[Equipamento::ESTADO_OPERACIONAL] ?? 0;  // Equipamentos operacionais
        $manutencaoCount = $estatisticas[Equipamento::ESTADO_MANUTENCAO] ?? 0;     // Equipamentos em manutenção
        $emUsoCount = $estatisticas[Equipamento::ESTADO_EM_USO] ?? 0;             // Equipamentos em uso

        // Renderiza a vista index com dados e estatísticas
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'totalEquipamentosCount' => $totalEquipamentos,
            'operacionaisCount' => $operacionaisCount,
            'manutencaoCount' => $manutencaoCount,
            'emUsoCount' => $emUsoCount,
        ]);
    }

    /**
     * Exibe detalhes de um equipamento específico
     * @param int $id ID do equipamento a visualizar
     */
    public function actionView($id)
    {
        // Busca e renderiza o modelo do equipamento
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Cria um novo equipamento
     * Processa o formulário com lista de tipos de equipamento
     */
    public function actionCreate()
    {
        $model = new Equipamento(); // Nova instância de equipamento

        // Obtém lista de tipos de equipamento para dropdown
        $tiposEquipamento = TipoEquipamento::find()
            ->select(['nome', 'id'])
            ->indexBy('id')
            ->column();

        // Processa os dados do formulário se enviados via POST
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // Mensagem de sucesso e redirecionamento
            Yii::$app->session->setFlash('success', 'Equipamento criado com sucesso!');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        // Renderiza o formulário de criação com tipos de equipamento
        return $this->render('create', [
            'model' => $model,
            'tiposEquipamento' => $tiposEquipamento,
        ]);
    }

    /**
     * Atualiza um equipamento existente
     * @param int $id ID do equipamento a atualizar
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id); // Busca o equipamento pelo ID

        // Obtém lista de tipos de equipamento para dropdown
        $tiposEquipamento = TipoEquipamento::find()
            ->select(['nome', 'id'])
            ->indexBy('id')
            ->column();

        // Processa os dados do formulário se enviados via POST
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // Mensagem de sucesso e redirecionamento
            Yii::$app->session->setFlash('success', 'Equipamento atualizado com sucesso!');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        // Renderiza o formulário de atualização com tipos de equipamento
        return $this->render('update', [
            'model' => $model,
            'tiposEquipamento' => $tiposEquipamento,
        ]);
    }

    /**
     * Elimina um equipamento existente
     * @param int $id ID do equipamento a eliminar
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();

        // Mensagem de sucesso e redirecionamento
        Yii::$app->session->setFlash('success', 'Equipamento eliminado com sucesso!');
        return $this->redirect(['index']);
    }

    /**
     * Encontra um equipamento pelo seu ID
     * Lança exceção se o equipamento não for encontrado
     * @param int $id ID do equipamento
     * @return Equipamento modelo do equipamento encontrado
     * @throws NotFoundHttpException se o equipamento não existir
     */
    protected function findModel($id)
    {
        // Procura o equipamento pelo ID
        if (($model = Equipamento::findOne($id)) !== null) {
            return $model;
        }

        // Lança exceção se o equipamento não for encontrado
        throw new NotFoundHttpException('O equipamento solicitado não existe.');
    }
}