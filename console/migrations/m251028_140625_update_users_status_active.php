<?php

use yii\db\Migration;

class m251028_140625_update_users_status_active extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('user', [
            'status' => 10, // STATUS_ACTIVE
            'verification_token' => null
        ], ['status' => 9]); // Onde status é 9 (inactive)
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251028_140625_update_users_status_active cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251028_140625_update_users_status_active cannot be reverted.\n";

        return false;
    }
    */
}
