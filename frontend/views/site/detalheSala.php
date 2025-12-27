<?php
<<<<<<< HEAD

/** @var yii\web\View $this */
/** @var string $sala */

use yii\bootstrap5\Html;

$this->title = "Detalhes da Sala $sala - Bloco A";
?>

<div class="site-detalhe-sala">
    <div class="container">
        <div class="card shadow">
            <div class="card-body p-5">
                <!-- Cabeçalho -->
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div>
                        <h1 class="display-5 text-primary mb-2"><?= Html::encode($this->title) ?></h1>
                        <p class="lead text-muted">Informações detalhadas da sala</p>
                    </div>
                    <div>
                        <?= Html::a('Voltar às Salas', ['site/salas'], ['class' => 'btn btn-outline-secondary']) ?>
                    </div>
                </div>

                <!-- Detalhes da Sala -->
                <div class="row">
                    <!-- Informações Principais -->
                    <div class="col-md-8">
                        <div class="room-info-card mb-4">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">Informações da Sala</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Nome:</strong> <?= Html::encode($sala) ?></p>
                                            <p><strong>Bloco:</strong> Bloco A</p>
                                            <p><strong>Capacidade:</strong> 30 pessoas</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Estado:</strong>
                                                <span class="text-success"><strong>Disponível</strong></span>
                                            </p>
                                            <p><strong>Tipo:</strong> Sala de Reunião</p>
                                            <p><strong>Área:</strong> 45 m²</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Equipamentos Disponíveis -->
                        <div class="equipment-card mb-4">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Equipamentos da Sala</h5>
                                </div>
                                <div class="card-body">
                                    <div class="equipment-list" id="equipmentList">
                                        <div class="list-group">
                                            <div class="list-group-item equipment-item">
                                                <div class="d-flex w-100 justify-content-between align-items-center">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1">Projetor Epson EB-X41</h6>
                                                        <p class="mb-1 text-muted">Projetor LCD XGA 3.300 lumens</p>
                                                        <small class="text-muted">Nº Série: PRJ2024001</small>
                                                    </div>
                                                    <div class="ms-3">
                                                        <span class="badge bg-success me-2">Disponível</span>
                                                        <button class="btn btn-primary btn-sm add-to-list" data-equipment='{"name":"Projetor Epson EB-X41","serial":"PRJ2024001","status":"Disponível"}'>
                                                            Adicionar à Lista
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="list-group-item equipment-item">
                                                <div class="d-flex w-100 justify-content-between align-items-center">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1">Sistema de Som JBL</h6>
                                                        <p class="mb-1 text-muted">Sistema de áudio 2.1 com 500W</p>
                                                        <small class="text-muted">Nº Série: AUD2024002</small>
                                                    </div>
                                                    <div class="ms-3">
                                                        <span class="badge bg-success me-2">Disponível</span>
                                                        <button class="btn btn-primary btn-sm add-to-list" data-equipment='{"name":"Sistema de Som JBL","serial":"AUD2024002","status":"Disponível"}'>
                                                            Adicionar à Lista
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="list-group-item equipment-item">
                                                <div class="d-flex w-100 justify-content-between align-items-center">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1">Ar Condicionado Daikin</h6>
                                                        <p class="mb-1 text-muted">Split 12.000 BTU</p>
                                                        <small class="text-muted">Nº Série: AC2024003</small>
                                                    </div>
                                                    <div class="ms-3">
                                                        <span class="badge bg-warning me-2">Em Manutenção</span>
                                                        <button class="btn btn-primary btn-sm add-to-list" data-equipment='{"name":"Ar Condicionado Daikin","serial":"AC2024003","status":"Em Manutenção"}'>
                                                            Adicionar à Lista
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="list-group-item equipment-item">
                                                <div class="d-flex w-100 justify-content-between align-items-center">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1">Quadro Interativo Smart</h6>
                                                        <p class="mb-1 text-muted">Quadro 86" touch screen</p>
                                                        <small class="text-muted">Nº Série: QI2024004</small>
                                                    </div>
                                                    <div class="ms-3">
                                                        <span class="badge bg-success me-2">Disponível</span>
                                                        <button class="btn btn-primary btn-sm add-to-list" data-equipment='{"name":"Quadro Interativo Smart","serial":"QI2024004","status":"Disponível"}'>
                                                            Adicionar à Lista
                                                        </button>
                                                    </div>
                                                </div>
=======
/** @var yii\web\View $this */
/** @var common\models\Sala $sala */
/** @var common\models\Equipamento[] $equipamentos */

use yii\bootstrap5\Html;

// PRIMEIRO: Definir $temReservasAtivas ANTES de usá-la
// Buscar reservas ativas do usuário atual para esta sala
$reservasAtivasUsuario = \common\models\Requisicao::find()
        ->where([
                'user_id' => Yii::$app->user->id,
                'sala_id' => $sala->id,
                'status' => \common\models\Requisicao::STATUS_ATIVA
        ])
        ->all();

$temReservasAtivas = !empty($reservasAtivasUsuario);

// DEBUG: Adicionar diretamente na página (remova depois de testar)
if (YII_DEBUG) {
    echo "<!-- DEBUG SALA -->\n";
    echo "<!-- Estado raw: " . $sala->estado . " -->\n";
    echo "<!-- Estado label: " . $sala->getEstadoLabel() . " -->\n";
    echo "<!-- Sala ID: " . $sala->id . " -->\n";
    echo "<!-- Constante EM_USO: " . $sala::ESTADO_EM_USO . " -->\n";
    echo "<!-- isDisponivelParaReserva: " . ($sala->isDisponivelParaReserva() ? 'Sim' : 'Não') . " -->\n";
    echo "<!-- Tem reservas ativas: " . ($temReservasAtivas ? 'Sim' : 'Não') . " -->\n";

    // Debug do método optsEstado()
    $opts = $sala::optsEstado();
    echo "<!-- optsEstado: " . print_r($opts, true) . " -->\n";
    echo "<!-- Estado existe em optsEstado: " . (isset($opts[$sala->estado]) ? 'Sim' : 'Não') . " -->\n";
    if (isset($opts[$sala->estado])) {
        echo "<!-- Valor em optsEstado: " . $opts[$sala->estado] . " -->\n";
    }
}

// ... resto do código permanece igual ...

$this->title = 'Detalhes da Sala: ' . $sala->nome;
$this->params['breadcrumbs'][] = ['label' => 'Blocos', 'url' => ['site/blocos']];
$this->params['breadcrumbs'][] = ['label' => $sala->bloco->nome, 'url' => ['site/salas', 'bloco' => $sala->bloco_id]];
$this->params['breadcrumbs'][] = $this->title;

// CORREÇÃO: Use todos os estados possíveis
$coresEstadoSala = [
        'Livre' => 'success',
        'EmUso' => 'primary',        // ← USAR APENAS 'EmUso' PARA SALAS OCUPADAS
        'Manutencao' => 'warning',
        'Inativa' => 'secondary',
];

// CORREÇÃO: Use as constantes para verificação
$corBadgeSala = isset($coresEstadoSala[$sala->estado]) ?
        $coresEstadoSala[$sala->estado] : 'secondary';

$coresEstadoEquipamento = [
        'Operacional' => 'success',
        'Em Manutenção' => 'warning',
        'Em Uso' => 'danger'
];

// CORREÇÃO: Use o método do modelo
$estadoTextoSala = $sala->getEstadoLabel();

// Verificar se a sala está disponível para reserva
$disponivelParaReserva = $sala->isDisponivelParaReserva();

// Buscar reservas ativas do usuário atual para esta sala
$reservasAtivasUsuario = \common\models\Requisicao::find()
        ->where([
                'user_id' => Yii::$app->user->id,
                'sala_id' => $sala->id,
                'status' => \common\models\Requisicao::STATUS_ATIVA
        ])
        ->all();

$temReservasAtivas = !empty($reservasAtivasUsuario);

// Buscar última atualização
$ultimaAtualizacao = $sala->updated_at ?? time();

// Calcular estatísticas de equipamentos
$operacionais = 0;
$emManutencao = 0;
foreach ($equipamentos as $equipamento) {
    if ($equipamento->estado === 'Operacional') {
        $operacionais++;
    } elseif ($equipamento->estado === 'Em Manutenção') {
        $emManutencao++;
    }
}
?>

    <div class="site-detalhe-sala">
        <div class="container">
            <div class="card shadow">
                <div class="card-body p-5">
                    <!-- Cabeçalho -->
                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <div>
                            <h1 class="display-5 text-primary mb-2">
                                <?= Html::encode($sala->nome) ?>
                                <span class="badge bg-<?= $corBadgeSala ?> fs-6"><?= $estadoTextoSala ?></span>
                            </h1>
                            <p class="lead text-muted">
                                Bloco: <strong><?= Html::encode($sala->bloco->nome) ?></strong> |
                                Equipamentos: <strong><?= count($equipamentos) ?></strong> |
                                Última Atualização: <strong><?= Yii::$app->formatter->asDateTime($ultimaAtualizacao) ?></strong>
                            </p>
                        </div>
                        <div>
                            <?= Html::a('Voltar às Salas', ['site/salas', 'bloco' => $sala->bloco_id], ['class' => 'btn btn-outline-secondary']) ?>
                        </div>
                    </div>

                    <?php if (Yii::$app->session->hasFlash('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?= Yii::$app->session->getFlash('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (Yii::$app->session->hasFlash('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?= Yii::$app->session->getFlash('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!$disponivelParaReserva && $sala->estado !== \common\models\Sala::ESTADO_EM_USO): ?>
                        <div class="alert alert-warning mb-4">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Atenção:</strong> Esta sala não está disponível para reserva no momento. Estado atual: <?= $estadoTextoSala ?>
                        </div>
                    <?php elseif ($sala->estado === \common\models\Sala::ESTADO_EM_USO): ?>
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Informação:</strong> Esta sala está atualmente marcada como <?= $estadoTextoSala ?>.
                            Ainda pode ser possível fazer uma nova reserva se não houver conflitos de horário.
                        </div>
                    <?php endif; ?>

                    <?php if ($temReservasAtivas): ?>
                        <div class="alert alert-success mb-4">
                            <i class="fas fa-calendar-check me-2"></i>
                            <strong>Você tem reservas ativas nesta sala!</strong>
                            <ul class="mb-0 mt-2">
                                <?php foreach ($reservasAtivasUsuario as $reserva): ?>
                                    <li>
                                        Reserva #<?= $reserva->id ?>:
                                        <?= Yii::$app->formatter->asDateTime($reserva->dataInicio) ?>
                                        até <?= Yii::$app->formatter->asDateTime($reserva->dataFim) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <!-- Informações Principais -->
                        <div class="col-md-8">
                            <div class="room-info-card mb-4">
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informações da Sala</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <table class="table table-borderless">
                                                    <tr>
                                                        <td><strong>Nome da Sala:</strong></td>
                                                        <td><?= Html::encode($sala->nome) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Bloco:</strong></td>
                                                        <td><?= Html::encode($sala->bloco->nome) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Equipamentos:</strong></td>
                                                        <td><?= count($equipamentos) ?></td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="col-md-6">
                                                <table class="table table-borderless">
                                                    <tr>
                                                        <td><strong>Estado:</strong></td>
                                                        <td>
                                                        <span class="badge bg-<?= $corBadgeSala ?>">
                                                            <?= $estadoTextoSala ?>
                                                        </span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Disponível para Reserva:</strong></td>
                                                        <td>
                                                            <?php if ($disponivelParaReserva): ?>
                                                                <span class="badge bg-success">Sim</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-danger">Não</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Última Atualização:</strong></td>
                                                        <td><?= Yii::$app->formatter->asDateTime($ultimaAtualizacao) ?></td>
                                                    </tr>
                                                </table>
>>>>>>> origin/filipe
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
<<<<<<< HEAD
                        </div>

                        <!-- Lista de Equipamentos Selecionados -->
                        <div class="selected-equipment-card mb-4">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">Lista de Equipamentos Selecionados</h5>
                                </div>
                                <div class="card-body">
                                    <div id="selectedEquipmentList" class="selected-list">
                                        <p class="text-muted mb-0">Nenhum equipamento selecionado</p>
                                    </div>
                                    <div class="mt-3 d-none" id="listActions">
                                        <?= Html::a('Reservar Equipamentos', ['#'], ['class' => 'btn btn-success me-2']) ?>
                                        <?= Html::a('Limpar Lista', ['#'], ['class' => 'btn btn-outline-danger', 'id' => 'clearList']) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar com Ações Rápidas -->
                    <div class="col-md-4">
                        <div class="action-sidebar">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Ações Rápidas</h6>
                                </div>
                                <div class="card-body">
                                    <?= Html::a('Reservar Sala', ['#'], ['class' => 'btn btn-success w-100 mb-2']) ?>
                                    <?= Html::a('Reportar Problema', ['#'], ['class' => 'btn btn-warning w-100 mb-2']) ?>
                                    <?= Html::a('Solicitar Manutenção', ['#'], ['class' => 'btn btn-danger w-100']) ?>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Estatísticas</h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <h4 class="text-primary">85%</h4>
                                        <small class="text-muted">Taxa de Ocupação</small>
                                    </div>
                                    <div class="text-center mb-3">
                                        <h4 class="text-success">12</h4>
                                        <small class="text-muted">Reservas este Mês</small>
                                    </div>
                                    <div class="text-center">
                                        <h4 class="text-warning">2</h4>
                                        <small class="text-muted">Manutenções Pendentes</small>
=======

                            <!-- Equipamentos da Sala -->
                            <?php if (!empty($equipamentos)): ?>
                                <div class="equipment-card mb-4">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0"><i class="fas fa-tools me-2"></i>Equipamentos da Sala</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                    <tr>
                                                        <th>Equipamento</th>
                                                        <th>Estado</th>
                                                        <th>Número de Série</th>
                                                        <th>Tipo</th>
                                                        <th>Ações</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php foreach ($equipamentos as $equipamento): ?>
                                                        <?php
                                                        $corBadgeEquip = isset($coresEstadoEquipamento[$equipamento->estado]) ?
                                                                $coresEstadoEquipamento[$equipamento->estado] : 'secondary';
                                                        ?>
                                                        <tr>
                                                            <td>
                                                                <strong><?= Html::encode($equipamento->equipamento) ?></strong>
                                                            </td>
                                                            <td>
                                                            <span class="badge bg-<?= $corBadgeEquip ?>">
                                                                <?= Html::encode($equipamento->estado) ?>
                                                            </span>
                                                            </td>
                                                            <td>
                                                            <span class="text-dark font-monospace">
                                                                <?= Html::encode($equipamento->numeroSerie) ?>
                                                            </span>
                                                            </td>
                                                            <td>
                                                                <small class="text-muted">
                                                                    <?= Html::encode($equipamento->tipoEquipamento->nome ?? 'N/A') ?>
                                                                </small>
                                                            </td>
                                                            <td>
                                                                <div class="btn-group btn-group-sm" role="group">
                                                                    <?= Html::a('<i class="fas fa-eye"></i>',
                                                                            ['site/detalhe-equipamento', 'id' => $equipamento->id],
                                                                            [
                                                                                    'class' => 'btn btn-outline-primary',
                                                                                    'title' => 'Ver detalhes'
                                                                            ]) ?>

                                                                    <?php if (Yii::$app->user->can('equipamentoAssign')): ?>
                                                                        <?= Html::a('<i class="fas fa-unlink"></i>',
                                                                                ['site/remove-equipamento', 'sala_id' => $sala->id, 'equipamento_id' => $equipamento->id],
                                                                                [
                                                                                        'class' => 'btn btn-outline-danger',
                                                                                        'title' => 'Remover da sala',
                                                                                        'data' => [
                                                                                                'confirm' => 'Tem certeza que deseja remover este equipamento da sala?',
                                                                                                'method' => 'post',
                                                                                        ]
                                                                                ]) ?>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Esta sala não tem equipamentos atribuídos.
                                    <?php if (Yii::$app->user->can('equipamentoAssign')): ?>
                                        <?= Html::a('Clique aqui para atribuir equipamentos',
                                                ['sala/assign-equipamento', 'id' => $sala->id],
                                                ['class' => 'alert-link']) ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Sidebar com Ações Rápidas -->
                        <div class="col-md-4">
                            <!-- Status Card -->
                            <div class="card mb-4">
                                <div class="card-header bg-<?= $corBadgeSala ?> text-white">
                                    <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Status</h6>
                                </div>
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <?php
                                        $icon = 'question-circle';
                                        if ($sala->estado === 'Livre') {
                                            $icon = 'check-circle';
                                        } elseif ($sala->estado === 'Manutencao') {
                                            $icon = 'tools';
                                        } elseif (in_array($sala->estado, ['EmUso', 'Requisitada'])) {
                                            $icon = 'user-clock';
                                        } elseif ($sala->estado === 'Inativa') {
                                            $icon = 'ban';
                                        }
                                        ?>
                                        <i class="fas fa-<?= $icon ?> fa-3x text-<?= $corBadgeSala ?> mb-3"></i>
                                        <h4><?= $estadoTextoSala ?></h4>
                                    </div>

                                    <?php if ($sala->estado === 'Manutencao'): ?>
                                        <div class="alert alert-warning">
                                            <small>
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                Esta sala está atualmente em manutenção
                                            </small>
                                        </div>
                                    <?php elseif (in_array($sala->estado, ['EmUso', 'Requisitada'])): ?>
                                        <div class="alert alert-primary">
                                            <small>
                                                <i class="fas fa-user-clock me-1"></i>
                                                Esta sala está atualmente em uso ou reservada
                                            </small>
                                        </div>
                                    <?php elseif ($sala->estado === 'Inativa'): ?>
                                        <div class="alert alert-secondary">
                                            <small>
                                                <i class="fas fa-ban me-1"></i>
                                                Esta sala está inativa
                                            </small>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-success">
                                            <small>
                                                <i class="fas fa-check-circle me-1"></i>
                                                Esta sala está disponível para uso
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Estatísticas SIMPLIFICADAS -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Estatísticas</h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <h4 class="text-primary"><?= count($equipamentos) ?></h4>
                                        <small class="text-muted">Total Equipamentos</small>
                                    </div>
                                    <div class="text-center mb-3">
                                        <h4 class="text-success"><?= $operacionais ?></h4>
                                        <small class="text-muted">Equipamentos Operacionais</small>
                                    </div>
                                    <div class="text-center">
                                        <h4 class="text-warning"><?= $emManutencao ?></h4>
                                        <small class="text-muted">Equipamentos em Manutenção</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Ações Rápidas -->
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-cogs me-2"></i>Ações Rápidas</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <?php if (Yii::$app->user->can('updateSala')): ?>
                                            <?= Html::a('<i class="fas fa-edit me-2"></i> Editar Sala',
                                                    ['sala/update', 'id' => $sala->id],
                                                    ['class' => 'btn btn-warning']) ?>
                                        <?php endif; ?>

                                        <!-- BOTÃO RESERVAR SALA -->
                                        <?php if ($disponivelParaReserva): ?>
                                            <?= Html::a('<i class="fas fa-calendar-check me-2"></i> Reservar Sala',
                                                    ['site/reserva', 'id' => $sala->id],
                                                    ['class' => 'btn btn-success']) ?>
                                        <?php else: ?>
                                            <button class="btn btn-success" disabled>
                                                <i class="fas fa-calendar-times me-2"></i> Reservar Sala
                                            </button>
                                            <small class="text-muted text-center">Não disponível para reserva</small>
                                        <?php endif; ?>

                                        <?php if ($sala->estado !== \common\models\Sala::ESTADO_MANUTENCAO): ?>
                                            <?= Html::a('<i class="fas fa-tools me-2"></i> Solicitar Manutenção',
                                                    ['site/solicitar-manutencao-sala', 'id' => $sala->id],
                                                    [
                                                            'class' => 'btn btn-warning',
                                                            'data' => [
                                                                    'confirm' => 'Deseja solicitar manutenção para a sala ' . $sala->nome . '?\n\nA sala será marcada como "Em Manutenção" e não estará disponível para reservas.',
                                                                    'method' => 'post',
                                                            ]
                                                    ]) ?>
                                        <?php else: ?>
                                            <button class="btn btn-secondary" disabled>
                                                <i class="fas fa-tools me-2"></i> Já em Manutenção
                                            </button>
                                        <?php endif; ?>

                                        <?= Html::a('<i class="fas fa-exclamation-triangle me-1"></i> Reportar Problema',
                                                ['site/suporte', 'assunto' => 'Problema com a Sala: ' . $sala->nome],
                                                ['class' => 'btn btn-danger w-100 mb-2']) ?>

                                        <!-- BOTÃO CANCELAR RESERVA -->
                                        <?php if ($temReservasAtivas): ?>
                                            <?= Html::a('<i class="fas fa-calendar-times me-2"></i> Cancelar Reserva',
                                                    ['site/cancelar-reserva', 'id' => $sala->id],
                                                    [
                                                            'class' => 'btn btn-danger',
                                                            'data' => [
                                                                    'confirm' => 'Tem certeza que deseja cancelar sua(s) reserva(s) ativa(s) nesta sala? ' .
                                                                            'Os equipamentos serão devolvidos ao estado operacional.',
                                                                    'method' => 'post',
                                                            ]
                                                    ]) ?>
                                            <small class="text-muted text-center">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Cancelar todas as suas reservas ativas nesta sala
                                            </small>
                                        <?php endif; ?>

                                        <?php if (Yii::$app->user->can('equipamentoAssign')): ?>
                                            <?= Html::a('<i class="fas fa-link me-2"></i> Atribuir Equipamentos',
                                                    ['sala/assign-equipamento', 'id' => $sala->id],
                                                    ['class' => 'btn btn-info']) ?>
                                        <?php endif; ?>

                                        <!-- NOVO BOTÃO: REMOVER TODOS OS EQUIPAMENTOS -->
                                        <?php if (Yii::$app->user->can('equipamentoAssign') && !empty($equipamentos)): ?>
                                            <?= Html::a('<i class="fas fa-unlink me-2"></i> Remover Todos Equipamentos',
                                                    ['site/remove-all-equipamentos', 'id' => $sala->id],
                                                    [
                                                            'class' => 'btn btn-outline-danger',
                                                            'data' => [
                                                                    'confirm' => 'Tem certeza que deseja remover TODOS os equipamentos desta sala?',
                                                                    'method' => 'post',
                                                            ]
                                                    ]) ?>
                                            <small class="text-muted text-center">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                Remove todos os equipamentos da sala
                                            </small>
                                        <?php endif; ?>

                                        <?php if (Yii::$app->user->can('deleteSala')): ?>
                                            <?= Html::a('<i class="fas fa-trash me-2"></i> Eliminar Sala',
                                                    ['sala/delete', 'id' => $sala->id],
                                                    [
                                                            'class' => 'btn btn-outline-danger',
                                                            'data' => [
                                                                    'confirm' => 'Tem certeza que deseja eliminar esta sala?',
                                                                    'method' => 'post',
                                                            ]
                                                    ]) ?>
                                        <?php endif; ?>
>>>>>>> origin/filipe
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<<<<<<< HEAD
</div>

<style>
    .room-info-card .card-header {
        background: linear-gradient(135deg, #007bff, #0056b3);
    }

    .equipment-item {
        border: none;
        border-left: 4px solid #007bff;
        margin-bottom: 8px;
        border-radius: 4px;
        transition: all 0.3s ease;
    }

    .equipment-item:hover {
        background-color: #f8f9fa;
        border-left-color: #0056b3;
    }

    .selected-equipment-card .card-header {
        background: linear-gradient(135deg, #17a2b8, #138496);
    }

    .selected-equipment-item {
        border: none;
        border-left: 4px solid #17a2b8;
        margin-bottom: 8px;
        border-radius: 4px;
        background-color: #f8f9fa;
    }

    .action-sidebar .btn {
        border-radius: 6px;
        padding: 10px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .action-sidebar .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .add-to-list {
        transition: all 0.3s ease;
    }

    .add-to-list:hover {
        transform: translateY(-1px);
    }

    .remove-from-list {
        transition: all 0.3s ease;
    }

    .remove-from-list:hover {
        transform: scale(1.1);
    }

    .text-success { color: #28a745 !important; }
    .text-warning { color: #ffc107 !important; }
    .text-danger { color: #dc3545 !important; }
    .text-info { color: #17a2b8 !important; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectedEquipmentList = document.getElementById('selectedEquipmentList');
        const listActions = document.getElementById('listActions');
        const clearListBtn = document.getElementById('clearList');
        let selectedEquipment = [];

        // Função para adicionar equipamento à lista
        function addEquipmentToSelected(equipmentData) {
            // Verificar se o equipamento já está na lista
            if (selectedEquipment.some(eq => eq.serial === equipmentData.serial)) {
                alert('Este equipamento já está na lista!');
                return;
            }

            // Adicionar à lista
            selectedEquipment.push(equipmentData);
            updateSelectedEquipmentList();
        }

        // Função para remover equipamento da lista
        function removeEquipmentFromSelected(serial) {
            selectedEquipment = selectedEquipment.filter(eq => eq.serial !== serial);
            updateSelectedEquipmentList();
        }

        // Função para atualizar a lista visual
        function updateSelectedEquipmentList() {
            if (selectedEquipment.length === 0) {
                selectedEquipmentList.innerHTML = '<p class="text-muted mb-0">Nenhum equipamento selecionado</p>';
                listActions.classList.add('d-none');
            } else {
                selectedEquipmentList.innerHTML = selectedEquipment.map(equipment => `
                    <div class="selected-equipment-item p-3">
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">${equipment.name}</h6>
                                <p class="mb-1 text-muted">Nº Série: ${equipment.serial}</p>
                                <span class="badge ${equipment.status === 'Disponível' ? 'bg-success' : 'bg-warning'}">${equipment.status}</span>
                            </div>
                            <button class="btn btn-outline-danger btn-sm remove-from-list ms-3" data-serial="${equipment.serial}">
                                Remover
                            </button>
                        </div>
                    </div>
                `).join('');
                listActions.classList.remove('d-none');
            }
        }

        // Event listeners para os botões "Adicionar à Lista"
        document.querySelectorAll('.add-to-list').forEach(button => {
            button.addEventListener('click', function() {
                const equipmentData = JSON.parse(this.getAttribute('data-equipment'));
                addEquipmentToSelected(equipmentData);
            });
        });

        // Event listener para remover equipamentos (usando event delegation)
        selectedEquipmentList.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-from-list')) {
                const serial = e.target.getAttribute('data-serial');
                removeEquipmentFromSelected(serial);
            }
        });

        // Event listener para limpar a lista
        clearListBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Tem a certeza que deseja limpar a lista de equipamentos?')) {
                selectedEquipment = [];
                updateSelectedEquipmentList();
            }
        });

        // Event listener para mostrar detalhes do equipamento ao clicar no item
        document.querySelectorAll('.equipment-item').forEach(item => {
            item.addEventListener('click', function(e) {
                // Só mostra detalhes se não clicar no botão
                if (!e.target.classList.contains('add-to-list') && !e.target.closest('.add-to-list')) {
                    const equipmentName = this.querySelector('h6').textContent;
                    const status = this.querySelector('.badge').textContent;
                    const serial = this.querySelector('small').textContent.replace('Nº Série: ', '');

                    alert(`Equipamento: ${equipmentName}\nNº Série: ${serial}\nStatus: ${status}`);
                }
            });
        });
    });
</script>
=======

    <style>
        .room-info-card .card-header {
            background: linear-gradient(135deg, #007bff, #0056b3);
        }

        .action-sidebar .btn {
            border-radius: 6px;
            padding: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-bottom: 8px;
        }

        .action-sidebar .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }

        .font-monospace {
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            color: #212529 !important;
            background-color: transparent !important;
            padding: 0 !important;
        }

        .badge {
            font-size: 0.75rem;
            padding: 6px 10px;
            border-radius: 20px;
        }

        .btn-group .btn {
            border-radius: 4px !important;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .text-muted small {
            font-size: 0.8rem;
            display: block;
            margin-top: 4px;
        }

        .alert {
            border-radius: 8px;
        }
    </style>

<?php
$js = <<<JS
$(document).ready(function() {
    // Recarregar a página após 2 segundos se houver uma mensagem de sucesso
    if ($('.alert-success').length > 0) {
        setTimeout(function() {
            location.reload();
        }, 2000);
    }
    
    // Confirmar antes de cancelar reserva
    $('.btn-danger[href*="cancelar-reserva"]').click(function(e) {
        if (!confirm('Tem certeza que deseja cancelar todas as suas reservas ativas nesta sala?\n\nEsta ação devolverá a sala ao estado livre e os equipamentos ao estado operacional.')) {
            e.preventDefault();
            return false;
        }
    });
});
JS;
$this->registerJs($js);
?>
>>>>>>> origin/filipe
