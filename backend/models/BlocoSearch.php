<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Bloco;
use backend\mosquitto\phpMQTT;

class BlocoSearch extends Bloco
{
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['nome', 'estado'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Bloco::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['nome' => SORT_ASC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'estado' => $this->estado,
        ]);

        $query->andFilterWhere(['like', 'nome', $this->nome]);

        return $dataProvider;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // Obter dados do registo
        $id = $this->id;
        $nome = $this->nome;
        $estado = $this->estado;

        // Criar objeto JSON
        $myObj = new \stdClass();
        $myObj->id = $id;
        $myObj->nome = $nome;
        $myObj->estado = $estado;

        $myJSON = json_encode($myObj);

        // Publicar no Mosquitto
        if ($insert) {
            $this->FazPublishNoMosquitto("INSERT_BLOCO", $myJSON);
        } else {
            $this->FazPublishNoMosquitto("UPDATE_BLOCO", $myJSON);
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();

        $bloco_id = $this->id;
        $myObj = new \stdClass();
        $myObj->id = $bloco_id;
        $myJSON = json_encode($myObj);

        $this->FazPublishNoMosquitto("DELETE_BLOCO", $myJSON);
    }

    public function FazPublishNoMosquitto($canal, $msg)
    {
        $server = "127.0.0.1";   // ou localhost
        $port = 1883;
        $username = "";          // se tiver autenticação
        $password = "";
        $client_id = "phpMQTT-publisher-bloco"; // ID único

        // Certifique-se de que a classe phpMQTT está disponível
        // Se estiver em backend/mosquitto/phpMQTT.php com namespace backend\mosquitto
        $mqtt = new phpMQTT($server, $port, $client_id);

        if ($mqtt->connect(true, NULL, $username, $password)) {
            $mqtt->publish($canal, $msg, 0);
            $mqtt->close();
        } else {
            // Log de erro
            error_log("Falha na conexão MQTT para o canal: $canal");
            // Alternativa: escrever em arquivo
            file_put_contents("debug_mosquitto_bloco.log", "[" . date('Y-m-d H:i:s') . "] Time out ao publicar em $canal\n", FILE_APPEND);
        }
    }
}