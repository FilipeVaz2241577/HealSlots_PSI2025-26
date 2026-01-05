<?php

/** @var yii\web\View $this - Objeto da view do Yii */
/** @var string $categoria - Categoria do equipamento (monitorizacao, moveis, etc.) */
/** @var common\models\TipoEquipamento $tipoEquipamento - Tipo de equipamento (se aplicável) */
/** @var common\models\Equipamento[] $equipamentos - Array de equipamentos a exibir */
/** @var array $contagemPorEstado - Contagem de equipamentos por estado */
/** @var string $search - Termo de pesquisa atual */
/** @var string $estadoFiltro - Filtro de estado atual */
/** @var string $sort - Coluna de ordenação atual */
/** @var string $order - Direção de ordenação (asc/desc) */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

// Mapear tipos de equipamento para títulos amigáveis
$titulosCategorias = [
        'monitorizacao' => 'Equipamentos de Monitorização',
        'moveis' => 'Equipamentos Móveis',
        'cirurgicos' => 'Instrumentos Cirúrgicos',
        'consumo' => 'Materiais de Consumo'
];

// Determinar o título da página
if (isset($tipoEquipamento)) {
    $titulo = $tipoEquipamento->nome; // Usar nome do tipo de equipamento se disponível
} else {
    $titulo = $titulosCategorias[$categoria] ?? 'Equipamentos'; // Fallback para título genérico
}

$this->title = $titulo; // Define o título da página

// CORREÇÃO: Mapear estados para cores de badge - "Em Uso" deve ser vermelho
$coresEstado = [
        'Operacional' => 'success',      // Verde para equipamentos operacionais
        'Em Manutenção' => 'warning',    // Amarelo para equipamentos em manutenção
        'Em Uso' => 'danger'             // Vermelho para equipamentos em uso (CORRIGIDO)
];

// CORREÇÃO: Calcular estatísticas totais a partir de $contagemPorEstado
// NOTA: $contagemPorEstado já vem do controlador com as contagens totais
$disponiveis = isset($contagemPorEstado['Operacional']) ? (int) $contagemPorEstado['Operacional'] : 0;
$manutencao = isset($contagemPorEstado['Em Manutenção']) ? (int) $contagemPorEstado['Em Manutenção'] : 0;
$emUso = isset($contagemPorEstado['Em Uso']) ? (int) $contagemPorEstado['Em Uso'] : 0;

// CORREÇÃO: Calcular o total corretamente somando todos os estados
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

// Calcular percentagem de equipamentos operacionais (evitar divisão por zero)
$percentagemOperacionais = 0;
if ($totalEquipamentos > 0 && $disponiveis > 0) {
    $percentagemOperacionais = round(($disponiveis / $totalEquipamentos) * 100);
}

/**
 * Função para gerar URL de ordenação
 * @param string $column - Coluna para ordenar
 * @param string $currentSort - Ordenação atual
 * @param string $currentOrder - Direção atual (asc/desc)
 * @return string - URL para ordenação
 */
function getSortUrl($column, $currentSort, $currentOrder)
{
    // Alterna entre asc e desc quando clica na mesma coluna
    $newOrder = ($currentSort === $column && $currentOrder === 'asc') ? 'desc' : 'asc';
    return \yii\helpers\Url::current(['sort' => $column, 'order' => $newOrder]);
}

/**
 * Função para obter ícone de ordenação
 * @param string $column - Coluna sendo ordenada
 * @param string $currentSort - Ordenação atual
 * @param string $currentOrder - Direção atual (asc/desc)
 * @return string - HTML do ícone de ordenação
 */
function getSortIcon($column, $currentSort, $currentOrder)
{
    // Se não está a ordenar por esta coluna, mostrar ícone neutro
    if ($currentSort !== $column) {
        return '<i class="fas fa-sort text-muted"></i>';
    }

    // Mostrar seta para cima ou para baixo baseado na direção
    return $currentOrder === 'asc'
            ? '<i class="fas fa-sort-up text-primary"></i>'
            : '<i class="fas fa-sort-down text-primary"></i>';
}
?>

    <div class="site-equipamentos">
        <div class="container">
            <div class="card shadow">
                <div class="card-body p-5">
                    <!-- Cabeçalho da página -->
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
                            <!-- Botão para voltar à página de tipos de equipamento -->
                            <?= Html::a('Voltar', ['site/tiposequipamento'], ['class' => 'btn btn-outline-secondary']) ?>
                            <!-- Botão para criar novo equipamento (apenas com permissão) -->
                            <?php if (Yii::$app->user->can('createEquipment')): ?>
                                <?= Html::a('<i class="fas fa-plus me-1"></i> Novo Equipamento',
                                        ['equipamento/create', 'tipo' => $tipoEquipamento->id ?? null],
                                        ['class' => 'btn btn-primary ms-2']) ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Secção de filtros -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <?php $form = ActiveForm::begin([
                                    'method' => 'get', // Formulário GET para filtros
                                    'action' => ['site/equipamentos', 'tipo' => $tipoEquipamento->id ?? null],
                                    'options' => ['class' => 'row g-3'] // Sistema de grid Bootstrap
                            ]); ?>

                            <!-- Campo de pesquisa por nome ou número de série -->
                            <div class="col-md-6">
                                <div class="input-group">
                                    <?= Html::textInput('search', $search, [
                                            'class' => 'form-control',
                                            'placeholder' => 'Pesquisar equipamentos por nome ou número de série...'
                                    ]) ?>
                                    <button class="btn btn-outline-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <!-- Botão para limpar filtros (se ativos) -->
                                    <?php if ($search || $estadoFiltro): ?>
                                        <?= Html::a('Limpar', ['site/equipamentos', 'tipo' => $tipoEquipamento->id ?? null], [
                                                'class' => 'btn btn-outline-secondary'
                                        ]) ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Filtro por estado do equipamento -->
                            <div class="col-md-3">
                                <select name="estado" class="form-select" onchange="this.form.submit()">
                                    <option value="">Todos os estados</option>
                                    <option value="Operacional" <?= $estadoFiltro === 'Operacional' ? 'selected' : '' ?>>Operacional</option>
                                    <option value="Em Manutenção" <?= $estadoFiltro === 'Em Manutenção' ? 'selected' : '' ?>>Em Manutenção</option>
                                    <option value="Em Uso" <?= $estadoFiltro === 'Em Uso' ? 'selected' : '' ?>>Em Uso</option>
                                </select>
                            </div>

                            <!-- Filtro por ordenação dos resultados -->
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
                                    <!-- Badge para pesquisa ativa -->
                                    <?php if ($search): ?>
                                        <span class="badge bg-primary ms-2">Pesquisa: "<?= Html::encode($search) ?>"</span>
                                    <?php endif; ?>
                                    <!-- Badge para filtro de estado ativo -->
                                    <?php if ($estadoFiltro): ?>
                                        <span class="badge bg-<?= isset($coresEstado[$estadoFiltro]) ? $coresEstado[$estadoFiltro] : 'secondary' ?> ms-2">
                                        Estado: <?= Html::encode($estadoFiltro) ?>
                                    </span>
                                    <?php endif; ?>
                                    <!-- Contagem de resultados filtrados -->
                                    <span class="badge bg-info ms-2">
                                    <?= $totalFiltrados ?> resultado(s) encontrado(s)
                                </span>
                                </div>
                                <div>
                                    <!-- Botão para remover todos os filtros -->
                                    <?= Html::a('Remover filtros', ['site/equipamentos', 'tipo' => $tipoEquipamento->id ?? null], [
                                            'class' => 'btn btn-sm btn-outline-secondary'
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Mensagem quando não há equipamentos -->
                    <?php if (empty($equipamentos)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <?php if ($search || $estadoFiltro): ?>
                                Nenhum equipamento encontrado com os filtros atuais.
                                <?= Html::a('Clique aqui para remover os filtros', ['site/equipamentos', 'tipo' => $tipoEquipamento->id ?? null], ['class' => 'alert-link']) ?>
                            <?php else: ?>
                                Não existem equipamentos cadastrados para esta categoria.
                                <!-- Link para criar novo equipamento (apenas com permissão) -->
                                <?php if (Yii::$app->user->can('createEquipment')): ?>
                                    <?= Html::a('Clique aqui para adicionar um novo equipamento',
                                            ['equipamento/create', 'tipo' => $tipoEquipamento->id ?? null],
                                            ['class' => 'alert-link']) ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Tabela de Equipamentos (apenas se houver equipamentos) -->
                    <?php if (!empty($equipamentos)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="table-primary">
                                <tr>
                                    <!-- Cabeçalho com ordenação por nome -->
                                    <th>
                                        <a href="<?= getSortUrl('equipamento', $sort, $order) ?>" class="text-decoration-none text-dark">
                                            Nome do Equipamento <?= getSortIcon('equipamento', $sort, $order) ?>
                                        </a>
                                    </th>
                                    <!-- Cabeçalho com ordenação por estado -->
                                    <th>
                                        <a href="<?= getSortUrl('estado', $sort, $order) ?>" class="text-decoration-none text-dark">
                                            Estado <?= getSortIcon('estado', $sort, $order) ?>
                                        </a>
                                    </th>
                                    <!-- Cabeçalho com ordenação por número de série -->
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
                                    // Obter salas onde o equipamento está localizado
                                    $salas = $equipamento->salas;
                                    $localizacao = 'Não atribuído';

                                    if (!empty($salas)) {
                                        $nomesSalas = [];
                                        foreach ($salas as $sala) {
                                            $nomesSalas[] = $sala->nome;
                                        }
                                        $localizacao = implode(', ', $nomesSalas);
                                    }

                                    // Determinar cor do badge baseado no estado do equipamento
                                    $corBadge = isset($coresEstado[$equipamento->estado]) ? $coresEstado[$equipamento->estado] : 'secondary';
                                    ?>
                                    <tr>
                                        <!-- Nome do equipamento -->
                                        <td>
                                            <strong><?= Html::encode($equipamento->equipamento) ?></strong>
                                        </td>
                                        <!-- Estado com badge colorido -->
                                        <td>
                                        <span class="badge bg-<?= $corBadge ?>">
                                            <?= Html::encode($equipamento->estado) ?>
                                        </span>
                                        </td>
                                        <!-- Número de série com fonte monoespaçada -->
                                        <td>
                                        <span class="text-dark font-monospace">
                                            <?= Html::encode($equipamento->numeroSerie) ?>
                                        </span>
                                        </td>
                                        <!-- Tipo de equipamento -->
                                        <td>
                                            <small class="text-muted">
                                                <?= Html::encode($equipamento->tipoEquipamento->nome ?? 'N/A') ?>
                                            </small>
                                        </td>
                                        <!-- Localização (sala) do equipamento -->
                                        <td>
                                            <small><?= Html::encode($localizacao) ?></small>
                                        </td>
                                        <!-- Botões de ação -->
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <!-- Botão para ver detalhes -->
                                                <?= Html::a('<i class="fas fa-eye"></i>',
                                                        ['site/detalhe-equipamento', 'id' => $equipamento->id],
                                                        [
                                                                'class' => 'btn btn-outline-primary',
                                                                'title' => 'Ver detalhes'
                                                        ]) ?>

                                                <!-- Botão para editar (apenas com permissão) -->
                                                <?php if (Yii::$app->user->can('updateEquipment')): ?>
                                                    <?= Html::a('<i class="fas fa-edit"></i>',
                                                            ['equipamento/update', 'id' => $equipamento->id],
                                                            [
                                                                    'class' => 'btn btn-outline-warning',
                                                                    'title' => 'Editar'
                                                            ]) ?>
                                                <?php endif; ?>

                                                <!-- Botão para eliminar (apenas com permissão) -->
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

                        <!-- Contador de resultados e paginação (se necessária) -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <!-- Paginação simples (se mais de 10 resultados) -->
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
                        <!-- Total de equipamentos -->
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card text-center p-3 bg-primary text-white rounded-3">
                                <h3 class="mb-1"><?= $totalEquipamentos ?></h3>
                                <p class="mb-0">Total Equipamentos</p>
                            </div>
                        </div>
                        <!-- Equipamentos operacionais -->
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card text-center p-3 bg-success text-white rounded-3">
                                <h3 class="mb-1"><?= $disponiveis ?></h3>
                                <p class="mb-0">Operacionais</p>
                            </div>
                        </div>
                        <!-- Equipamentos em uso -->
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card text-center p-3 bg-danger text-white rounded-3">
                                <h3 class="mb-1"><?= $emUso ?></h3>
                                <p class="mb-0">Em Uso</p>
                            </div>
                        </div>
                        <!-- Equipamentos em manutenção -->
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
        /* Estilos para cabeçalhos da tabela */
        .table th {
            border-top: none;
            font-weight: 600;
            color: #495057;
            background-color: #f8f9fa;
        }

        /* Efeito hover nos links de ordenação */
        .table th a:hover {
            color: #007bff !important;
        }

        /* Cartões de estatísticas */
        .stat-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            background: white;
            transition: all 0.3s ease;
        }

        /* Efeito hover nos cartões de estatísticas */
        .stat-card:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-3px);
        }

        /* Estilização de badges (indicadores de estado) */
        .badge {
            font-size: 0.75rem;
            padding: 6px 10px;
            border-radius: 20px; /* Bordas arredondadas para badge oval */
        }

        /* Estilo para grupos de botões */
        .btn-group .btn {
            border-radius: 4px !important;
        }

        /* Botões com contorno primário */
        .btn-outline-primary {
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        /* Efeito hover para botões primários */
        .btn-outline-primary:hover {
            background-color: #007bff;
            color: white;
            transform: translateY(-1px);
        }

        /* Fonte monoespaçada para números de série */
        .font-monospace {
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            color: #212529 !important;
            background-color: transparent !important;
            padding: 0 !important;
        }
    </style>

<?php
// CSS adicional para melhorar a experiência da tabela
$this->registerCss(<<<CSS
    /* Efeito hover nas linhas da tabela */
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
    
    /* Posicionamento dos links de ordenação */
    .table th {
        position: relative;
    }
    
    /* Layout dos links de ordenação */
    .table th a {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    /* Tamanho dos ícones de ordenação */
    .table th i {
        font-size: 0.9em;
    }
    
    /* Cores dos badges (consistente com o sistema) */
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
    
    /* Melhorar visibilidade dos badges amarelos */
    .badge.bg-warning {
        font-weight: 500;
    }
    
    /* Estilo para números de série */
    td .font-monospace {
        color: #212529 !important;
        font-weight: 500;
    }
CSS);