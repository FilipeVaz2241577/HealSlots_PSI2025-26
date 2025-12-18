<?php

/** @var yii\web\View $this */
/** @var common\models\Bloco|null $blocoModel */
/** @var common\models\Sala[] $salas */
/** @var string $search */
/** @var string $estadoFiltro */
/** @var array $contagemPorEstado */
/** @var common\models\Bloco[] $todosBlocos */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;



// Mapear cores para estados (usando as constantes do modelo)
$coresEstado = [
        \common\models\Sala::ESTADO_LIVRE => 'success',
        \common\models\Sala::ESTADO_EM_USO => 'danger',
        \common\models\Sala::ESTADO_MANUTENCAO => 'warning',
        \common\models\Sala::ESTADO_DESATIVADA => 'secondary' // Alterado
];

// Calcular estatísticas
$totalSalas = count($salas);
$livres = $contagemPorEstado[\common\models\Sala::ESTADO_LIVRE] ?? 0;
$emUso = $contagemPorEstado[\common\models\Sala::ESTADO_EM_USO] ?? 0;
$manutencao = $contagemPorEstado[\common\models\Sala::ESTADO_MANUTENCAO] ?? 0;
$desativadas = $contagemPorEstado[\common\models\Sala::ESTADO_DESATIVADA] ?? 0; // Alterado
?>

<div class="site-salas">
    <div class="container">
        <div class="card shadow">
            <div class="card-body p-5">
                <!-- Cabeçalho -->
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div>
                        <h1 class="display-5 text-primary mb-2"><?= Html::encode($this->title) ?></h1>
                        <p class="lead text-muted">
                            <?php if ($blocoModel): ?>
                                Gerencie todas as salas do <?= Html::encode($blocoModel->nome) ?>
                            <?php else: ?>
                                Gerencie todas as salas hospitalares
                            <?php endif; ?>
                        </p>
                    </div>
                    <div>
                        <?= Html::a('Voltar aos blocos', ['site/blocos'], ['class' => 'btn btn-outline-secondary']) ?>
                        <?php if (Yii::$app->user->can('createSala')): ?>
                            <?= Html::a('<i class="fas fa-plus me-1"></i> Nova Sala',
                                    ['sala/create', 'bloco_id' => $blocoModel->id ?? null],
                                    ['class' => 'btn btn-primary ms-2']) ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <?php $form = ActiveForm::begin([
                                'method' => 'get',
                                'action' => ['site/salas', 'bloco' => $blocoModel->id ?? null],
                                'options' => ['class' => 'row g-3']
                        ]); ?>

                        <!-- Campo de pesquisa com botão integrado -->
                        <div class="col-md-6">
                            <div class="input-group">
                                <?= Html::textInput('search', $search, [
                                        'class' => 'form-control',
                                        'placeholder' => 'Pesquisar salas por nome...'
                                ]) ?>
                                <button class="btn btn-outline-primary" type="submit" title="Filtrar">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Filtro por bloco -->
                        <div class="col-md-3">
                            <select name="bloco" class="form-select" onchange="this.form.submit()">
                                <option value="">Todos os blocos</option>
                                <?php foreach ($todosBlocos as $bloco): ?>
                                    <option value="<?= $bloco->id ?>" <?= ($blocoModel && $blocoModel->id == $bloco->id) ? 'selected' : '' ?>>
                                        <?= Html::encode($bloco->nome) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Filtro por estado -->
                        <div class="col-md-3">
                            <select name="estado" class="form-select" onchange="this.form.submit()">
                                <option value="">Todos os estados</option>
                                <?php foreach (\common\models\Sala::optsEstado() as $key => $label): ?>
                                    <option value="<?= $key ?>" <?= $estadoFiltro === $key ? 'selected' : '' ?>>
                                        <?= Html::encode($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?php if ($search || $estadoFiltro || $blocoModel): ?>
                            <div class="col-md-12 mt-2">
                                <div class="alert alert-info py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-filter me-2"></i>
                                            <strong>Filtros ativos:</strong>
                                            <?php if ($blocoModel): ?>
                                                <span class="badge bg-primary ms-2">Bloco: <?= Html::encode($blocoModel->nome) ?></span>
                                            <?php endif; ?>
                                            <?php if ($search): ?>
                                                <span class="badge bg-info ms-2">Pesquisa: "<?= Html::encode($search) ?>"</span>
                                            <?php endif; ?>
                                            <?php if ($estadoFiltro): ?>
                                                <span class="badge bg-<?= isset($coresEstado[$estadoFiltro]) ? $coresEstado[$estadoFiltro] : 'secondary' ?> ms-2">
                                    Estado: <?= Html::encode(\common\models\Sala::optsEstado()[$estadoFiltro] ?? $estadoFiltro) ?>
                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <?= Html::a('Remover filtros', ['site/salas'], [
                                                    'class' => 'btn btn-sm btn-outline-secondary'
                                            ]) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>

                <!-- Mensagem se não houver salas -->
                <?php if (empty($salas)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <?php if ($search || $estadoFiltro || $blocoModel): ?>
                            Nenhuma sala encontrada com os filtros atuais.
                            <?= Html::a('Clique aqui para remover os filtros', ['site/salas'], ['class' => 'alert-link']) ?>
                        <?php else: ?>
                            Não existem salas cadastradas.
                            <?php if (Yii::$app->user->can('createSala')): ?>
                                <?= Html::a('Clique aqui para adicionar uma nova sala', ['sala/create'], ['class' => 'alert-link']) ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Lista de Salas -->
                <div class="row">
                    <?php foreach ($salas as $sala): ?>
                        <?php
                        $equipamentosCount = $sala->getEquipamentos()->count();
                        $corBadge = isset($coresEstado[$sala->estado]) ? $coresEstado[$sala->estado] : 'secondary';
                        $estadoTexto = $sala->getEstadoLabel();
                        ?>
                        <div class="col-md-4 mb-4">
                            <div class="room-card h-100">
                                <div class="room-content p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h4 class="text-primary mb-0"><?= Html::encode($sala->nome) ?></h4>
                                        <span class="badge bg-<?= $corBadge ?>">
                                        <?= Html::encode($estadoTexto) ?>
                                    </span>
                                    </div>

                                    <div class="mb-3">
                                        <p class="mb-2">
                                            <i class="fas fa-building text-primary me-2"></i>
                                            <strong><?= Html::encode($sala->blocoName) ?></strong>
                                        </p>
                                        <p class="mb-0">
                                            <i class="fas fa-tools text-primary me-2"></i>
                                            <strong><?= $equipamentosCount ?></strong> equipamento(s)
                                        </p>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mt-4">
                                        <div>
                                            <?= Html::a('Ver Detalhes',
                                                    ['site/detalhe-sala', 'id' => $sala->id],
                                                    ['class' => 'btn btn-outline-primary btn-sm']) ?>
                                        </div>
                                        <div class="text-end">
                                            <?php if (Yii::$app->user->can('updateSala')): ?>
                                                <?= Html::a('<i class="fas fa-edit"></i>',
                                                        ['sala/update', 'id' => $sala->id],
                                                        [
                                                                'class' => 'btn btn-outline-warning btn-sm me-1',
                                                                'title' => 'Editar sala'
                                                        ]) ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Informação adicional -->
                <?php if (!empty($salas)): ?>
                    <div class="alert alert-light mt-4">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3"><i class="fas fa-info-circle me-2"></i>Informações</h6>
                                <ul class="list-unstyled">
                                    <li>
                                        <span class="badge bg-success me-2">&nbsp;</span>
                                        <strong><?= $livres ?></strong> sala(s) disponível(eis) para uso
                                    </li>
                                    <li>
                                        <span class="badge bg-danger me-2">&nbsp;</span>
                                        <strong><?= $emUso ?></strong> sala(s) atualmente em uso
                                    </li>
                                    <li>
                                        <span class="badge bg-warning me-2">&nbsp;</span>
                                        <strong><?= $manutencao ?></strong> sala(s) em manutenção
                                    </li>
                                    <li>
                                        <span class="badge bg-secondary me-2">&nbsp;</span>
                                        <strong><?= $desativadas ?></strong> sala(s) desativadas(s) <!-- Alterado -->
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <?php if ($livres > 0): ?>
                                    <div class="alert alert-success">
                                        <i class="fas fa-thumbs-up me-2"></i>
                                        <strong><?= $totalSalas > 0 ? round(($livres / $totalSalas) * 100) : 0 ?>%</strong> das salas estão disponíveis
                                    </div>
                                <?php endif; ?>

                                <?php if ($manutencao > 0): ?>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong><?= $manutencao ?></strong> sala(s) necessita(m) de atenção
                                    </div>
                                <?php endif; ?>

                                <?php if ($emUso > 0): ?>
                                    <div class="alert alert-primary">
                                        <i class="fas fa-user-injured me-2"></i>
                                        <strong><?= $emUso ?></strong> sala(s) está(ão) atualmente em uso
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    .room-card {
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 0;
        transition: all 0.3s ease;
        overflow: hidden;
        background: white;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        height: 100%;
    }

    .room-card:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        transform: translateY(-5px);
    }

    .room-content {
        padding: 25px;
    }

    .stat-card {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        background: white;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transform: translateY(-3px);
    }

    .badge {
        font-size: 0.75rem;
        padding: 6px 10px;
        border-radius: 20px;
    }

    .btn-outline-primary {
        border-radius: 6px;
        padding: 6px 12px;
        font-size: 0.875rem;
        transition: all 0.3s ease;
    }

    .btn-outline-primary:hover {
        background-color: #007bff;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
    }

    .btn-outline-warning {
        border-radius: 6px;
        padding: 6px 12px;
        font-size: 0.875rem;
        transition: all 0.3s ease;
    }

    .btn-outline-warning:hover {
        background-color: #ffc107;
        color: #212529;
        transform: translateY(-2px);
    }
</style>