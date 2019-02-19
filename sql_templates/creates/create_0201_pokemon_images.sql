/**
 * All the image names with some additional information
 * about the pictured pokémon.
 *
 * You could possibly consider all fields beside name and file_name
 * to be calculated values as they are but the logic behind it is quite
 * complicated so we're probably better off simply doing this once in PHP during
 * the generation phase rather than every single time we want to queue this table.
 */
CREATE TABLE pokemon_images
(
  pokemons_name   VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  male            TINYINT(1)   NOT NULL,
  female          TINYINT(1)   NOT NULL,
  alt_form        CHAR(2)      NULL,
  shiny           TINYINT(1)   NOT NULL,
  file_name       VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,

  PRIMARY KEY(file_name),
  FOREIGN KEY(pokemons_name) REFERENCES pokemons (name)
)