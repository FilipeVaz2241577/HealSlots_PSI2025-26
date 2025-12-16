<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "equipamento".
 *
 * @property int $id
 * @property string $numeroSerie
 * @property int $tipoEquipamento_id
 * @property string $equipamento
 * @property string $estado
 *
 * @property TipoEquipamento $tipoEquipamento
 * @property SalaEquipamento[] $salaEquipamentos
 * @property Sala[] $salas
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
    public function behaviors()
    {
        return [
        ];
    }

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

    /**
     * Get equipamentos em manutenção sem registo de manutenção ativa
     */
    public static function getEquipamentosManutencaoSemRegisto()
    {
        return self::find()
            ->where(['estado' => self::ESTADO_MANUTENCAO])
            ->andWhere(['NOT IN', 'id',
                (new \yii\db\Query())
                    ->select(['equipamento_id'])
                    ->from('manutencao')
                    ->where(['status' => ['Pendente', 'Em Curso']])
                    ->andWhere(['IS NOT', 'equipamento_id', null])
            ])
            ->all();
    }

    /**
     * Get count de equipamentos em manutenção sem registo
     */
    public static function getCountEquipamentosManutencaoSemRegisto()
    {
        return self::find()
            ->where(['estado' => self::ESTADO_MANUTENCAO])
            ->andWhere(['NOT IN', 'id',
                (new \yii\db\Query())
                    ->select(['equipamento_id'])
                    ->from('manutencao')
                    ->where(['status' => ['Pendente', 'Em Curso']])
                    ->andWhere(['IS NOT', 'equipamento_id', null])
            ])
            ->count();
    }

    /**
     * Get current sala for this equipamento
     */
    public function getCurrentSala()
    {
        $salaEquipamento = SalaEquipamento::find()
            ->where(['idEquipamento' => $this->id])
            ->one();

        if ($salaEquipamento) {
            return $salaEquipamento->sala;
        }

        return null;
    }

    /**
     * Check if equipamento is in maintenance
     */
    public function isInMaintenance()
    {
        return $this->estado === self::ESTADO_MANUTENCAO;
    }

    /**
     * Check if equipamento is operational
     */
    public function isOperational()
    {
        return $this->estado === self::ESTADO_OPERACIONAL;
    }

    /**
     * Check if equipamento is in use
     */
    public function isInUse()
    {
        return $this->estado === self::ESTADO_EM_USO;
    }

    /**
     * Check if equipamento has a sala assigned
     */
    public function hasSala()
    {
        return SalaEquipamento::find()
            ->where(['idEquipamento' => $this->id])
            ->exists();
    }

    /**
     * Get salas count for this equipamento
     */
    public function getSalasCount()
    {
        return SalaEquipamento::find()
            ->where(['idEquipamento' => $this->id])
            ->count();
    }
}