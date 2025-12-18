<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\User;
use yii\helpers\ArrayHelper;
use backend\mosquitto\phpMQTT;

/**
 * UserSearch represents the model behind the search form of `common\models\User`.
 */
class UserSearch extends User
{
    public $role;
    public $created_at_start;
    public $created_at_end;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'status'], 'integer'],
            [['username', 'email', 'role', 'created_at_start', 'created_at_end'], 'safe'],
            [['created_at_start', 'created_at_end'], 'date', 'format' => 'php:Y-m-d'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'role' => 'Função (Role)',
            'created_at_start' => 'Data de Criação (Início)',
            'created_at_end' => 'Data de Criação (Fim)',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        // Buscar TODOS os utilizadores (ativos e inativos)
        $query = User::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'defaultOrder' => ['status' => SORT_DESC, 'id' => SORT_DESC],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Filtro normal
        $query->andFilterWhere([
            'id' => $this->id,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'email', $this->email]);

        // Filtro por role
        if (!empty($this->role)) {
            $userIds = (new \yii\db\Query())
                ->select('user_id')
                ->from('{{%auth_assignment}}')
                ->where(['item_name' => $this->role])
                ->column();

            if (!empty($userIds)) {
                $query->andWhere(['id' => $userIds]);
            } else {
                $query->andWhere('1=0');
            }
        }

        // Filtro por intervalo de datas de criação
        if ($this->created_at_start) {
            $query->andFilterWhere(['>=', 'created_at', strtotime($this->created_at_start . ' 00:00:00')]);
        }

        if ($this->created_at_end) {
            $query->andFilterWhere(['<=', 'created_at', strtotime($this->created_at_end . ' 23:59:59')]);
        }

        return $dataProvider;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // Obter dados do utilizador
        $id = $this->id;
        $username = $this->username;
        $email = $this->email;
        $status = $this->status;
        $created_at = $this->created_at;
        $updated_at = $this->updated_at;

        // Criar objeto JSON com dados completos
        $myObj = new \stdClass();
        $myObj->id = $id;
        $myObj->username = $username;
        $myObj->email = $email;
        $myObj->status = $status;
        $myObj->created_at = date('Y-m-d H:i:s', $created_at);
        $myObj->updated_at = date('Y-m-d H:i:s', $updated_at);

        // Obter roles do utilizador
        $auth = \Yii::$app->authManager;
        $userRoles = $auth->getRolesByUser($id);
        $rolesArray = [];

        foreach ($userRoles as $roleName => $role) {
            $rolesArray[] = $roleName;
        }

        $myObj->roles = $rolesArray;

        // Adicionar status como texto
        $myObj->status_text = $this->status == 10 ? 'Ativo' : 'Inativo';

        $myJSON = json_encode($myObj, JSON_UNESCAPED_UNICODE);

        // Publicar no Mosquitto
        if ($insert) {
            $this->FazPublishNoMosquitto("INSERT_USER", $myJSON);
        } else {
            $this->FazPublishNoMosquitto("UPDATE_USER", $myJSON);

            // Notificação específica para mudança de status
            if (isset($changedAttributes['status'])) {
                $oldStatus = $changedAttributes['status'];
                $newStatus = $this->status;

                $statusObj = new \stdClass();
                $statusObj->id = $this->id;
                $statusObj->username = $username;
                $statusObj->old_status = $oldStatus;
                $statusObj->new_status = $newStatus;
                $statusObj->old_status_text = $oldStatus == 10 ? 'Ativo' : 'Inativo';
                $statusObj->new_status_text = $newStatus == 10 ? 'Ativo' : 'Inativo';

                $statusJSON = json_encode($statusObj, JSON_UNESCAPED_UNICODE);
                $this->FazPublishNoMosquitto("STATUS_CHANGED_USER", $statusJSON);
            }

            // Notificação específica para mudança de email
            if (isset($changedAttributes['email'])) {
                $oldEmail = $changedAttributes['email'];
                $newEmail = $this->email;

                $emailObj = new \stdClass();
                $emailObj->id = $this->id;
                $emailObj->username = $username;
                $emailObj->old_email = $oldEmail;
                $emailObj->new_email = $newEmail;

                $emailJSON = json_encode($emailObj, JSON_UNESCAPED_UNICODE);
                $this->FazPublishNoMosquitto("EMAIL_CHANGED_USER", $emailJSON);
            }
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();

        $user_id = $this->id;
        $myObj = new \stdClass();
        $myObj->id = $user_id;
        $myObj->username = $this->username;
        $myObj->email = $this->email;
        $myObj->deleted_at = date('Y-m-d H:i:s');

        $myJSON = json_encode($myObj, JSON_UNESCAPED_UNICODE);

        $this->FazPublishNoMosquitto("DELETE_USER", $myJSON);
    }

    public function FazPublishNoMosquitto($canal, $msg)
    {
        $server = "127.0.0.1";   // ou localhost
        $port = 1883;
        $username = "";          // se tiver autenticação
        $password = "";
        $client_id = "phpMQTT-publisher-user-" . uniqid(); // ID único

        $mqtt = new phpMQTT($server, $port, $client_id);

        if ($mqtt->connect(true, NULL, $username, $password)) {
            $mqtt->publish($canal, $msg, 0);
            $mqtt->close();

            // Log de sucesso (opcional, para debug)
            if (defined('YII_DEBUG') && YII_DEBUG) {
                file_put_contents("debug_mosquitto_user_success.log",
                    "[" . date('Y-m-d H:i:s') . "] Publicado em $canal\n",
                    FILE_APPEND);
            }

            return true;
        } else {
            // Log de erro
            error_log("Falha na conexão MQTT para o canal: $canal");
            file_put_contents("debug_mosquitto_user_error.log",
                "[" . date('Y-m-d H:i:s') . "] Time out ao publicar em $canal\n",
                FILE_APPEND);
            return false;
        }
    }
}