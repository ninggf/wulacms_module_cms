<?php
$tables['1.0.0'][] = "CREATE TABLE IF NOT EXISTS `{prefix}cms_domain` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `is_default` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否是默认域名',
    `is_https` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否启用HTTPS',
    `domain` VARCHAR(64) NOT NULL COMMENT '域名',
    `theme` VARCHAR(32) NOT NULL DEFAULT 'default' COMMENT '模板目录',
    PRIMARY KEY (`id`),
    UNIQUE INDEX `UDX_DOMAIN` (`domain` ASC)
)  ENGINE=INNODB DEFAULT CHARACTER SET={encoding} COMMENT='站点域名配置'";

$tables['1.0.0'][] = "CREATE TABLE IF NOT EXISTS `{prefix}cms_model` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `refid` VARCHAR(16) NOT NULL COMMENT '模型标识',
    `name` VARCHAR(32) NOT NULL COMMENT '模型名称',
    `flags` VARCHAR(128) NULL COMMENT '自定义标志',
    PRIMARY KEY (`id`),
    UNIQUE INDEX `UDX_REFID` (`refid` ASC)
)  ENGINE=INNODB DEFAULT CHARACTER SET={encoding} COMMENT='模型'";

$tables['1.0.0'][] = "CREATE TABLE IF NOT EXISTS `{prefix}cms_model_field` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `model` VARCHAR(16) NOT NULL COMMENT '模型编号',
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
)  ENGINE=INNODB DEFAULT CHARACTER SET={encoding} COMMENT='模型字段'";

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
    `model` VARCHAR(16) NOT NULL COMMENT '模型',
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '0删除1草稿2发布',
    `noindex` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '阻止被索引',
    `ver` SMALLINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '当前版本',
    `expire` INT NOT NULL DEFAULT 0 COMMENT '-1不缓存0系统配置，其它值为自定义缓存',
    `url` VARCHAR(256) NULL COMMENT 'URL',
    PRIMARY KEY (`id`),
    INDEX `IDX_CUSERID` (`create_uid` ASC),
    INDEX `IDX_STATUS_MODEL` (`status` ASC, `model` ASC)
)  ENGINE=INNODB DEFAULT CHARACTER SET={encoding} COMMENT='cms页面'";

$tables['1.0.0'][] = "CREATE TABLE IF NOT EXISTS `{prefix}cms_page_field` (
    `page_id` INT UNSIGNED NOT NULL COMMENT '页面编号',
    `update_time` INT UNSIGNED NOT NULL COMMENT '修改时间',
    `update_uid` INT UNSIGNED NOT NULL COMMENT '作者',
    `channel` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '栏目',
    `model` VARCHAR(16) NOT NULL COMMENT '模型',
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '0删除1草稿2发布',
    `path` VARCHAR(64) NOT NULL COMMENT '虚拟路径',
    `title` VARCHAR(128) NULL COMMENT '标题',
    `title2` VARCHAR(64) NULL COMMENT '短标题',
    `template_file` VARCHAR(64) NULL COMMENT '模板文件',
    `content_file` VARCHAR(128) NULL COMMENT '内容文件地址',
    `author` VARCHAR(32) NULL COMMENT '作者',
    `source` VARCHAR(32) NULL COMMENT '来源',
    `tags` VARCHAR(128) NULL COMMENT '标签,逗号分隔',
    `flags` VARCHAR(128) NULL COMMENT '标志逗号分隔',
    `keywords` VARCHAR(128) NULL COMMENT '关键词',
    `description` VARCHAR(256) NULL COMMENT '描述',
    `image` VARCHAR(512) NULL COMMENT '插图',
    `related_pages` TEXT NULL COMMENT '相关页面',
    PRIMARY KEY (`page_id`),
    INDEX `IDX_TIME` (`update_time` ASC),
    INDEX `IDX_CHANNEL` (`channel` ASC),
    INDEX `IDX_STATUS_MODEL` (`model` ASC, `status` ASC),
    INDEX `IDX_STATUS_PATH` (`path` ASC, `status` ASC)
)  ENGINE=INNODB DEFAULT CHARACTER SET={encoding} COMMENT='cms页面字段'";

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
    `data` LONGTEXT NULL,
    PRIMARY KEY (`page_id` , `ver`)
)  ENGINE=INNODB DEFAULT CHARACTER SET={encoding} COMMENT='页面版本'";

$tables['1.0.0'][] = "CREATE TABLE IF NOT EXISTS `{prefix}cms_channel` (
    `page_id` INT UNSIGNED NOT NULL COMMENT '页面ID',
    `display_sort` SMALLINT UNSIGNED NOT NULL DEFAULT 999 COMMENT '显示排序',
    `path` VARCHAR(64) NOT NULL COMMENT '栏目路径',
    `store_path` VARCHAR(16) NULL COMMENT '页面文件存储路径',
    `url_pattern` TEXT NOT NULL COMMENT 'URL生成规则',
    `template_file` TEXT NULL COMMENT '页面模板文件',
    PRIMARY KEY (`page_id`),
    UNIQUE INDEX `UDX_PATH` (`path` ASC)
)  ENGINE=INNODB DEFAULT CHARACTER SET={encoding} COMMENT='栏目'";