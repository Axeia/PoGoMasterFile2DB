/**
 * Left out FOREIGN KEY (evolves_from) REFERENCES pokemons (name),
 * As later generations added baby forms for pokémon that were already
 * in the game. 
 * So even though Pikachu evolves from Pichu, 
 * Pikachu is a gen 1 pokémon and thus comes first without Pichu being known yet.
 * - Reversing the logic is not an option as the value wouldn't be unique, e.g. 
 * Jolteon has always evolved from Eevee, But Eevee doesn't always evolve into Joleon
 * (also into Flareon, Vaporeon, Leafeon etc)
 *
 *
 * The female_ratio field isn't properly normalised as it's a calculated value
 * but it's added for convience
 * The following should net us the same value as the female_ratio field:
 *
 * SELECT CASE WHEN male_ratio IS NOT NULL 
 *             THEN 1 - male_ratio 
 *             ELSE null
 *              END 
               AS female_ratio
 */
CREATE TABLE pokemons
(
  name              VARCHAR(255)  COLLATE utf8mb4_unicode_ci NOT NULL,
  pokedex_number    INT           NOT NULL,
  type1             VARCHAR(255)  COLLATE utf8mb4_unicode_ci NOT NULL,
  type2             VARCHAR(255)  COLLATE utf8mb4_unicode_ci NULL,
  base_attack       INT           NOT NULL,
  base_defense      INT           NOT NULL,
  base_stamina      INT           NOT NULL,
  base_capture_rate DECIMAL(7, 3) NULL,
  base_flee_rate    DECIMAL(7, 3) NULL,
  evolves_from      VARCHAR(255)  COLLATE utf8mb4_unicode_ci NULL,
  buddy_distance    INT           NOT NULL,
  candy_to_evolve   INT           NULL,
  released          TINYINT(1)    NOT NULL,
  shiny             TINYINT(1)    NOT NULL,
  male_ratio        DECIMAL(4, 3) NULL,
  female_ratio      DECIMAL(4, 3) NULL,

  PRIMARY KEY (name),
  FOREIGN KEY (type1) REFERENCES types (typing),
  FOREIGN KEY (type2) REFERENCES types (typing)
);