<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\User;

/**
 * UserSearch represents the model behind the search form of `common\models\User`.
 */
class UserSearch extends User
{
    public $role;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'status'], 'integer'],
            [['username', 'email', 'created_at', 'role'], 'safe'],
        ];
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

        if ($this->created_at) {
            $query->andFilterWhere(['>=', 'created_at', $this->created_at]);
        }

        return $dataProvider;
    }
}