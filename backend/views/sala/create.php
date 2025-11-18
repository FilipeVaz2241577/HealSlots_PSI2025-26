<?php

/** @var yii\web\View $this */
/** @var common\models\Sala $model */

$this->title = 'Adicionar Nova Sala';
$this->params['breadcrumbs'][] = ['label' => 'Salas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sala-create">
    <?= $this->render('_form', [
        'model' => $model,
        'blocos' => $blocos,
    ]) ?>
</div>