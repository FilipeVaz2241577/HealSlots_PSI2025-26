<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\User;
use yii\helpers\ArrayHelper;

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

    // REMOVA os métodos afterSave, afterDelete e FazPublishNoMosquitto!
    // Eles devem estar APENAS no modelo principal (common/models/User.php)
}