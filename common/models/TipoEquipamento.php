<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tipoEquipamento".
 *
 * @property int $id
 * @property string $nome
 *
 * @property Equipamento[] $equipamentos
 */
class TipoEquipamento extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tipoEquipamento}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nome'], 'required'],
            [['nome'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nome' => 'Nome',
        ];
    }

    /**
     * Gets query for [[Equipamentos]].
     */
    public function getEquipamentos()
    {
        return $this->hasMany(Equipamento::class, ['tipoEquipamento_id' => 'id']);
    }

    /**
     * Get all tipos as array for dropdown
     */
    public static function getTiposArray()
    {
        return self::find()->select(['nome', 'id'])->indexBy('id')->column();
    }
}