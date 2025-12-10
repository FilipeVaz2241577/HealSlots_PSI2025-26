<?php

/** @var yii\web\View $this */
/** @var common\models\Equipamento $model */

use yii\bootstrap5\Html;
use hail812\adminlte\widgets\Alert;

$this->title = $model->numeroSerie;
$this->params['breadcrumbs'][] = ['label' => 'Equipamentos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-microscope me-2"></i>
                        Detalhes do Equipamento: <strong><?= Html::encode($model->numeroSerie) ?></strong>
                    </h3>
                    <div class="card-tools">
                        <?= Html::a('<i class="fas fa-edit me-1"></i> Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-warning btn-sm']) ?>
                        <?= Html::a('<i class="fas fa-trash me-1"></i> Eliminar', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-danger btn-sm',
                            'data' => [
                                'confirm' => 'Tem a certeza que deseja eliminar este equipamento?',
                                'method' => 'post',
                            ],
                        ]) ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered table-striped">
                                <tr>
                                    <th style="width: 40%;">ID</th>
                                    <td><?= $model->id ?></td>
                                </tr>
                                <tr>
                                    <th>Número de Série</th>
                                    <td>
                                        <span class="fw-bold text-primary"><?= Html::encode($model->numeroSerie) ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Tipo de Equipamento</th>
                                    <td>
                                        <?= $model->tipoEquipamento ?
                                            Html::encode($model->tipoEquipamento->nome) :
                                            '<span class="text-muted">N/A</span>' ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered table-striped">
                                <tr>
                                    <th style="width: 40%;">Estado</th>
                                    <td>
                                        <?php
                                        $badgeClass = [
                                            'Operacional' => 'bg-success',
                                            'Em Manutenção' => 'bg-warning',
                                            'Em Uso' => 'bg-primary'
                                        ];
                                        $estadoLabel = [
                                            'Operacional' => 'Operacional',
                                            'Em Manutenção' => 'Em Manutenção',
                                            'Em Uso' => 'Em Uso'
                                        ];
                                        $icon = [
                                            'Operacional' => 'fa-check-circle',
                                            'Em Manutenção' => 'fa-tools',
                                            'Em Uso' => 'fa-procedures'
                                        ];
                                        ?>
                                        <span class="badge <?= $badgeClass[$model->estado] ?? 'bg-dark' ?>">
                                            <i class="fas <?= $icon[$model->estado] ?? 'fa-question-circle' ?> me-1"></i>
                                            <?= $estadoLabel[$model->estado] ?? 'Desconhecido' ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <?= Html::a('<i class="fas fa-arrow-left me-1"></i> Voltar à Lista', ['index'], ['class' => 'btn btn-secondary']) ?>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="btn-group">
                                <?= Html::a('<i class="fas fa-edit me-1"></i> Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
                                <?= Html::a('<i class="fas fa-trash me-1"></i> Eliminar', ['delete', 'id' => $model->id], [
                                    'class' => 'btn btn-danger',
                                    'data' => [
                                        'confirm' => '🚨 ELIMINAÇÃO DE EQUIPAMENTO 🚨\n\nTem a ABSOLUTA certeza que deseja eliminar PERMANENTEMENTE o equipamento:\n➤ ' . $model->numeroSerie . '\n\n▶️ Esta ação NÃO pode ser desfeita!\n▶️ Todos os dados do equipamento serão PERDIDOS!\n\nDigite "ELIMINAR" para confirmar:',
                                        'method' => 'post',
                                    ],
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Salas onde o equipamento está -->
            <div class="card card-info mt-4">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-door-open me-2"></i>
                        Salas onde está localizado
                    </h3>
                </div>
                <div class="card-body">
                    <?php if ($model->salas): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                <tr>
                                    <th>Nome da Sala</th>
                                    <th>Bloco</th>
                                    <th>Estado</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($model->salas as $sala): ?>
                                    <tr>
                                        <td><?= Html::encode($sala->nome) ?></td>
                                        <td><?= $sala->bloco ? Html::encode($sala->bloco->nome) : '<span class="text-muted">N/A</span>' ?></td>
                                        <td>
                                            <?php
                                            $badgeClass = [
                                                'Livre' => 'bg-success',
                                                'EmUso' => 'bg-primary',
                                                'Manutencao' => 'bg-warning',
                                                'Desativada' => 'bg-danger'
                                            ];
                                            ?>
                                            <span class="badge <?= $badgeClass[$sala->estado] ?? 'bg-dark' ?>">
                                                    <?= $sala->estado ?>
                                                </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Este equipamento não está associado a nenhuma sala.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar com Informações -->
        <div class="col-md-4">
            <!-- Card de Estado -->
            <div class="card card-<?= $model->estado === 'Operacional' ? 'success' : ($model->estado === 'Em Uso' ? 'primary' : 'warning') ?>">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar me-2"></i>
                        Estado do Equipamento
                    </h3>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas <?= $icon[$model->estado] ?? 'fa-question-circle' ?> fa-4x mb-3 text-<?= $model->estado === 'Operacional' ? 'success' : ($model->estado === 'Em Uso' ? 'primary' : 'warning') ?>"></i>
                        <h3 class="text-<?= $model->estado === 'Operacional' ? 'success' : ($model->estado === 'Em Uso' ? 'primary' : 'warning') ?>">
                            <?= $estadoLabel[$model->estado] ?? 'Desconhecido' ?>
                        </h3>
                    </div>

                    <?php if ($model->estado === 'Operacional'): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            Este equipamento está operacional e pronto para uso.
                        </div>
                    <?php elseif ($model->estado === 'Em Uso'): ?>
                        <div class="alert alert-primary">
                            <i class="fas fa-procedures me-2"></i>
                            Este equipamento está atualmente em uso.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-tools me-2"></i>
                            Este equipamento está em manutenção.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Card de Ações Rápidas -->
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt me-2"></i>
                        Ações Rápidas
                    </h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?= Html::a('<i class="fas fa-microscope me-2"></i> Ver Todos Equipamentos', ['index'], ['class' => 'btn btn-outline-primary btn-block text-left']) ?>
                        <?= Html::a('<i class="fas fa-plus me-2"></i> Novo Equipamento', ['create'], ['class' => 'btn btn-outline-success btn-block text-left']) ?>
                        <?= Html::a('<i class="fas fa-door-open me-2"></i> Associar a Sala', ['/sala-equipamento/create', 'equipamento_id' => $model->id], ['class' => 'btn btn-outline-info btn-block text-left']) ?>
                    </div>
                </div>
            </div>

            <!-- Card de Informações do Sistema -->
            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle me-2"></i>
                        Informações do Sistema
                    </h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS Adicional -->
<style>
    .btn-block {
        text-align: left;
        padding: 0.75rem 1rem;
        margin-bottom: 0.5rem;
    }
    .table-sm td {
        padding: 0.3rem;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Confirmação melhorada para eliminação
        const deleteButtons = document.querySelectorAll('a.btn-danger[data-confirm]');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                const equipamentoName = '<?= addslashes($model->numeroSerie) ?>';
                const confirmationMessage = '🚨 ELIMINAÇÃO DE EQUIPAMENTO 🚨\n\n' +
                    'Tem a ABSOLUTA certeza que deseja eliminar PERMANENTEMENTE o equipamento:\n' +
                    '➤ ' + equipamentoName + '\n\n' +
                    '▶️ Esta ação NÃO pode ser desfeita!\n' +
                    '▶️ Todos os dados do equipamento serão PERDIDOS!\n' +
                    '▶️ Todas as associações com salas serão removidas!\n\n' +
                    'Digite "ELIMINAR" para confirmar:';

                const userInput = prompt(confirmationMessage);
                if (userInput !== 'ELIMINAR') {
                    e.preventDefault();
                    e.stopPropagation();
                    alert('❌ Eliminação cancelada.');
                    return false;
                }
            });
        });
    });
</script>