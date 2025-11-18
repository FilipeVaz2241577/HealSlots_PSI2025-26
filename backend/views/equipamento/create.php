<?php

/** @var yii\web\View $this */
/** @var common\models\Equipamento $model */
/** @var array $tiposEquipamento */

$this->title = 'Adicionar Novo Equipamento';
$this->params['breadcrumbs'][] = ['label' => 'Equipamentos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="equipamento-create">
    <?= $this->render('_form', [
        'model' => $model,
        'tiposEquipamento' => $tiposEquipamento,
    ]) ?>
</div>