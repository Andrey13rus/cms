<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m170701_133505__alter_table__cms_content_element_property extends Migration
{
    public function safeUp()
    {
        $this->delete("{{%cms_content_element_property}}", [
            'or',
            ['element_id' => null],
            ['property_id' => null],
        ]);

        $this->alterColumn("{{%cms_content_element_property}}", 'element_id', $this->integer()->notNull());
        $this->alterColumn("{{%cms_content_element_property}}", 'property_id', $this->integer()->notNull());
    }

    public function safeDown()
    {
        echo "m170701_133505__alter_table__cms_content_element_property cannot be reverted.\n";
        return false;
    }
}