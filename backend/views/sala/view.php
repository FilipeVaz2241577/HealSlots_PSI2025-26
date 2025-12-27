<?php

/** @var yii\web\View $this */
/** @var common\models\Sala $model */

use yii\bootstrap5\Html;
use hail812\adminlte\widgets\Alert;

$this->title = $model->nome;
$this->params['breadcrumbs'][] = ['label' => 'Salas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-door-open me-2"></i>
                        Detalhes da Sala: <strong><?= Html::encode($model->nome) ?></strong>
                    </h3>
<<<<<<< HEAD
                    <div class="card-tools">
                        <?= Html::a('<i class="fas fa-edit me-1"></i> Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-warning btn-sm']) ?>
                        <?= Html::a('<i class="fas fa-trash me-1"></i> Eliminar', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-danger btn-sm',
                            'data' => [
                                'confirm' => 'Tem a certeza que deseja eliminar esta sala?',
                                'method' => 'post',
                            ],
                        ]) ?>
                    </div>
=======
>>>>>>> origin/filipe
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
                                    <th>Nome</th>
                                    <td>
                                        <span class="fw-bold text-primary"><?= Html::encode($model->nome) ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Bloco</th>
                                    <td>
                                        <?php if ($model->bloco): ?>
                                            <?= Html::a(
                                                '<i class="fas fa-building me-1"></i>' . Html::encode($model->bloco->nome),
                                                ['bloco/view', 'id' => $model->bloco_id],
                                                ['class' => 'text-success fw-bold text-decoration-none']
                                            ) ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
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
                                            'Livre' => 'bg-success',
                                            'EmUso' => 'bg-danger',
                                            'Manutencao' => 'bg-warning',
                                            'Desativada' => 'bg-secondary'
                                        ];
                                        $estadoLabel = [
                                            'Livre' => 'Livre',
                                            'EmUso' => 'Em Uso',
                                            'Manutencao' => 'Manutenção',
                                            'Desativada' => 'Desativada'
                                        ];
                                        $icon = [
                                            'Livre' => 'fa-circle-check',
                                            'EmUso' => 'fa-procedures',
                                            'Manutencao' => 'fa-tools',
                                            'Desativada' => 'fa-ban'
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

                    <!-- Informações Adicionais -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card card-info">
                                <div class="card-header">
                                    <h4 class="card-title">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Informações Adicionais
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-4">
                                            <div class="info-box bg-light">
                                                <span class="info-box-icon bg-primary">
                                                    <i class="fas fa-building"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Bloco Associado</span>
                                                    <span class="info-box-number">
                                                        <?= $model->bloco ? Html::encode($model->bloco->nome) : 'N/A' ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box bg-light">
                                                <span class="info-box-icon <?= $badgeClass[$model->estado] ?? 'bg-dark' ?>">
                                                    <i class="fas <?= $icon[$model->estado] ?? 'fa-question-circle' ?>"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Estado Atual</span>
                                                    <span class="info-box-number">
                                                        <?= $estadoLabel[$model->estado] ?? 'Desconhecido' ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box bg-light">
                                                <span class="info-box-icon bg-info">
                                                    <i class="fas fa-id-card"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">ID da Sala</span>
                                                    <span class="info-box-number">#<?= $model->id ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                                        'confirm' => '🚨 ELIMINAÇÃO DE SALA OPERATÓRIA 🚨\n\nTem a ABSOLUTA certeza que deseja eliminar PERMANENTEMENTE a sala:\n➤ ' . $model->nome . '\n\n▶️ Esta ação NÃO pode ser desfeita!\n▶️ Todos os dados da sala serão PERDIDOS!\n\nDigite "ELIMINAR" para confirmar:',
                                        'method' => 'post',
                                    ],
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar com Ações Rápidas -->
        <div class="col-md-4">
            <!-- Card de Estado -->
            <div class="card card-<?= $model->estado === 'Livre' ? 'success' : ($model->estado === 'EmUso' ? 'danger' : ($model->estado === 'Manutencao' ? 'warning' : 'secondary')) ?>">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar me-2"></i>
                        Estado da Sala
                    </h3>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas <?= $icon[$model->estado] ?? 'fa-question-circle' ?> fa-4x mb-3 text-<?= $model->estado === 'Livre' ? 'success' : ($model->estado === 'EmUso' ? 'danger' : ($model->estado === 'Manutencao' ? 'warning' : 'secondary')) ?>"></i>
                        <h3 class="text-<?= $model->estado === 'Livre' ? 'success' : ($model->estado === 'EmUso' ? 'danger' : ($model->estado === 'Manutencao' ? 'warning' : 'secondary')) ?>">
                            <?= $estadoLabel[$model->estado] ?? 'Desconhecido' ?>
                        </h3>
                    </div>

                    <?php if ($model->estado === 'Livre'): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            Esta sala está disponível para utilização.
                        </div>
                    <?php elseif ($model->estado === 'EmUso'): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-procedures me-2"></i>
                            Esta sala está atualmente em uso.
                        </div>
                    <?php elseif ($model->estado === 'Manutencao'): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-tools me-2"></i>
                            Esta sala está em manutenção.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-secondary">
                            <i class="fas fa-ban me-2"></i>
                            Esta sala está desativada.
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
                        <?= Html::a('<i class="fas fa-door-open me-2"></i> Ver Todas as Salas', ['index'], ['class' => 'btn btn-outline-primary btn-block text-left']) ?>
                        <?= Html::a('<i class="fas fa-plus me-2"></i> Nova Sala', ['create'], ['class' => 'btn btn-outline-success btn-block text-left']) ?>
                        <?= Html::a('<i class="fas fa-building me-2"></i> Ver Bloco', ['bloco/view', 'id' => $model->bloco_id], ['class' => 'btn btn-outline-info btn-block text-left' . (!$model->bloco ? ' disabled' : '')]) ?>
                    </div>
                </div>
            </div>

            <!-- Card de Informações do Bloco -->
            <?php if ($model->bloco): ?>
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-building me-2"></i>
                            Informações do Bloco
                        </h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Nome do Bloco:</span>
                                <strong class="text-success"><?= Html::encode($model->bloco->nome) ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Estado do Bloco:</span>
                                <?php
                                $blocoBadge = [
                                    'ativo' => 'bg-success',
                                    'inativo' => 'bg-secondary',
                                    'manutencao' => 'bg-warning'
                                ];
                                $blocoLabel = [
                                    'ativo' => 'Ativo',
                                    'inativo' => 'Inativo',
                                    'manutencao' => 'Manutenção'
                                ];
                                ?>
                                <span class="badge <?= $blocoBadge[$model->bloco->estado] ?? 'bg-secondary' ?>">
                                <?= $blocoLabel[$model->bloco->estado] ?? 'Desconhecido' ?>
                            </span>
                            </li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- CSS Adicional -->
<style>
    .info-box {
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        border-radius: 0.25rem;
        background: #fff;
        display: flex;
        margin-bottom: 1rem;
        min-height: 80px;
        padding: 0.5rem;
        position: relative;
    }
    .info-box .info-box-icon {
        border-radius: 0.25rem;
        align-items: center;
        display: flex;
        font-size: 1.875rem;
        justify-content: center;
        text-align: center;
        width: 70px;
    }
    .info-box .info-box-content {
        display: flex;
        flex-direction: column;
        justify-content: center;
        line-height: 1.8;
        flex: 1;
        padding: 0 10px;
    }
    .info-box-text {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 0.875rem;
        color: #6c757d;
    }
    .info-box-number {
        display: block;
        font-weight: 700;
        font-size: 1.5rem;
    }
    .list-group-item {
        border: none;
        padding: 0.75rem 0;
    }
    .btn-block {
        text-align: left;
        padding: 0.75rem 1rem;
        margin-bottom: 0.5rem;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Confirmação melhorada para eliminação
        const deleteButtons = document.querySelectorAll('a.btn-danger[data-confirm]');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                const salaName = '<?= addslashes($model->nome) ?>';
                const confirmationMessage = '🚨 ELIMINAÇÃO DE SALA OPERATÓRIA 🚨\n\n' +
                    'Tem a ABSOLUTA certeza que deseja eliminar PERMANENTEMENTE a sala:\n' +
                    '➤ ' + salaName + '\n\n' +
                    '▶️ Esta ação NÃO pode ser desfeita!\n' +
                    '▶️ Todos os dados da sala serão PERDIDOS!\n\n' +
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