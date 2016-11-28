<?php

use Propel\Generator\Manager\MigrationManager;

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1480342014.
 * Generated on 2016-11-28 14:06:54 by vagrant
 */
class PropelMigration_1480342014
{
    public $comment = '';

    public function preUp(MigrationManager $manager)
    {
        // add the pre-migration code here
    }

    public function postUp(MigrationManager $manager)
    {
        // add the post-migration code here
    }

    public function preDown(MigrationManager $manager)
    {
        // add the pre-migration code here
    }

    public function postDown(MigrationManager $manager)
    {
        // add the post-migration code here
    }

    /**
     * Get the SQL statements for the Up migration
     *
     * @return array list of the SQL strings to execute for the Up migration
     *               the keys being the datasources
     */
    public function getUpSQL()
    {
        return array (
  'mysql' => '
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

CREATE INDEX `child_adult_fi_194cca` ON `child_adult` (`parent_id`);

ALTER TABLE `child_adult` ADD CONSTRAINT `child_adult_fk_35cf67`
    FOREIGN KEY (`child_id`)
    REFERENCES `child` (`id`);

ALTER TABLE `child_adult` ADD CONSTRAINT `child_adult_fk_194cca`
    FOREIGN KEY (`parent_id`)
    REFERENCES `adult` (`id`);

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

    /**
     * Get the SQL statements for the Down migration
     *
     * @return array list of the SQL strings to execute for the Down migration
     *               the keys being the datasources
     */
    public function getDownSQL()
    {
        return array (
  'mysql' => '
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `child_adult` DROP FOREIGN KEY `child_adult_fk_35cf67`;

ALTER TABLE `child_adult` DROP FOREIGN KEY `child_adult_fk_194cca`;

DROP INDEX `child_adult_fi_194cca` ON `child_adult`;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}