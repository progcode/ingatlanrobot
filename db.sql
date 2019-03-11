CREATE TABLE `property` (
  `id` int(100) NOT NULL,
  `portal` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `hash` varchar(255) NOT NULL,
  `synced` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;