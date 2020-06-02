ALTER TABLE
  `maps`
MODIFY
  `bound_south_lat` decimal(9, 7) NOT NULL,
MODIFY
  `bound_west_lng` decimal(10, 7) NOT NULL,
MODIFY
  `bound_north_lat` decimal(9, 7) NOT NULL,
MODIFY
  `bound_east_lng` decimal(10, 7) NOT NULL;

ALTER TABLE
  `places`
MODIFY
  `lat` decimal(9, 7) NOT NULL,
MODIFY
  `lng` decimal(10, 7) NOT NULL;
