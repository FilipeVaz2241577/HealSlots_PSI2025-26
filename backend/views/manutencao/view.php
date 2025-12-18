<?php

/** @var yii\web\View $this */
/** @var common\models\Manutencao $model */

use yii\bootstrap5\Html;
use yii\widgets\DetailView;

$this->title = 'Manutenção #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Manutenções', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Calcular duração
$duracao = null;
if ($model->dataFim && $model->dataInicio) {
    $inicio = new DateTime($model->dataInicio);
    $fim = new DateTime($model->dataFim);
    $interval = $inicio->diff($fim);
    $duracao = $interval->format('%d dias, %h horas, %i minutos');
}

// Sala atual do equipamento
$salaAtual = null;
if ($model->equipamento) {
    $salaEquipamento = \common\models\SalaEquipamento::find()
        ->where(['idEquipamento' => $model->equipamento_id])
        ->one();
    if ($salaEquipamento) {
        $salaAtual = $salaEquipamento->sala;
    }
}
?>

<div class="manutencao-view">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('<i class="fas fa-arrow-left"></i> Voltar', ['index'], ['class' => 'btn btn-secondary']) ?>
            <?= Html::a('<i class="fas fa-edit"></i> Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
            <?= Html::a('<i class="fas fa-trash"></i> Eliminar', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Tem a certeza que deseja eliminar esta manutenção?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informações da Manutenção</h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-striped'],
                        'attributes' => [
                            [
                                'attribute' => 'equipamento_id',
                                'value' => $model->equipamento ?
                                    Html::encode($model->equipamento->equipamento) .
                                    ($model->equipamento->numeroSerie ?
                                        '<br><small class="text-muted">Série: ' . Html::encode($model->equipamento->numeroSerie) . '</small>' : '') :
                                    '-',
                                'format' => 'raw',
                            ],
                            [
                                'label' => 'Localização',
                                'value' => function() use ($model, $salaAtual) {
                                    if ($model->sala) {
                                        $html = Html::encode($model->sala->nome);
                                        if ($model->sala->bloco) {
                                            $html .= ' (' . Html::encode($model->sala->bloco->nome) . ')';
                                        }
                                        return $html;
                                    } elseif ($salaAtual) {
                                        $html = Html::encode($salaAtual->nome);
                                        if ($salaAtual->bloco) {
                                            $html .= ' (' . Html::encode($salaAtual->bloco->nome) . ')';
                                        }
                                        return $html . '<br><small class="text-muted">(Localização do equipamento)</small>';
                                    }
                                    return 'Não definido';
                                },
                                'format' => 'raw',
                            ],
                            [
                                'attribute' => 'user_id',
                                'value' => $model->user ?
                                    Html::encode($model->user->username) .
                                    '<br><small class="text-muted">' . Html::encode($model->user->email) . '</small>' :
                                    'Não atribuído',
                                'format' => 'raw',
                            ],
                            [
                                'attribute' => 'dataInicio',
                                'format' => ['datetime', 'php:d/m/Y H:i'],
                            ],
                            [
                                'attribute' => 'dataFim',
                                'format' => ['datetime', 'php:d/m/Y H:i'],
                                'value' => $model->dataFim ?
                                    Yii::$app->formatter->asDatetime($model->dataFim, 'php:d/m/Y H:i') :
                                    Html::tag('span', 'Em andamento', ['class' => 'badge bg-warning']),
                                'format' => 'raw',
                            ],
                            [
                                'attribute' => 'status',
                                'value' => $model->getStatusBadge(),
                                'format' => 'raw',
                            ],
                        ],
                    ]) ?>
                </div>
            </div>

            <?php if ($model->descricao): ?>
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Descrição</h5>
                    </div>
                    <div class="card-body">
                        <div class="p-3 bg-light rounded">
                            <?= nl2br(Html::encode($model->descricao)) ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Informações Adicionais</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Duração:</strong><br>
                        <?= $duracao ? $duracao : 'Ainda em progresso' ?>
                    </div>
                    <div class="mb-3">
                        <strong>Estado do Equipamento:</strong><br>
                        <?= $model->equipamento ? $model->equipamento->getEstadoBadge() : '-' ?>
                    </div>
                    <?php if ($model->equipamento && $model->equipamento->tipoEquipamento): ?>
                        <div class="mb-3">
                            <strong>Tipo de Equipamento:</strong><br>
                            <?= Html::encode($model->equipamento->tipoEquipamento->nome) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Ações Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if ($model->status === \common\models\Manutencao::STATUS_PENDENTE): ?>
                            <?= Html::a('<i class="fas fa-play"></i> Iniciar Manutenção', ['iniciar', 'id' => $model->id], [
                                'class' => 'btn btn-success',
                                'data' => [
                                    'confirm' => 'Deseja iniciar esta manutenção?',
                                    'method' => 'post',
                                ],
                            ]) ?>
                        <?php elseif ($model->status === \common\models\Manutencao::STATUS_EM_CURSO && !$model->dataFim): ?>
                            <?= Html::a('<i class="fas fa-check"></i> Concluir Manutenção', ['concluir', 'id' => $model->id], [
                                'class' => 'btn btn-primary',
                                'data' => [
                                    'confirm' => 'Deseja marcar esta manutenção como concluída?',
                                    'method' => 'post',
                                ],
                            ]) ?>
                        <?php endif; ?>

                        <?php if ($model->equipamento): ?>
                            <?= Html::a('<i class="fas fa-eye"></i> Ver Equipamento', ['equipamento/view', 'id' => $model->equipamento_id], [
                                'class' => 'btn btn-outline-info',
                            ]) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>