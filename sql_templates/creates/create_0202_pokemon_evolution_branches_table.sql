
/*
 * Contains the evolution branches
*/
CREATE TABLE evolution_branches
(
  pokemons_name   VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  evolved_name    VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL, --could be a special form
  evolution_item  VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL, --Bag_Sinnoh_Stone_Sprite.png
  buddy_distance  DECIMAL(4,1) NULL, --20.0 for feebas
  gender          TINYINT(1) NULL
)