<?php

/** @var yii\web\View $this */
/** @var string $categoria */
<<<<<<< HEAD

use yii\bootstrap5\Html;

// Mapear categorias para títulos
=======
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
>>>>>>> origin/filipe
$titulosCategorias = [
        'monitorizacao' => 'Equipamentos de Monitorização',
        'moveis' => 'Equipamentos Móveis',
        'cirurgicos' => 'Instrumentos Cirúrgicos',
        'consumo' => 'Materiais de Consumo'
];

<<<<<<< HEAD
$titulo = $titulosCategorias[$categoria] ?? 'Equipamentos';
$this->title = $titulo;
?>

<div class="site-equipamentos">
    <div class="container">
        <div class="card shadow">
            <div class="card-body p-5">
                <!-- Cabeçalho -->
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div>
                        <h1 class="display-5 text-primary mb-2"><?= Html::encode($this->title) ?></h1>
                        <p class="lead text-muted">Lista detalhada de todos os equipamentos</p>
                    </div>
                    <div>
                        <?= Html::a('Voltar às Categorias', ['site/tiposequipamento'], ['class' => 'btn btn-outline-secondary']) ?>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Pesquisar equipamentos...">
                            <button class="btn btn-outline-primary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <select class="form-select">
                            <option value="">Todos os estados</option>
                            <option value="disponivel">Disponível</option>
                            <option value="ocupado">Ocupado</option>
                            <option value="manutencao">Em Manutenção</option>
                            <option value="defeito">Com Defeito</option>
                        </select>
                    </div>
                </div>

                <!-- Tabela de Equipamentos -->
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-primary">
                        <tr>
                            <th>Nome do Equipamento</th>
                            <th>Estado</th>
                            <th>Número de Série</th>
                            <th>Ações</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if ($categoria === 'monitorizacao'): ?>
                            <!-- Equipamentos de Monitorização -->
                            <tr>
                                <td>Monitor de Sinais Vitais DX-1000</td>
                                <td><span class="badge bg-success">Disponível</span></td>
                                <td>SN-MON-2024-001</td>
                                <td>
                                    <?= Html::a('Detalhes', ['site/detalhe-equipamento', 'equipamento' => 'Monitor de Sinais Vitais DX-1000', 'categoria' => $categoria], [
                                            'class' => 'btn btn-outline-primary btn-sm'
                                    ]) ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Electrocardíógrafo ECG-200</td>
                                <td><span class="badge bg-warning">Ocupado</span></td>
                                <td>SN-ECG-2024-002</td>
                                <td>
                                    <?= Html::a('Detalhes', ['#'], [
                                            'class' => 'btn btn-outline-primary btn-sm',
                                            'onclick' => 'showEquipmentDetails("Electrocardíógrafo ECG-200", "SN-ECG-2024-002", "Ocupado", "ECG de 12 derivações", "Bloco B - Sala 205", "2024-01-20")'
                                    ]) ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Oxímetro de Pulso OP-50</td>
                                <td><span class="badge bg-danger">Em Manutenção</span></td>
                                <td>SN-OXI-2024-003</td>
                                <td>
                                    <?= Html::a('Detalhes', ['#'], [
                                            'class' => 'btn btn-outline-primary btn-sm',
                                            'onclick' => 'showEquipmentDetails("Oxímetro de Pulso OP-50", "SN-OXI-2024-003", "Em Manutenção", "Oxímetro portátil com alarme", "Departamento Manutenção", "2024-02-01")'
                                    ]) ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Monitor de Pressão Arterial PA-300</td>
                                <td><span class="badge bg-success">Disponível</span></td>
                                <td>SN-PA-2024-004</td>
                                <td>
                                    <?= Html::a('Detalhes', ['#'], [
                                            'class' => 'btn btn-outline-primary btn-sm',
                                            'onclick' => 'showEquipmentDetails("Monitor de Pressão Arterial PA-300", "SN-PA-2024-004", "Disponível", "Monitor automático de PA", "Bloco A - Sala 102", "2024-01-10")'
                                    ]) ?>
                                </td>
                            </tr>

                        <?php elseif ($categoria === 'moveis'): ?>
                            <!-- Equipamentos Móveis -->
                            <tr>
                                <td>Cama Hospitalar Elétrica CH-500</td>
                                <td><span class="badge bg-success">Disponível</span></td>
                                <td>SN-CAM-2024-001</td>
                                <td>
                                    <?= Html::a('Detalhes', ['#'], [
                                            'class' => 'btn btn-outline-primary btn-sm',
                                            'onclick' => 'showEquipmentDetails("Cama Hospitalar Elétrica CH-500", "SN-CAM-2024-001", "Disponível", "Cama elétrica com comando remoto", "Bloco C - Enfermaria 301", "2024-01-05")'
                                    ]) ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Maca de Emergência ME-100</td>
                                <td><span class="badge bg-warning">Ocupado</span></td>
                                <td>SN-MAC-2024-002</td>
                                <td>
                                    <?= Html::a('Detalhes', ['#'], [
                                            'class' => 'btn btn-outline-primary btn-sm',
                                            'onclick' => 'showEquipmentDetails("Maca de Emergência ME-100", "SN-MAC-2024-002", "Ocupado", "Maca de emergência com rodas", "Urgências - Área Triagem", "2024-01-25")'
                                    ]) ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Carro de Anestesia CA-200</td>
                                <td><span class="badge bg-success">Disponível</span></td>
                                <td>SN-CAR-2024-003</td>
                                <td>
                                    <?= Html::a('Detalhes', ['#'], [
                                            'class' => 'btn btn-outline-primary btn-sm',
                                            'onclick' => 'showEquipmentDetails("Carro de Anestesia CA-200", "SN-CAR-2024-003", "Disponível", "Carro de anestesia completo", "Bloco B - Sala Cirúrgica", "2024-01-18")'
                                    ]) ?>
                                </td>
                            </tr>

                        <?php elseif ($categoria === 'cirurgicos'): ?>
                            <!-- Instrumentos Cirúrgicos -->
                            <tr>
                                <td>Kit Cirúrgico Básico KCB-100</td>
                                <td><span class="badge bg-success">Disponível</span></td>
                                <td>SN-KIT-2024-001</td>
                                <td>
                                    <?= Html::a('Detalhes', ['#'], [
                                            'class' => 'btn btn-outline-primary btn-sm',
                                            'onclick' => 'showEquipmentDetails("Kit Cirúrgico Básico KCB-100", "SN-KIT-2024-001", "Disponível", "Kit cirúrgico básico esterilizado", "Bloco B - Central Sterilização", "2024-02-05")'
                                    ]) ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Autoclave AC-3000</td>
                                <td><span class="badge bg-danger">Em Manutenção</span></td>
                                <td>SN-AUT-2024-002</td>
                                <td>
                                    <?= Html::a('Detalhes', ['#'], [
                                            'class' => 'btn btn-outline-primary btn-sm',
                                            'onclick' => 'showEquipmentDetails("Autoclave AC-3000", "SN-AUT-2024-002", "Em Manutenção", "Autoclave de grande capacidade", "Bloco B - Central Sterilização", "2023-12-15")'
                                    ]) ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Bisturi Elétrico BE-200</td>
                                <td><span class="badge bg-success">Disponível</span></td>
                                <td>SN-BIS-2024-003</td>
                                <td>
                                    <?= Html::a('Detalhes', ['#'], [
                                            'class' => 'btn btn-outline-primary btn-sm',
                                            'onclick' => 'showEquipmentDetails("Bisturi Elétrico BE-200", "SN-BIS-2024-003", "Disponível", "Bisturi elétrico de alta precisão", "Bloco B - Sala Cirúrgica 1", "2024-01-30")'
                                    ]) ?>
                                </td>
                            </tr>

                        <?php elseif ($categoria === 'consumo'): ?>
                            <!-- Materiais de Consumo -->
                            <tr>
                                <td>Luvas Cirúrgicas Estéreis</td>
                                <td><span class="badge bg-success">Disponível</span></td>
                                <td>LOTE-LUV-2024-001</td>
                                <td>
                                    <?= Html::a('Detalhes', ['#'], [
                                            'class' => 'btn btn-outline-primary btn-sm',
                                            'onclick' => 'showEquipmentDetails("Luvas Cirúrgicas Estéreis", "LOTE-LUV-2024-001", "Disponível", "Luvas cirúrgicas estéreis tamanho M", "Armazém Central", "2024-02-10", "consumo")'
                                    ]) ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Seringas 10ml</td>
                                <td><span class="badge bg-info">Stock Baixo</span></td>
                                <td>LOTE-SER-2024-002</td>
                                <td>
                                    <?= Html::a('Detalhes', ['#'], [
                                            'class' => 'btn btn-outline-primary btn-sm',
                                            'onclick' => 'showEquipmentDetails("Seringas 10ml", "LOTE-SER-2024-002", "Stock Baixo", "Seringas descartáveis 10ml", "Armazém Central", "2024-02-08", "consumo")'
                                    ]) ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Agulhas 21G</td>
                                <td><span class="badge bg-success">Disponível</span></td>
                                <td>LOTE-AGU-2024-003</td>
                                <td>
                                    <?= Html::a('Detalhes', ['#'], [
                                            'class' => 'btn btn-outline-primary btn-sm',
                                            'onclick' => 'showEquipmentDetails("Agulhas 21G", "LOTE-AGU-2024-003", "Disponível", "Agulhas hipodérmicas 21G", "Armazém Central", "2024-02-12", "consumo")'
                                    ]) ?>
                                </td>
                            </tr>

                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="fas fa-exclamation-circle fa-2x mb-3"></i>
                                    <br>
                                    Categoria não encontrada
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Estatísticas -->
                <div class="row mt-5">
                    <div class="col-md-3">
                        <div class="stat-card text-center p-3">
                            <h3 class="text-primary" id="totalEquipamentos">0</h3>
                            <p class="text-muted mb-0">Total</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card text-center p-3">
                            <h3 class="text-success" id="disponiveis">0</h3>
                            <p class="text-muted mb-0">Disponíveis</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card text-center p-3">
                            <h3 class="text-warning" id="ocupados">0</h3>
                            <p class="text-muted mb-0">Ocupados</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card text-center p-3">
                            <h3 class="text-danger" id="manutencao">0</h3>
                            <p class="text-muted mb-0">Manutenção</p>
=======
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
>>>>>>> origin/filipe
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<<<<<<< HEAD
</div>

<!-- Modal para Detalhes do Equipamento -->
<div class="modal fade" id="equipmentModal" tabindex="-1" aria-labelledby="equipmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="equipmentModalLabel">Detalhes do Equipamento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">Informações Gerais</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Nome:</strong></td>
                                <td id="modalName">-</td>
                            </tr>
                            <tr>
                                <td><strong>Nº Série/Lote:</strong></td>
                                <td id="modalSerial">-</td>
                            </tr>
                            <tr>
                                <td><strong>Estado:</strong></td>
                                <td id="modalStatus">-</td>
                            </tr>
                            <tr>
                                <td><strong>Localização:</strong></td>
                                <td id="modalLocation">-</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Detalhes Técnicos</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Descrição:</strong></td>
                                <td id="modalDescription">-</td>
                            </tr>
                            <tr>
                                <td><strong>Data de Entrada:</strong></td>
                                <td id="modalDate">-</td>
                            </tr>
                            <tr>
                                <td><strong>Categoria:</strong></td>
                                <td id="modalCategory">-</td>
                            </tr>
                            <tr id="stockRow" style="display: none;">
                                <td><strong>Stock Atual:</strong></td>
                                <td id="modalStock">-</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Histórico de Manutenções (apenas para equipamentos não de consumo) -->
                <div id="maintenanceHistory" style="display: none;">
                    <hr>
                    <h6 class="text-muted">Última Manutenção</h6>
                    <div class="alert alert-info">
                        <small>
                            <strong>Data:</strong> 15/01/2024<br>
                            <strong>Tipo:</strong> Manutenção Preventiva<br>
                            <strong>Técnico:</strong> João Silva<br>
                            <strong>Observações:</strong> Equipamento em perfeitas condições
                        </small>
                    </div>
                </div>

                <!-- Informações de Stock (apenas para materiais de consumo) -->
                <div id="stockInfo" style="display: none;">
                    <hr>
                    <h6 class="text-muted">Gestão de Stock</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <h4 class="text-primary" id="currentStock">0</h4>
                                <small class="text-muted">Stock Atual</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h4 class="text-warning" id="minStock">0</h4>
                                <small class="text-muted">Stock Mínimo</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h4 class="text-success" id="maxStock">0</h4>
                                <small class="text-muted">Stock Máximo</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" id="reserveButton" style="display: none;">Reservar Equipamento</button>
                <button type="button" class="btn btn-warning" id="requestMaintenanceButton" style="display: none;">Solicitar Manutenção</button>
            </div>
        </div>
    </div>
</div>

<style>
    .table th {
        border-top: none;
        font-weight: 600;
        color: #495057;
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
        transform: translateY(-1px);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Calcular estatísticas
        const total = document.querySelectorAll('tbody tr').length - 1; // -1 para excluir a linha de "não encontrado"
        const disponiveis = document.querySelectorAll('.badge.bg-success').length;
        const ocupados = document.querySelectorAll('.badge.bg-warning').length;
        const manutencao = document.querySelectorAll('.badge.bg-danger').length;

        document.getElementById('totalEquipamentos').textContent = total;
        document.getElementById('disponiveis').textContent = disponiveis;
        document.getElementById('ocupados').textContent = ocupados;
        document.getElementById('manutencao').textContent = manutencao;
    });

    // Função para mostrar detalhes do equipamento
    function showEquipmentDetails(name, serial, status, description, location, date, tipo = 'equipamento') {
        // Preencher os dados no modal
        document.getElementById('modalName').textContent = name;
        document.getElementById('modalSerial').textContent = serial;
        document.getElementById('modalDescription').textContent = description;
        document.getElementById('modalLocation').textContent = location;
        document.getElementById('modalDate').textContent = formatDate(date);
        document.getElementById('modalCategory').textContent = document.querySelector('h1.display-5').textContent;

        // Configurar o estado com badge
        let statusBadge = '';
        let statusClass = '';
        switch(status) {
            case 'Disponível':
                statusClass = 'success';
                break;
            case 'Ocupado':
                statusClass = 'warning';
                break;
            case 'Em Manutenção':
                statusClass = 'danger';
                break;
            case 'Stock Baixo':
                statusClass = 'info';
                break;
            default:
                statusClass = 'secondary';
        }
        statusBadge = `<span class="badge bg-${statusClass}">${status}</span>`;
        document.getElementById('modalStatus').innerHTML = statusBadge;

        // Mostrar/ocultar seções baseadas no tipo
        const maintenanceHistory = document.getElementById('maintenanceHistory');
        const stockInfo = document.getElementById('stockInfo');
        const stockRow = document.getElementById('stockRow');
        const reserveButton = document.getElementById('reserveButton');
        const requestMaintenanceButton = document.getElementById('requestMaintenanceButton');

        if (tipo === 'consumo') {
            // Material de consumo
            maintenanceHistory.style.display = 'none';
            stockInfo.style.display = 'block';
            stockRow.style.display = 'table-row';
            reserveButton.style.display = 'none';
            requestMaintenanceButton.style.display = 'none';

            // Configurar dados de stock
            document.getElementById('modalStock').textContent = getRandomStock();
            document.getElementById('currentStock').textContent = getRandomStock();
            document.getElementById('minStock').textContent = '50';
            document.getElementById('maxStock').textContent = '500';
        } else {
            // Equipamento
            maintenanceHistory.style.display = 'block';
            stockInfo.style.display = 'none';
            stockRow.style.display = 'none';

            // Mostrar botões baseados no estado
            if (status === 'Disponível') {
                reserveButton.style.display = 'inline-block';
                requestMaintenanceButton.style.display = 'none';
            } else if (status === 'Em Manutenção') {
                reserveButton.style.display = 'none';
                requestMaintenanceButton.style.display = 'inline-block';
            } else {
                reserveButton.style.display = 'none';
                requestMaintenanceButton.style.display = 'inline-block';
            }
        }

        // Mostrar o modal
        const modal = new bootstrap.Modal(document.getElementById('equipmentModal'));
        modal.show();
    }

    // Função auxiliar para formatar data
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('pt-PT');
    }

    // Função para gerar stock aleatório (apenas para demonstração)
    function getRandomStock() {
        return Math.floor(Math.random() * 400) + 50;
    }

    // Event listeners para os botões do modal
    document.getElementById('reserveButton').addEventListener('click', function() {
        const equipmentName = document.getElementById('modalName').textContent;
        alert(`Equipamento "${equipmentName}" reservado com sucesso!`);
        bootstrap.Modal.getInstance(document.getElementById('equipmentModal')).hide();
    });

    document.getElementById('requestMaintenanceButton').addEventListener('click', function() {
        const equipmentName = document.getElementById('modalName').textContent;
        alert(`Pedido de manutenção para "${equipmentName}" enviado!`);
        bootstrap.Modal.getInstance(document.getElementById('equipmentModal')).hide();
    });
</script>
=======

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
>>>>>>> origin/filipe
