<?php

namespace common\models;

use Yii;
<<<<<<< HEAD
=======
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
>>>>>>> origin/filipe

/**
 * This is the model class for table "sala".
 *
 * @property int $id
 * @property string $nome
 * @property string $estado
 * @property int $bloco_id
 *
 * @property Bloco $bloco
 */
<<<<<<< HEAD
class Sala extends \yii\db\ActiveRecord
{
    /**
     * ENUM field values
     */
=======
class Sala extends ActiveRecord
{
>>>>>>> origin/filipe
    const ESTADO_LIVRE = 'Livre';
    const ESTADO_EM_USO = 'EmUso';
    const ESTADO_MANUTENCAO = 'Manutencao';
    const ESTADO_DESATIVADA = 'Desativada';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
<<<<<<< HEAD
        return 'sala';
=======
        return '{{%sala}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
        ];
>>>>>>> origin/filipe
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nome', 'bloco_id'], 'required'],
            [['bloco_id'], 'integer'],
            [['nome'], 'string', 'max' => 100],
            [['estado'], 'string', 'max' => 20],
            [['estado'], 'default', 'value' => self::ESTADO_LIVRE],
            ['estado', 'in', 'range' => array_keys(self::optsEstado())],
            [['bloco_id'], 'exist', 'skipOnError' => true, 'targetClass' => Bloco::class, 'targetAttribute' => ['bloco_id' => 'id']],
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
            'estado' => 'Estado',
            'bloco_id' => 'Bloco',
            'blocoName' => 'Bloco',
        ];
    }

    /**
     * Gets query for [[Bloco]].
<<<<<<< HEAD
     *
     * @return \yii\db\ActiveQuery
=======
>>>>>>> origin/filipe
     */
    public function getBloco()
    {
        return $this->hasOne(Bloco::class, ['id' => 'bloco_id']);
    }

    /**
     * Get bloco name
     */
    public function getBlocoName()
    {
        return $this->bloco ? $this->bloco->nome : 'N/A';
    }

    /**
     * column estado ENUM value labels
     * @return string[]
     */
    public static function optsEstado()
    {
        return [
            self::ESTADO_LIVRE => 'Livre',
<<<<<<< HEAD
            self::ESTADO_EM_USO => 'Em Uso',
            self::ESTADO_MANUTENCAO => 'Manutenção',
=======
            self::ESTADO_EM_USO => 'Em Uso',          // ← "EmUso" mapeado para "Em Uso"
            self::ESTADO_MANUTENCAO => 'Em Manutenção',
>>>>>>> origin/filipe
            self::ESTADO_DESATIVADA => 'Desativada',
        ];
    }

    /**
     * @return string
     */
    public function getEstadoLabel()
    {
<<<<<<< HEAD
        return self::optsEstado()[$this->estado] ?? 'Desconhecido';
=======
        $opts = self::optsEstado();

        // Verificar exatamente o valor armazenado
        if (isset($opts[$this->estado])) {
            return $opts[$this->estado];
        }

        // Se não encontrar, verificar case-insensitive
        $estadoLower = strtolower($this->estado);
        foreach ($opts as $key => $label) {
            if (strtolower($key) === $estadoLower) {
                return $label;
            }
        }

        return 'Desconhecido (' . $this->estado . ')';
>>>>>>> origin/filipe
    }

    /**
     * @return bool
     */
    public function isEstadoLivre()
    {
        return $this->estado === self::ESTADO_LIVRE;
    }

    public function setEstadoToLivre()
    {
        $this->estado = self::ESTADO_LIVRE;
    }

    /**
     * @return bool
     */
    public function isEstadoEmUso()
    {
        return $this->estado === self::ESTADO_EM_USO;
    }

    public function setEstadoToEmUso()
    {
        $this->estado = self::ESTADO_EM_USO;
    }

    /**
     * @return bool
     */
    public function isEstadoManutencao()
    {
        return $this->estado === self::ESTADO_MANUTENCAO;
    }

    public function setEstadoToManutencao()
    {
        $this->estado = self::ESTADO_MANUTENCAO;
    }

    /**
     * @return bool
     */
    public function isEstadoDesativada()
    {
        return $this->estado === self::ESTADO_DESATIVADA;
    }

    public function setEstadoToDesativada()
    {
        $this->estado = self::ESTADO_DESATIVADA;
    }

    /**
<<<<<<< HEAD
=======
     * Verifica se a sala está disponível para reserva
     * @return bool
     */
    public function isDisponivelParaReserva()
    {
        return in_array($this->estado, [
            self::ESTADO_LIVRE,
            self::ESTADO_EM_USO  // Sala ainda pode ser reservada mesmo se já estiver em uso
        ]);
    }

    /**
>>>>>>> origin/filipe
     * Get all salas count by estado
     */
    public static function getCountByEstado()
    {
        return self::find()
            ->select(['estado', 'COUNT(*) as count'])
            ->groupBy('estado')
            ->indexBy('estado')
            ->column();
    }

    /**
     * Get salas em manutenção sem registo de manutenção ativa
     */
    public static function getSalasManutencaoSemRegisto()
    {
        return self::find()
            ->where(['estado' => self::ESTADO_MANUTENCAO])
            ->andWhere(['NOT IN', 'id',
                (new \yii\db\Query())
                    ->select(['sala_id'])
                    ->from('manutencao')
                    ->where(['status' => ['Pendente', 'Em Curso']])
                    ->andWhere(['IS NOT', 'sala_id', null])
            ])
            ->all();
    }

    /**
     * Get count de salas em manutenção sem registo
     */
    public static function getCountSalasManutencaoSemRegisto()
    {
        return self::find()
            ->where(['estado' => self::ESTADO_MANUTENCAO])
            ->andWhere(['NOT IN', 'id',
                (new \yii\db\Query())
                    ->select(['sala_id'])
                    ->from('manutencao')
                    ->where(['status' => ['Pendente', 'Em Curso']])
                    ->andWhere(['IS NOT', 'sala_id', null])
            ])
            ->count();
    }
<<<<<<< HEAD
=======

    /**
     * Get equipamentos in this sala
     */
    public function getEquipamentos()
    {
        return $this->hasMany(Equipamento::class, ['id' => 'idEquipamento'])
            ->viaTable('sala_equipamento', ['idSala' => 'id']);
    }

    /**
     * Get sala_equipamento relationships
     */
    public function getSalaEquipamentos()
    {
        return $this->hasMany(SalaEquipamento::class, ['idSala' => 'id']);
    }

    /**
     * Debug method to check state
     */
    public function debugEstado()
    {
        return [
            'estado' => $this->estado,
            'constante_EM_USO' => self::ESTADO_EM_USO,
            'getEstadoLabel' => $this->getEstadoLabel(),
            'optsEstado' => self::optsEstado(),
            'estado_in_opts' => isset(self::optsEstado()[$this->estado]),
        ];
    }
>>>>>>> origin/filipe
}