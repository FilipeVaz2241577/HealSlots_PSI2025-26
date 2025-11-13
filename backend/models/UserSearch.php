<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\User;
use yii\db\Query;

class UserSearch extends User
{
    public $role;

    public function rules()
    {
        return [
            [['id', 'status'], 'integer'],
            [['username', 'email', 'created_at', 'role'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = User::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
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
            $userIds = (new Query())
                ->select('user_id')
                ->from('{{%auth_assignment}}')
                ->where(['item_name' => $this->role])
                ->column();

            if (!empty($userIds)) {
                $query->andWhere(['id' => $userIds]);
            } else {
                // Se não encontrar users com essa role, retorna vazio
                $query->andWhere('1=0');
            }
        }

        if ($this->created_at) {
            $query->andFilterWhere(['>=', 'created_at', $this->created_at]);
        }

        return $dataProvider;
    }
}