<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('editor')};
CREATE TABLE {$this->getTable('editor')} (
  `editor_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `filename` varchar(255) NOT NULL default '',
  `content` text NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`editor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS {$this->getTable('abcv_product_save')};
create table {$this->getTable('abcv_product_save')}( 
    `save_id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'id', 
   `customer_id` int(10) NOT NULL COMMENT 'Customer Id', 
   `product_id` text NOT NULL COMMENT 'Product Id: product and quote', 
   `template_id` varchar(255) NOT NULL COMMENT 'Template Id', 
   `productstyle_id` varchar(255) NOT NULL COMMENT 'product style id',
   `json` longtext NOT NULL COMMENT 'Context save', 
   `image` longtext NOT NULL COMMENT 'Image template', 
   `date_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Create or update time for save', 
   PRIMARY KEY (`save_id`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

 -- ----------------------------
-- Table structure for `abcv_my_images`
-- ----------------------------
DROP TABLE IF EXISTS {$this->getTable('abcv_my_images')};
CREATE TABLE {$this->getTable('abcv_my_images')} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `image_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `image_name_saved` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `time` varchar(14) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
 
 ");

$installer->endSetup(); 