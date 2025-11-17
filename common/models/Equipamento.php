<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "equipamento".
 *
 * @property int $id
 * @property string $numeroSerie
 * @property int $tipoEquipamento_id
 * @property string $equipamento
 * @property string $estado
 * @property int $created_by
 * @property int $updated_by
 *
 * @property TipoEquipamento $tipoEquipamento
 * @property SalaEquipamento[] $salaEquipamentos
 * @property Sala[] $salas
 * @property User $createdBy
 * @property User $updatedBy
 */
class Equipamento extends ActiveRecord
{
    const ESTADO_OPERACIONAL = 'Operacional';
    const ESTADO_MANUTENCAO = 'Em Manutenção';
    const ESTADO_EM_USO = 'Em Uso';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%equipamento}}';
    }

    /**
     * {@inheritdoc}
     */

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['numeroSerie', 'tipoEquipamento_id', 'equipamento', 'estado'], 'required'],
            [['tipoEquipamento_id'], 'integer'],
            [['numeroSerie', 'equipamento'], 'string', 'max' => 100],
            [['estado'], 'string', 'max' => 20],
            [['estado'], 'default', 'value' => self::ESTADO_OPERACIONAL],
            [['numeroSerie'], 'unique'],
            [['tipoEquipamento_id'], 'exist', 'skipOnError' => true, 'targetClass' => TipoEquipamento::class, 'targetAttribute' => ['tipoEquipamento_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'numeroSerie' => 'Número de Série',
            'tipoEquipamento_id' => 'Tipo de Equipamento',
            'equipamento' => 'Nome do Equipamento',
            'estado' => 'Estado',
            'created_by' => 'Criado Por',
            'updated_by' => 'Atualizado Por'
        ];
    }

    /**
     * Gets query for [[TipoEquipamento]].
     */
    public function getTipoEquipamento()
    {
        return $this->hasOne(TipoEquipamento::class, ['id' => 'tipoEquipamento_id']);
    }

    /**
     * Gets query for [[SalaEquipamentos]].
     */
    public function getSalaEquipamentos()
    {
        return $this->hasMany(SalaEquipamento::class, ['idEquipamento' => 'id']);
    }

    /**
     * Gets query for [[Salas]] through sala_equipamento.
     */
    public function getSalas()
    {
        return $this->hasMany(Sala::class, ['id' => 'idSala'])
            ->via('salaEquipamentos');
    }

    /**
     * Gets query for [[CreatedBy]].
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Gets query for [[UpdatedBy]].
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    /**
     * Get estado options
     */
    public static function optsEstado()
    {
        return [
            self::ESTADO_OPERACIONAL => 'Operacional',
            self::ESTADO_MANUTENCAO => 'Em Manutenção',
            self::ESTADO_EM_USO => 'Em Uso',
        ];
    }

    /**
     * Get badge color for estado
     */
    public function getEstadoBadge()
    {
        $colors = [
            self::ESTADO_OPERACIONAL => 'success',
            self::ESTADO_MANUTENCAO => 'warning',
            self::ESTADO_EM_USO => 'primary',
        ];

        return '<span class="badge bg-' . ($colors[$this->estado] ?? 'secondary') . '">' . $this->estado . '</span>';
    }

    /**
     * Get count by estado for statistics
     */
    public static function getCountByEstado()
    {
        return self::find()
            ->select(['estado', 'COUNT(*) as count'])
            ->groupBy(['estado'])
            ->indexBy('estado')
            ->column();
    }
}