<?php
/**
 * Добавляем типы публикаций и страниц
 * m141111_100557_alter_tables_tree_and_publication
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010-2014 SkeekS (Sx)
 * @date 11.11.2014
 * @since 1.0.0
 */

use yii\db\Schema;
use yii\db\Migration;

class m141111_100557_alter_tables_tree_and_publication extends Migration
{
    public function up()
    {
        $this->addColumn('{{%publication}}', 'type', Schema::TYPE_STRING . '(32) NULL');
        $this->addColumn('{{%cms_tree}}', 'type', Schema::TYPE_STRING . '(32) NULL');
        $this->execute("ALTER TABLE {{%publication}} ADD INDEX(type);");
        $this->execute("ALTER TABLE {{%cms_tree}} ADD INDEX(type);");
    }

    public function down()
    {
        $this->dropColumn('{{%publication}}', 'type');
        $this->dropColumn('{{%cms_tree}}', 'type');

    }
}
