# 内容管理系统 - CMS

**特性**：

1. 防CC机制
2. 防雪崩的缓存机制
3. 页面版本控制
4. 审核-发布控制
5. 自定义内容模型功能
6. 模板页支持（动态或静态）
7. 牛逼的`cts`与`ctsp`模板标签

## 缓存支持
1. 在`bootstrap.php`文件中将`APP_MODE`设为`pro`。
2. 修改`conf/cache_config.php`配置缓存服务器。
3. 在输出页面内容前定义`EXPIRE`常量值为缓存时间即可(单位秒)。

### 防雪崩机制
在`bootstrap.php`文件中将`ANTI_AVALANCHE`设为`true`即可开启（需要redis支持）。

## 防CC支持

1. 在`bootstrap.php`文件中将`ANTI_CC`设为单位时间内同一IP可访问次数开启防CC机制。
   * `ANTI_CC` 格式有两种：
      1. 直接配置访问次数，格式为:`100`。表示60秒内最多访问100次。
      2. 同时配置访问次数与单位时间,格式为:`60/120`。表示120秒内最多访问100次。
   * 通过定义`ANTI_CC_WHITE`常量设置白名单，以逗号分隔.
      
2. 在`conf/ccredis_config.php`配置供防CC机制工作的redis。
   ```php
   return ['host'=>'localhost','port'=>6379,'db'=>0,'auth'=>'','timeout'=>5];
   ```