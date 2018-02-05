<?php
$tables['1.0.0'][] = "CREATE TABLE IF NOT EXISTS `{prefix}cms_domain` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `is_default` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否是默认域名',
    `is_https` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否启用HTTPS',
    `domain` VARCHAR(64) NOT NULL COMMENT '域名',
    `theme` VARCHAR(32) NOT NULL DEFAULT 'default' COMMENT '模板目录',
    PRIMARY KEY (`id`),
    UNIQUE INDEX `domain_UNIQUE` (`domain` ASC)
)  ENGINE=INNODB DEFAULT CHARACTER SET=UTF8 COMMENT='站点域名配置'";

$tables['1.0.0'][] = "CREATE TABLE IF NOT EXISTS `{prefix}cms_model` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `refid` VARCHAR(16) NOT NULL COMMENT '模型标识',
    `name` VARCHAR(32) NOT NULL COMMENT '模型名称',
    `flags` VARCHAR(128) NULL COMMENT '自定义标志',
    PRIMARY KEY (`id`),
    UNIQUE INDEX `UDX_REFID` (`refid` ASC)
)  ENGINE=INNODB DEFAULT CHARACTER SET={encoding} COMMENT='模型'";

$tables['1.0.0'][] = "CREATE TABLE IF NOT EXISTS `{prefix}cms_channel_model` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `page_id` INT UNSIGNED NOT NULL COMMENT '栏目ID',
    `model` INT NOT NULL COMMENT '模型ID',
    `url_pattern` TEXT NULL COMMENT 'URL生成规则',
    `template_file` VARCHAR(128) NULL COMMENT '页面模板文件',
    INDEX `CHANNEL_ID` (`page_id` ASC),
    PRIMARY KEY (`id`),
    INDEX `fk_cms_channel_model_cms_model1_idx` (`model` ASC),
    CONSTRAINT `fk_cms_channel_model_cms_channel1` FOREIGN KEY (`page_id`)
        REFERENCES `{prefix}cms_channel` (`page_id`)
        ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT `fk_cms_channel_model_cms_model1` FOREIGN KEY (`model`)
        REFERENCES `{prefix}cms_model` (`id`)
        ON DELETE NO ACTION ON UPDATE NO ACTION
)  ENGINE=INNODB DEFAULT CHARACTER SET=UTF8 COMMENT='栏目里可以发放的模型配置'";

$tables['1.0.0'][] = "CREATE TABLE IF NOT EXISTS `{prefix}cms_model_field` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `model` INT NOT NULL COMMENT '模型编号',
    `label` VARCHAR(32) NOT NULL COMMENT '名称',
    `name` VARCHAR(16) NOT NULL COMMENT '字段名',
    `field` VARCHAR(128) NOT NULL COMMENT '类型',
    `layout` VARCHAR(128) NULL COMMENT '布局',
    `fieldCfg` TEXT NULL COMMENT '字段配置',
    `dsCfg` TEXT NULL COMMENT '数据源配置',
    `note` VARCHAR(64) NULL COMMENT '提示',
    `index` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否索引',
    `unique` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否是唯一索引',
    `required` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否必须提供',
    `type` VARCHAR(8) NOT NULL DEFAULT 'varchar' COMMENT '类型',
    `length` SMALLINT UNSIGNED NOT NULL DEFAULT 128 COMMENT '长度',
    PRIMARY KEY (`id`),
    UNIQUE INDEX `UDX_M_NAME` (`model` ASC , `name` ASC)
)  ENGINE=INNODB DEFAULT CHARACTER SET=UTF8MB4 COMMENT='模型字段'";

$tables['1.0.0'][] = "CREATE TABLE IF NOT EXISTS `{prefix}cms_router` (
    `id` INT UNSIGNED NOT NULL COMMENT '页面编号',
    `route` CHAR(32) NOT NULL COMMENT 'URL的MD5',
    PRIMARY KEY (`id`),
    UNIQUE INDEX `UDX_ROUTE` (`route` ASC)
)  ENGINE=INNODB DEFAULT CHARACTER SET={encoding} COMMENT='CMS路由表'";

$tables['1.0.0'][] = "CREATE TABLE IF NOT EXISTS `{prefix}cms_page` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `create_time` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
    `create_uid` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建用户',
    `model` INT NOT NULL COMMENT '模型',
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0草稿1发布2删除',
    `noindex` TINYINT UNSIGNED ZEROFILL NOT NULL DEFAULT 0 COMMENT '阻止被索引',
    `ver` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '当前版本',
    `expire` INT NOT NULL DEFAULT 0 COMMENT '-1不缓存0系统配置，其它值为自定义缓存',
    `path` VARCHAR(64) NOT NULL COMMENT '路径',
    `url` VARCHAR(256) NULL COMMENT 'URL',
    PRIMARY KEY (`id`),
    INDEX `fk_cms_page_cms_model1_idx` (`model` ASC),
    UNIQUE INDEX `create_time_UNIQUE` (`create_time` ASC),
    CONSTRAINT `fk_cms_page_cms_model1` FOREIGN KEY (`model`)
        REFERENCES `{prefix}cms_model` (`id`)
        ON DELETE NO ACTION ON UPDATE NO ACTION
)  ENGINE=INNODB DEFAULT CHARACTER SET=UTF8MB4 COMMENT='cms页面'";

$tables['1.0.0'][] = "CREATE TABLE IF NOT EXISTS `{prefix}cms_page_field` (
    `page_id` INT UNSIGNED NOT NULL COMMENT '页面编号',
    `update_time` INT UNSIGNED NOT NULL COMMENT '修改时间',
    `update_uid` INT UNSIGNED NOT NULL COMMENT '作者',
    `channel` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '栏目',
    `model` INT NOT NULL COMMENT '模型',
    `path` VARCHAR(64) NOT NULL COMMENT '虚拟路径',
    `title` VARCHAR(96) NULL COMMENT '标题',
    `title2` VARCHAR(64) NULL COMMENT '短标题',
    `template_file` VARCHAR(128) NULL COMMENT '模板文件',
    `content_file` VARCHAR(128) NULL COMMENT '内容文件地址',
    `author` VARCHAR(32) NULL COMMENT '作者',
    `source` VARCHAR(32) NULL COMMENT '来源',
    `tags` VARCHAR(128) NULL COMMENT '标签,逗号分隔',
    `flags` VARCHAR(128) NULL COMMENT '标志,逗号分隔',
    `keywords` VARCHAR(128) NULL COMMENT '关键词',
    `description` VARCHAR(256) NULL COMMENT '描述',
    `image` VARCHAR(512) NULL COMMENT '插图',
    `related_pages` TEXT NULL COMMENT '相关页面',
    PRIMARY KEY (`page_id`),
    INDEX `IDX_TIME` (`update_time` ASC),
    INDEX `IDX_MODEL` (`model` ASC , `path` ASC),
    CONSTRAINT `fk_cms_page_field_cms_page1` FOREIGN KEY (`page_id`)
        REFERENCES `{prefix}cms_page` (`id`)
        ON DELETE NO ACTION ON UPDATE NO ACTION
)  ENGINE=INNODB DEFAULT CHARACTER SET=UTF8MB4 COMMENT='cms页面字段'";

$tables['1.0.0'][] = "CREATE TABLE IF NOT EXISTS `{prefix}cms_page_tag` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `page_id` INT UNSIGNED NOT NULL COMMENT '页面编号',
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `tag` VARCHAR(16) NOT NULL COMMENT '标签',
    PRIMARY KEY (`id`),
    INDEX `IDX_PAGEID` (`page_id` ASC),
    INDEX `IDX_TAG` (`tag` ASC, `status` ASC)
)  ENGINE=INNODB DEFAULT CHARACTER SET={encoding} COMMENT='标签页，发布时同步'";

$tables['1.0.0'][] = "CREATE TABLE IF NOT EXISTS `{prefix}cms_page_flag` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `page_id` INT UNSIGNED NOT NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `model` VARCHAR(16) NOT NULL,
    `flag` VARCHAR(16) NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `IDX_SMF` (`flag` ASC, `model` ASC, `status` ASC)
)  ENGINE=INNODB DEFAULT CHARACTER SET={encoding} COMMENT='页面标志，发布时同步过来'";

$tables['1.0.0'][] = "CREATE TABLE IF NOT EXISTS `{prefix}cms_page_rev` (
    `page_id` INT UNSIGNED NOT NULL COMMENT '页面编号',
    `ver` INT UNSIGNED NOT NULL COMMENT '版本号',
    `create_time` INT UNSIGNED NOT NULL DEFAULT 0,
    `create_uid` INT UNSIGNED NOT NULL,
    `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态,0草稿，1：提交审核，2：未通过，3：通过',
    `ip` VARCHAR(64) NOT NULL COMMENT '操作时使用的IP地址',
    `content_file` VARCHAR(128) NULL COMMENT '内容文件',
    `data_file` VARCHAR(128) NULL COMMENT '数据文件',
    `msg` TEXT NULL COMMENT '审核不通行原因',
    PRIMARY KEY (`page_id` , `ver`)
)  ENGINE=INNODB DEFAULT CHARACTER SET=UTF8MB4 COMMENT='页面版本'";

$tables['1.0.0'][] = "CREATE TABLE IF NOT EXISTS `{prefix}cms_channel` (
    `page_id` INT UNSIGNED NOT NULL COMMENT '页面ID',
    `display_sort` SMALLINT UNSIGNED NOT NULL DEFAULT 999 COMMENT '显示排序',
    `store_path` VARCHAR(15) NOT NULL COMMENT '页面文件存储路径',
    PRIMARY KEY (`page_id`)
)  ENGINE=INNODB DEFAULT CHARACTER SET=UTF8 COMMENT='栏目'";


$tables['1.0.0'][] = "CREATE TABLE IF NOT EXISTS `{prefix}cms_search_index` (
    `page_id` INT UNSIGNED NOT NULL,
    `index` TEXT NOT NULL COMMENT '全文索引',
    PRIMARY KEY (`page_id`),
    FULLTEXT INDEX `FIDX_INDEX` ( `index` ASC ),
    CONSTRAINT `fk_cms_search_index_cms_page_field1` FOREIGN KEY (`page_id`)
        REFERENCES `{prefix}cms_page_field` (`page_id`)
        ON DELETE NO ACTION ON UPDATE NO ACTION
)  ENGINE=INNODB DEFAULT CHARACTER SET=UTF8MB4 COMMENT='全文索引'";