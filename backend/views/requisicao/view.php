<?php

use yii\widgets\DetailView;
use yii\bootstrap5\Html;
use yii\grid\GridView;

$this->title = 'Requisição #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Gestão de Requisições', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Formatar datas
$dataInicio = Yii::$app->formatter->asDatetime($model->dataInicio, 'php:d/m/Y H:i');
$dataFim = $model->dataFim ? Yii::$app->formatter->asDatetime($model->dataFim, 'php:d/m/Y H:i') : 'Não definida';

// Calcular duração
$duracao = '';
if ($model->dataFim) {
    $inicio = new DateTime($model->dataInicio);
    $fim = new DateTime($model->dataFim);
    $interval = $inicio->diff($fim);

    $parts = [];
    if ($interval->y > 0) $parts[] = $interval->y . ' ano(s)';
    if ($interval->m > 0) $parts[] = $interval->m . ' mês(es)';
    if ($interval->d > 0) $parts[] = $interval->d . ' dia(s)';
    if ($interval->h > 0) $parts[] = $interval->h . ' hora(s)';
    if ($interval->i > 0) $parts[] = $interval->i . ' minuto(s)';

    $duracao = implode(', ', $parts);
} else {
    $duracao = 'Requisição contínua';
}

// Estado atual da requisição
$estadoBadge = [
    'Ativa' => '<span class="badge bg-success"><i class="fas fa-play-circle me-1"></i>Ativa</span>',
    'Concluída' => '<span class="badge bg-secondary"><i class="fas fa-check-circle me-1"></i>Concluída</span>',
    'Cancelada' => '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Cancelada</span>'
];
$estado = $estadoBadge[$model->status] ?? '<span class="badge bg-warning">Desconhecido</span>';

// Estado da sala
$salaEstadoBadge = [
    'Livre' => '<span class="badge bg-success"><i class="fas fa-circle-check me-1"></i>Livre</span>',
    'EmUso' => '<span class="badge bg-danger"><i class="fas fa-procedures me-1"></i>Em Uso</span>',
    'Manutencao' => '<span class="badge bg-warning"><i class="fas fa-tools me-1"></i>Manutenção</span>',
    'Desativada' => '<span class="badge bg-secondary"><i class="fas fa-ban me-1"></i>Desativada</span>'
];
$salaEstado = $model->sala ? ($salaEstadoBadge[$model->sala->estado] ?? '<span class="badge bg-dark">Desconhecido</span>') : 'N/A';
?>

<div class="requisicao-view">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <!-- Cabeçalho -->
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title">
                                <i class="fas fa-calendar-alt me-2"></i>
                                Detalhes da Requisição: #<?= $model->id ?>
                            </h3>
                            <div>
                                <?= Html::a('<i class="fas fa-arrow-left me-2"></i>Voltar à Lista', ['index'], ['class' => 'btn btn-secondary btn-sm']) ?>
                                <?php if ($model->isAtiva()): ?>
                                    <?= Html::a('<i class="fas fa-edit me-2"></i>Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm']) ?>

                                    <!-- Botão Concluir com formulário POST -->
                                    <?= Html::beginForm(['marcar-concluida', 'id' => $model->id], 'post', ['style' => 'display: inline;']) ?>
                                    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Tem a certeza que deseja marcar esta requisição como concluída? A sala voltará ao estado Livre.')">
                                        <i class="fas fa-check me-2"></i>Concluir
                                    </button>
                                    <?= Html::endForm() ?>

                                    <!-- Botão Cancelar com formulário POST -->
                                    <?= Html::beginForm(['marcar-cancelada', 'id' => $model->id], 'post', ['style' => 'display: inline;']) ?>
                                    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem a certeza que deseja cancelar esta requisição? A sala voltará ao estado Livre.')">
                                        <i class="fas fa-times me-2"></i>Cancelar
                                    </button>
                                    <?= Html::endForm() ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Informações principais -->
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <?= DetailView::widget([
                                    'model' => $model,
                                    'attributes' => [
                                        'id',
                                        [
                                            'attribute' => 'sala_id',
                                            'label' => 'Sala',
                                            'value' => function($model) {
                                                return Html::a($model->sala->nome, ['sala/view', 'id' => $model->sala_id], [
                                                    'class' => 'text-primary'
                                                ]);
                                            },
                                            'format' => 'raw',
                                        ],
                                        [
                                            'label' => 'Estado da Sala',
                                            'value' => function($model) use ($salaEstadoBadge) {
                                                if (!$model->sala) return 'N/A';
                                                return $salaEstadoBadge[$model->sala->estado] ?? '<span class="badge bg-dark">Desconhecido</span>';
                                            },
                                            'format' => 'raw',
                                        ],
                                        [
                                            'label' => 'Bloco',
                                            'value' => function($model) {
                                                return $model->sala->bloco->nome ?? '-';
                                            },
                                        ],
                                        [
                                            'attribute' => 'user_id',
                                            'label' => 'Utilizador',
                                            'value' => function($model) {
                                                return $model->user->username ?? '-';
                                            },
                                        ],
                                        [
                                            'attribute' => 'dataInicio',
                                            'value' => $dataInicio,
                                        ],
                                        [
                                            'attribute' => 'dataFim',
                                            'value' => $dataFim,
                                        ],
                                        [
                                            'label' => 'Duração',
                                            'value' => $duracao,
                                        ],
                                        [
                                            'attribute' => 'status',
                                            'value' => $estado,
                                            'format' => 'raw',
                                        ],
                                    ],
                                    'options' => ['class' => 'table table-striped table-bordered'],
                                ]) ?>
                            </div>

                            <!-- Ações Rápidas e Estado -->
                            <div class="col-md-4">
                                <div class="card border-0 bg-light h-100">
                                    <div class="card-header bg-transparent">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-bolt me-2"></i>Estado e Ações
                                        </h6>
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <div class="text-center mb-4">
                                            <div class="display-4 mb-2">
                                                <?= $estado ?>
                                            </div>
                                            <p class="text-muted small">
                                                <?php if ($model->isAtiva()): ?>
                                                    Requisição ativa e em uso<br>
                                                    <span class="badge bg-danger mt-2">Sala: Em Uso</span>
                                                <?php elseif ($model->isConcluida()): ?>
                                                    Requisição já concluída<br>
                                                    <span class="badge bg-success mt-2">Sala: Livre</span>
                                                <?php else: ?>
                                                    Requisição cancelada<br>
                                                    <span class="badge bg-success mt-2">Sala: Livre</span>
                                                <?php endif; ?>
                                            </p>
                                        </div>

                                        <div class="mt-auto">
                                            <?php if ($model->isAtiva()): ?>
                                                <?= Html::a('<i class="fas fa-door-open me-2"></i>Ver Sala', ['sala/view', 'id' => $model->sala_id], [
                                                    'class' => 'btn btn-info btn-block mb-2 text-left w-100',
                                                ]) ?>
                                                <?= Html::a('<i class="fas fa-calendar me-2"></i>Ver Calendário', ['calendar'], [
                                                    'class' => 'btn btn-warning btn-block mb-2 text-left w-100',
                                                ]) ?>
                                            <?php endif; ?>

                                            <?= Html::a('<i class="fas fa-print me-2"></i>Imprimir', ['#'], [
                                                'class' => 'btn btn-secondary btn-block text-left w-100',
                                                'onclick' => 'window.print(); return false;'
                                            ]) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informação Temporal -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card border-0">
                                    <div class="card-header bg-transparent">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-clock me-2"></i>Informação Temporal
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="text-center p-3 border rounded">
                                                    <i class="fas fa-hourglass-start fa-2x text-primary mb-2"></i>
                                                    <h6>Início</h6>
                                                    <p class="mb-1"><strong><?= $dataInicio ?></strong></p>
                                                    <small class="text-muted">Data e hora de início</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="text-center p-3 border rounded">
                                                    <i class="fas fa-hourglass-end fa-2x text-success mb-2"></i>
                                                    <h6>Fim</h6>
                                                    <p class="mb-1"><strong><?= $dataFim ?></strong></p>
                                                    <small class="text-muted">Data e hora de término</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="text-center p-3 border rounded">
                                                    <i class="fas fa-history fa-2x text-warning mb-2"></i>
                                                    <h6>Duração</h6>
                                                    <p class="mb-1"><strong><?= $duracao ?></strong></p>
                                                    <small class="text-muted">Tempo total da requisição</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Equipamentos (se existirem) -->
                        <?php if ($model->equipamentos && count($model->equipamentos) > 0): ?>
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card border-0">
                                        <div class="card-header bg-transparent">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-microchip me-2"></i>Equipamentos Associados
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Nome</th>
                                                        <th>Número Série</th>
                                                        <th>Estado</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php foreach ($model->equipamentos as $equipamento): ?>
                                                        <tr>
                                                            <td><?= $equipamento->id ?></td>
                                                            <td><?= Html::encode($equipamento->nome) ?></td>
                                                            <td><?= Html::encode($equipamento->numero_serie) ?></td>
                                                            <td>
                                                                <?php
                                                                $estadoEquip = [
                                                                    'Disponível' => '<span class="badge bg-success">Disponível</span>',
                                                                    'Em Uso' => '<span class="badge bg-warning">Em Uso</span>',
                                                                    'Manutenção' => '<span class="badge bg-danger">Manutenção</span>',
                                                                ];
                                                                echo $estadoEquip[$equipamento->estado] ?? $equipamento->estado;
                                                                ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS adicional -->
<style>
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        width: 200px;
    }
    .table td {
        vertical-align: middle;
    }
    .display-4 {
        font-size: 2.5rem;
    }
    .btn-block {
        display: block;
        width: 100%;
    }
    .border {
        border-color: #dee2e6 !important;
    }
    .border:hover {
        border-color: #3498db !important;
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }
    form {
        display: inline;
    }
</style>