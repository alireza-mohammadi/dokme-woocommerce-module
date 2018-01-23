CREATE TABLE IF NOT EXISTS `PREFIX_dokme_sync` (
      `id` INT NOT NULL AUTO_INCREMENT,
      `product_id` INT NOT NULL,
      `status` VARCHAR(100) NOT NULL DEFAULT 'ارسال نشد',
      `created_at` DATETIME NULL,
      `updated_at` DATETIME NULL,
      PRIMARY KEY (`id`),
      UNIQUE INDEX `product_id_UNIQUE` (`product_id` ASC))DEFAULT CHARSET=utf8;

INSERT IGNORE INTO `PREFIX_dokme_sync` (`product_id`,`status`)
      SELECT id AS `product_id`,'ارسال نشد' AS `status` FROM  `PREFIX_posts` WHERE post_type="product" AND post_status="publish";