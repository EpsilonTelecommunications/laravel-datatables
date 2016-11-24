<?php

use Propel\Generator\Manager\MigrationManager;

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1479915531.
 * Generated on 2016-11-23 15:38:51 by vagrant
 */
class PropelMigration_1479915531
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

CREATE TABLE `gender`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `parent`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `gender_id` INTEGER,
    `name` VARCHAR(255),
    `date_of_birth` DATETIME,
    PRIMARY KEY (`id`),
    INDEX `parent_fi_ccc05a` (`gender_id`),
    CONSTRAINT `parent_fk_ccc05a`
        FOREIGN KEY (`gender_id`)
        REFERENCES `gender` (`id`)
) ENGINE=InnoDB;

CREATE TABLE `child`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `gender_id` INTEGER,
    `name` VARCHAR(255),
    `date_of_birth` DATETIME,
    PRIMARY KEY (`id`),
    INDEX `child_fi_ccc05a` (`gender_id`),
    CONSTRAINT `child_fk_ccc05a`
        FOREIGN KEY (`gender_id`)
        REFERENCES `gender` (`id`)
) ENGINE=InnoDB;

CREATE TABLE `child_parent`
(
    `child_id` INTEGER NOT NULL,
    `parent_id` INTEGER NOT NULL,
    PRIMARY KEY (`child_id`,`parent_id`)
) ENGINE=InnoDB;

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

DROP TABLE IF EXISTS `gender`;

DROP TABLE IF EXISTS `parent`;

DROP TABLE IF EXISTS `child`;

DROP TABLE IF EXISTS `child_parent`;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}