CREATE TABLE `sessions` (
  `id` varchar(64) NOT NULL,
  `data` text NOT NULL,
  `updated` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
