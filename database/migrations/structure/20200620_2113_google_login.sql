ALTER TABLE
  `users`
ADD
  `google_sub` varchar(255) CHARACTER SET ascii COLLATE ascii_bin NULL DEFAULT NULL,
ADD
  UNIQUE `google_sub` (`google_sub`),
MODIFY
  `password` varchar(60) NULL DEFAULT NULL;
