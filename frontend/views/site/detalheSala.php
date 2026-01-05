<?php
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
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

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
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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