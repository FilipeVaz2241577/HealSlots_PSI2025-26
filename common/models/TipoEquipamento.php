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

    // ==============================================
    // MÉTODOS MQTT - ATUALIZADOS
    // ==============================================

    /**
     * {@inheritdoc}
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // Obter dados do tipo de equipamento
        $data = [
            'id' => $this->id,
            'nome' => $this->nome,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        // Adicionar contagem de equipamentos
        $data['total_equipamentos'] = $this->getEquipamentos()->count();

        $myJSON = json_encode($data, JSON_UNESCAPED_UNICODE);

        // Publicar no Mosquitto
        if ($insert) {
            $this->FazPublishNoMosquitto("INSERT_TIPO_EQUIPAMENTO", $myJSON);
        } else {
            $this->FazPublishNoMosquitto("UPDATE_TIPO_EQUIPAMENTO", $myJSON);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function afterDelete()
    {
        parent::afterDelete();

        $data = [
            'id' => $this->id,
            'nome' => $this->nome,
            'deleted_at' => date('Y-m-d H:i:s'),
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $myJSON = json_encode($data, JSON_UNESCAPED_UNICODE);
        $this->FazPublishNoMosquitto("DELETE_TIPO_EQUIPAMENTO", $myJSON);
    }

    /**
     * Publica mensagem no Mosquitto MQTT
     */
    public function FazPublishNoMosquitto($canal, $msg)
    {
        try {
            // Caminho ABSOLUTO para o phpMQTT
            $phpMQTTPath = Yii::getAlias('@backend') . '/mosquitto/phpMQTT.php';

            if (!file_exists($phpMQTTPath)) {
                error_log("MQTT ERRO: Arquivo não encontrado: $phpMQTTPath");
                return false;
            }

            require_once $phpMQTTPath;

            $server = "127.0.0.1";
            $port = 1883;
            $client_id = "yii_tipoequipamento_" . uniqid();

            $mqtt = new \backend\mosquitto\phpMQTT($server, $port, $client_id);

            if ($mqtt->connect(true, null, null, null, 5)) {
                $mqtt->publish($canal, $msg, 0);
                $mqtt->close();

                // Log de sucesso
                error_log("✅ MQTT TipoEquipamento: Publicado em $canal - ID: " . json_decode($msg)->id);

                // Log em arquivo para debug
                file_put_contents(Yii::getAlias('@backend') . '/mqtt_catalogo.log',
                    date('Y-m-d H:i:s') . " | $canal | " . substr($msg, 0, 100) . "\n",
                    FILE_APPEND
                );

                return true;
            }

            error_log("❌ MQTT TipoEquipamento: Falha na conexão para $canal");
            return false;

        } catch (\Exception $e) {
            error_log("❌ MQTT TipoEquipamento Exception: " . $e->getMessage());
            return false;
        }
    }
}