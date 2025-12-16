<?php

/** @var yii\web\View $this */
/** @var common\models\Equipamento $model */
/** @var array $tiposEquipamento */

$this->title = 'Atualizar Equipamento: ' . $model->equipamento;
$this->params['breadcrumbs'][] = ['label' => 'Equipamentos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->equipamento, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Atualizar';
?>
<div class="equipamento-update">
    <?= $this->render('_form', [
        'model' => $model,
        'tiposEquipamento' => $tiposEquipamento,
    ]) ?>
</div>