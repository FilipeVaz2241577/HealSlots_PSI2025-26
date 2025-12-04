<?php

/** @var yii\web\View $this */
/** @var string $categoria */
/** @var common\models\TipoEquipamento $tipoEquipamento */
/** @var common\models\Equipamento[] $equipamentos */
/** @var array $contagemPorEstado */
/** @var string $search */
/** @var string $estadoFiltro */
/** @var string $sort */
/** @var string $order */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

// Mapear tipos para títulos
$titulosCategorias = [
        'monitorizacao' => 'Equipamentos de Monitorização',
        'moveis' => 'Equipamentos Móveis',
        'cirurgicos' => 'Instrumentos Cirúrgicos',
        'consumo' => 'Materiais de Consumo'
];

// Se tiver $tipoEquipamento, usar o nome dele
if (isset($tipoEquipamento)) {
    $titulo = $tipoEquipamento->nome;
} else {
    $titulo = $titulosCategorias[$categoria] ?? 'Equipamentos';
}

$this->title = $titulo;

// CORREÇÃO: Mapear estados para cores de badge - "Em Uso" deve ser vermelho
$coresEstado = [
        'Operacional' => 'success',      // Verde
        'Em Manutenção' => 'warning',    // Amarelo
        'Em Uso' => 'danger'             // Vermelho (CORRIGIDO)
];

// DEBUG: Verificar o que está vindo do controlador
// Adicione este código temporariamente para verificar




// CORREÇÃO: Calcular estatísticas totais a partir de $contagemPorEstado
// NOTA: $contagemPorEstado já vem do controlador com as contagens totais
$disponiveis = isset($contagemPorEstado['Operacional']) ? (int) $contagemPorEstado['Operacional'] : 0;
$manutencao = isset($contagemPorEstado['Em Manutenção']) ? (int) $contagemPorEstado['Em Manutenção'] : 0;
$emUso = isset($contagemPorEstado['Em Uso']) ? (int) $contagemPorEstado['Em Uso'] : 0;

// CORREÇÃO: Calcular o total corretamente
$totalEquipamentos = $disponiveis + $manutencao + $emUso;

// Contar equipamentos filtrados (para mostrar na tabela)
$totalFiltrados = (int) count($equipamentos);

// Calcular estatísticas DOS FILTRADOS (apenas para referência)
$disponiveisFiltrados = 0;
$manutencaoFiltrados = 0;
$emUsoFiltrados = 0;

foreach ($equipamentos as $equipamento) {
    switch ($equipamento->estado) {
        case 'Operacional':
            $disponiveisFiltrados++;
            break;
        case 'Em Manutenção':
            $manutencaoFiltrados++;
            break;
        case 'Em Uso':
            $emUsoFiltrados++;
            break;
    }
}

// Evitar divisão por zero
$percentagemOperacionais = 0;
if ($totalEquipamentos > 0 && $disponiveis > 0) {
    $percentagemOperacionais = round(($disponiveis / $totalEquipamentos) * 100);
}

// Função para gerar URL de ordenação
function getSortUrl($column, $currentSort, $currentOrder)
{
    $newOrder = ($currentSort === $column && $currentOrder === 'asc') ? 'desc' : 'asc';
    return \yii\helpers\Url::current(['sort' => $column, 'order' => $newOrder]);
}

// Função para obter ícone de ordenação
function getSortIcon($column, $currentSort, $currentOrder)
{
    if ($currentSort !== $column) {
        return '<i class="fas fa-sort text-muted"></i>';
    }

    return $currentOrder === 'asc'
            ? '<i class="fas fa-sort-up text-primary"></i>'
            : '<i class="fas fa-sort-down text-primary"></i>';
}
?>

    <div class="site-equipamentos">
        <div class="container">
            <div class="card shadow">
                <div class="card-body p-5">
                    <!-- Cabeçalho -->
                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <div>
                            <h1 class="display-5 text-primary mb-2"><?= Html::encode($this->title) ?></h1>
                            <p class="lead text-muted">
                                <?php if ($tipoEquipamento): ?>
                                    Lista de equipamentos do tipo: <strong><?= Html::encode($tipoEquipamento->nome) ?></strong>
                                <?php else: ?>
                                    Lista detalhada de todos os equipamentos
                                <?php endif; ?>
                            </p>
                        </div>
                        <div>
                            <?= Html::a('Voltar', ['site/tiposequipamento'], ['class' => 'btn btn-outline-secondary']) ?>
                            <?php if (Yii::$app->user->can('createEquipment')): ?>
                                <?= Html::a('<i class="fas fa-plus me-1"></i> Novo Equipamento', ['equipamento/create', 'tipo' => $tipoEquipamento->id ?? null], ['class' => 'btn btn-primary ms-2']) ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <?php $form = ActiveForm::begin([
                                    'method' => 'get',
                                    'action' => ['site/equipamentos', 'tipo' => $tipoEquipamento->id ?? null],
                                    'options' => ['class' => 'row g-3']
                            ]); ?>

                            <!-- Campo de pesquisa -->
                            <div class="col-md-6">
                                <div class="input-group">
                                    <?= Html::textInput('search', $search, [
                                            'class' => 'form-control',
                                            'placeholder' => 'Pesquisar equipamentos por nome ou número de série...'
                                    ]) ?>
                                    <button class="btn btn-outline-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <?php if ($search || $estadoFiltro): ?>
                                        <?= Html::a('Limpar', ['site/equipamentos', 'tipo' => $tipoEquipamento->id ?? null], [
                                                'class' => 'btn btn-outline-secondary'
                                        ]) ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Filtro por estado -->
                            <div class="col-md-3">
                                <select name="estado" class="form-select" onchange="this.form.submit()">
                                    <option value="">Todos os estados</option>
                                    <option value="Operacional" <?= $estadoFiltro === 'Operacional' ? 'selected' : '' ?>>Operacional</option>
                                    <option value="Em Manutenção" <?= $estadoFiltro === 'Em Manutenção' ? 'selected' : '' ?>>Em Manutenção</option>
                                    <option value="Em Uso" <?= $estadoFiltro === 'Em Uso' ? 'selected' : '' ?>>Em Uso</option>
                                </select>
                            </div>

                            <!-- Filtro por ordenação -->
                            <div class="col-md-3">
                                <select name="sort" class="form-select" onchange="this.form.submit()">
                                    <option value="equipamento" <?= $sort === 'equipamento' ? 'selected' : '' ?>>Ordenar por: Nome</option>
                                    <option value="estado" <?= $sort === 'estado' ? 'selected' : '' ?>>Ordenar por: Estado</option>
                                    <option value="numeroSerie" <?= $sort === 'numeroSerie' ? 'selected' : '' ?>>Ordenar por: Nº Série</option>
                                </select>
                            </div>

                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>

                    <!-- Mensagens de filtro ativo -->
                    <?php if ($search || $estadoFiltro): ?>
                        <div class="alert alert-info mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-filter me-2"></i>
                                    <strong>Filtros ativos:</strong>
                                    <?php if ($search): ?>
                                        <span class="badge bg-primary ms-2">Pesquisa: "<?= Html::encode($search) ?>"</span>
                                    <?php endif; ?>
                                    <?php if ($estadoFiltro): ?>
                                        <span class="badge bg-<?= isset($coresEstado[$estadoFiltro]) ? $coresEstado[$estadoFiltro] : 'secondary' ?> ms-2">
                                        Estado: <?= Html::encode($estadoFiltro) ?>
                                    </span>
                                    <?php endif; ?>
                                    <!-- Mostrar contagem de resultados filtrados -->
                                    <span class="badge bg-info ms-2">
                                    <?= $totalFiltrados ?> resultado(s) encontrado(s)
                                </span>
                                </div>
                                <div>
                                    <?= Html::a('Remover filtros', ['site/equipamentos', 'tipo' => $tipoEquipamento->id ?? null], [
                                            'class' => 'btn btn-sm btn-outline-secondary'
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Mensagem se não houver equipamentos -->
                    <?php if (empty($equipamentos)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <?php if ($search || $estadoFiltro): ?>
                                Nenhum equipamento encontrado com os filtros atuais.
                                <?= Html::a('Clique aqui para remover os filtros', ['site/equipamentos', 'tipo' => $tipoEquipamento->id ?? null], ['class' => 'alert-link']) ?>
                            <?php else: ?>
                                Não existem equipamentos cadastrados para esta categoria.
                                <?php if (Yii::$app->user->can('createEquipment')): ?>
                                    <?= Html::a('Clique aqui para adicionar um novo equipamento', ['equipamento/create', 'tipo' => $tipoEquipamento->id ?? null], ['class' => 'alert-link']) ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Tabela de Equipamentos -->
                    <?php if (!empty($equipamentos)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="table-primary">
                                <tr>
                                    <th>
                                        <a href="<?= getSortUrl('equipamento', $sort, $order) ?>" class="text-decoration-none text-dark">
                                            Nome do Equipamento <?= getSortIcon('equipamento', $sort, $order) ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="<?= getSortUrl('estado', $sort, $order) ?>" class="text-decoration-none text-dark">
                                            Estado <?= getSortIcon('estado', $sort, $order) ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="<?= getSortUrl('numeroSerie', $sort, $order) ?>" class="text-decoration-none text-dark">
                                            Número de Série <?= getSortIcon('numeroSerie', $sort, $order) ?>
                                        </a>
                                    </th>
                                    <th>Tipo</th>
                                    <th>Localização</th>
                                    <th>Ações</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($equipamentos as $equipamento): ?>
                                    <?php
                                    // Obter salas do equipamento
                                    $salas = $equipamento->salas;
                                    $localizacao = 'Não atribuído';

                                    if (!empty($salas)) {
                                        $nomesSalas = [];
                                        foreach ($salas as $sala) {
                                            $nomesSalas[] = $sala->nome;
                                        }
                                        $localizacao = implode(', ', $nomesSalas);
                                    }

                                    // Usar o mapeamento correto de cores
                                    $corBadge = isset($coresEstado[$equipamento->estado]) ? $coresEstado[$equipamento->estado] : 'secondary';
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?= Html::encode($equipamento->equipamento) ?></strong>
                                        </td>
                                        <td>
                                        <span class="badge bg-<?= $corBadge ?>">
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
                                            <small><?= Html::encode($localizacao) ?></small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <?= Html::a('<i class="fas fa-eye"></i>',
                                                        ['site/detalhe-equipamento', 'id' => $equipamento->id],
                                                        [
                                                                'class' => 'btn btn-outline-primary',
                                                                'title' => 'Ver detalhes'
                                                        ]) ?>

                                                <?php if (Yii::$app->user->can('updateEquipment')): ?>
                                                    <?= Html::a('<i class="fas fa-edit"></i>',
                                                            ['equipamento/update', 'id' => $equipamento->id],
                                                            [
                                                                    'class' => 'btn btn-outline-warning',
                                                                    'title' => 'Editar'
                                                            ]) ?>
                                                <?php endif; ?>

                                                <?php if (Yii::$app->user->can('deleteEquipment')): ?>
                                                    <?= Html::a('<i class="fas fa-trash"></i>',
                                                            ['equipamento/delete', 'id' => $equipamento->id],
                                                            [
                                                                    'class' => 'btn btn-outline-danger',
                                                                    'title' => 'Eliminar',
                                                                    'data' => [
                                                                            'confirm' => 'Tem certeza que deseja eliminar este equipamento?',
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

                        <!-- Contador de resultados -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <?php if (count($equipamentos) > 10): ?>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-chevron-left"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm active">1</button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm">2</button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm">3</button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-chevron-right"></i>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Estatísticas - COM DADOS REAIS DE $contagemPorEstado -->
                    <div class="row mt-5">
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card text-center p-3 bg-primary text-white rounded-3">
                                <h3 class="mb-1"><?= $totalEquipamentos ?></h3>
                                <p class="mb-0">Total Equipamentos</p>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card text-center p-3 bg-success text-white rounded-3">
                                <h3 class="mb-1"><?= $disponiveis ?></h3>
                                <p class="mb-0">Operacionais</p>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card text-center p-3 bg-danger text-white rounded-3">
                                <h3 class="mb-1"><?= $emUso ?></h3>
                                <p class="mb-0">Em Uso</p>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card text-center p-3 bg-warning text-white rounded-3">
                                <h3 class="mb-1"><?= $manutencao ?></h3>
                                <p class="mb-0">Em Manutenção</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .table th {
            border-top: none;
            font-weight: 600;
            color: #495057;
            background-color: #f8f9fa;
        }

        .table th a:hover {
            color: #007bff !important;
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

        .btn-group .btn {
            border-radius: 4px !important;
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
            transform: translateY(-1px);
        }

        .font-monospace {
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            color: #212529 !important;
            background-color: transparent !important;
            padding: 0 !important;
        }
    </style>

<?php
// Adicionar CSS para a tabela
$this->registerCss(<<<CSS
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
    
    .table th {
        position: relative;
    }
    
    .table th a {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .table th i {
        font-size: 0.9em;
    }
    
    /* Cores dos badges */
    .bg-success {
        background-color: #28a745 !important;
    }
    
    .bg-warning {
        background-color: #ffc107 !important;
        color: #212529 !important;
    }
    
    .bg-danger {
        background-color: #dc3545 !important;
    }
    
    .bg-primary {
        background-color: #007bff !important;
    }
    
    /* Melhorar visibilidade dos badges */
    .badge.bg-warning {
        font-weight: 500;
    }
    
    td .font-monospace {
        color: #212529 !important;
        font-weight: 500;
    }
CSS);