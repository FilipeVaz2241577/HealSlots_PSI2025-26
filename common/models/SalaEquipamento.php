<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sala_equipamento".
 *
 * @property int $idSala
 * @property int $idEquipamento
 *
 * @property Sala $sala
 * @property Equipamento $equipamento
 */
class SalaEquipamento extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%sala_equipamento}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idSala', 'idEquipamento'], 'required'],
            [['idSala', 'idEquipamento'], 'integer'],
            [['idSala', 'idEquipamento'], 'unique', 'targetAttribute' => ['idSala', 'idEquipamento']],
            [['idSala'], 'exist', 'skipOnError' => true, 'targetClass' => Sala::class, 'targetAttribute' => ['idSala' => 'id']],
            [['idEquipamento'], 'exist', 'skipOnError' => true, 'targetClass' => Equipamento::class, 'targetAttribute' => ['idEquipamento' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'idSala' => 'Sala',
            'idEquipamento' => 'Equipamento',
        ];
    }

    /**
     * Gets query for [[Sala]].
     */
    public function getSala()
    {
        return $this->hasOne(Sala::class, ['id' => 'idSala']);
    }

    /**
     * Gets query for [[Equipamento]].
     */
    public function getEquipamento()
    {
        return $this->hasOne(Equipamento::class, ['id' => 'idEquipamento']);
    }
}