<?php

/** @var yii\web\View $this */
/** @var common\models\Bloco $model */

use yii\widgets\DetailView;
use yii\bootstrap5\Html;
use yii\grid\GridView;

$this->title = $model->nome;
$this->params['breadcrumbs'][] = ['label' => 'Gestão de Blocos Operatórios', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Calcular estatísticas das salas
$totalSalas = $model->salas ? count($model->salas) : 0;
$salasLivres = 0;
$salasOcupadas = 0;
$salasManutencao = 0;

if ($model->salas) {
    foreach ($model->salas as $sala) {
        switch ($sala->estado) {
            case 'Livre':
                $salasLivres++;
                break;
            case 'Ocupada':
                $salasOcupadas++;
                break;
            case 'Em Manutencao':
                $salasManutencao++;
                break;
        }
    }
}

?>

<div class="bloco-view">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">

                    <!-- Cabeçalho -->
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title">
                                <i class="fas fa-hospital me-2"></i>
                                Detalhes do Bloco: <?= Html::encode($model->nome) ?>
                            </h3>
                            <div>
                                <?= Html::a('<i class="fas fa-arrow-left me-2"></i>Voltar à Lista', ['index'], ['class' => 'btn btn-secondary btn-sm']) ?>
                                <?= Html::a('<i class="fas fa-edit me-2"></i>Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm']) ?>
                                <?= Html::a('<i class="fas fa-trash me-2"></i>Eliminar', ['delete', 'id' => $model->id], [
                                    'class' => 'btn btn-danger btn-sm',
                                    'data' => [
                                        'confirm' => 'Tem a certeza que deseja eliminar este bloco operatório?',
                                        'method' => 'post',
                                    ],
                                ]) ?>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Estatísticas Rápidas -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h3><?= $totalSalas ?></h3>
                                        <p>Total de Salas</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-door-open"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h3><?= $salasLivres ?></h3>
                                        <p>Salas Livres</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="small-box bg-danger">
                                    <div class="inner">
                                        <h3><?= $salasOcupadas ?></h3>
                                        <p>Salas Ocupadas</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-times-circle"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="small-box bg-warning">
                                    <div class="inner">
                                        <h3><?= $salasManutencao ?></h3>
                                        <p>Em Manutenção</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-tools"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informações do Bloco -->
                        <div class="row">
                            <div class="col-md-8">
                                <?= DetailView::widget([
                                    'model' => $model,
                                    'attributes' => [
                                        'id',
                                        'nome',
                                        [
                                            'attribute' => 'estado',
                                            'value' => function($model) {
                                                $estados = [
                                                    'ativo' => '<span class="badge bg-success">Ativo</span>',
                                                    'inativo' => '<span class="badge bg-danger">Inativo</span>',
                                                    'manutencao' => '<span class="badge bg-warning">Em Manutenção</span>',
                                                ];
                                                return $estados[$model->estado] ?? $model->estado;
                                            },
                                            'format' => 'raw',
                                        ],
                                    ],
                                    'options' => ['class' => 'table table-striped table-bordered'],
                                ]) ?>
                            </div>

                            <!-- Ações Rápidas -->
                            <div class="col-md-4">
                                <div class="card border-0 bg-light">
                                    <div class="card-header bg-transparent">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-bolt me-2"></i>Ações Rápidas
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <?= Html::a('<i class="fas fa-door-open me-2"></i>Gerir Salas', ['sala/index', 'SalaSearch[bloco_id]' => $model->id], [
                                            'class' => 'btn btn-info btn-block mb-2 text-left w-100',
                                        ]) ?>

                                        <?= Html::a('<i class="fas fa-plus me-2"></i>Adicionar Sala', ['sala/create', 'bloco_id' => $model->id], [
                                            'class' => 'btn btn-success btn-block mb-2 text-left w-100',
                                        ]) ?>

                                        <?= Html::a('<i class="fas fa-chart-bar me-2"></i>Relatórios', ['#'], [
                                            'class' => 'btn btn-warning btn-block mb-2 text-left w-100',
                                        ]) ?>

                                        <?= Html::a('<i class="fas fa-print me-2"></i>Imprimir', ['#'], [
                                            'class' => 'btn btn-secondary btn-block text-left w-100',
                                            'onclick' => 'window.print(); return false;'
                                        ]) ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Lista de Salas (se existirem) -->
                        <?php if ($model->salas): ?>
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card border-0">
                                        <div class="card-header bg-transparent">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-list me-2"></i>Salas deste Bloco (<?= $totalSalas ?>)
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Nome</th>
                                                        <th>Estado</th>
                                                        <th>Equipamentos</th>
                                                        <th width="120">Ações</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php foreach ($model->salas as $sala): ?>
                                                        <tr>
                                                            <td><?= $sala->id ?></td>
                                                            <td><?= Html::encode($sala->nome) ?></td>
                                                            <td>
                                                                <?php
                                                                $estadoSala = [
                                                                    'Livre' => '<span class="badge bg-success">Livre</span>',
                                                                    'Ocupada' => '<span class="badge bg-danger">Ocupada</span>',
                                                                    'Em Manutencao' => '<span class="badge bg-warning">Manutenção</span>',
                                                                ];
                                                                echo $estadoSala[$sala->estado] ?? $sala->estado;
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <?= $sala->salaEquipamentos ? count($sala->salaEquipamentos) : 0 ?>
                                                            </td>
                                                            <td>
                                                                <?= Html::a('<i class="fas fa-eye"></i>', ['sala/view', 'id' => $sala->id], [
                                                                    'class' => 'btn btn-xs btn-info',
                                                                    'title' => 'Ver detalhes'
                                                                ]) ?>
                                                                <?= Html::a('<i class="fas fa-edit"></i>', ['sala/update', 'id' => $sala->id], [
                                                                    'class' => 'btn btn-xs btn-warning',
                                                                    'title' => 'Editar'
                                                                ]) ?>
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
                        <?php else: ?>
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <h6><i class="fas fa-info-circle me-2"></i>Nenhuma Sala Encontrada</h6>
                                        <p class="mb-0">Este bloco ainda não tem salas associadas. <?= Html::a('Clique aqui para adicionar a primeira sala', ['sala/create', 'bloco_id' => $model->id]) ?>.</p>
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
    .small-box {
        border-radius: 0.25rem;
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        display: block;
        margin-bottom: 20px;
        position: relative;
    }
    .small-box > .inner {
        padding: 10px;
    }
    .small-box h3 {
        font-size: 2.2rem;
        font-weight: bold;
        margin: 0 0 10px 0;
        white-space: nowrap;
        padding: 0;
    }
    .small-box p {
        font-size: 0.9rem;
        margin: 0;
    }
    .small-box .icon {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 0;
        font-size: 70px;
        color: rgba(0,0,0,0.15);
        transition: all .3s linear;
    }
    .small-box:hover .icon {
        font-size: 75px;
    }
    .bg-info { background-color: #17a2b8 !important; color: white; }
    .bg-success { background-color: #28a745 !important; color: white; }
    .bg-danger { background-color: #dc3545 !important; color: white; }
    .bg-warning { background-color: #ffc107 !important; color: #212529; }
    .btn-block {
        display: block;
        width: 100%;
    }
</style>