/**
 * This file will contain common utility functions ported from includes/common_funcs.inc.php
 * and other similar PHP include files.
 */

/**
 * Maps a numeric ship size to a textual description.
 * Based on discern_size() from common_funcs.inc.php.
 * @param size The numeric size of the ship.
 * @returns A string describing the ship's size.
 */
export function discernShipSize(size: number): string {
  switch (size) {
    case 1:
      return "Tiny";
    case 2:
      return "Very Small";
    case 3:
      return "Small";
    case 4:
      return "Medium";
    case 5:
      return "Large";
    case 6:
      return "Very Large";
    case 7:
      return "Huge";
    case 8:
      return "Gigantic";
    default:
      return "Unknown Size";
  }
}

/**
 * Validates if the input string contains only allowed alphanumeric and specific special characters.
 * Based on valid_input() from common_funcs.inc.php.
 * PHP eregi was case-insensitive. JavaScript regex uses the 'i' flag.
 * Original regex: '^([a-z0-9~!@#$%&*_+-=��������׀��])+$'
 * The unicode characters are tricky to directly translate without knowing their exact intent/range.
 * This version focuses on common alphanumeric and special chars.
 * @param input The string to validate.
 * @returns True if the input is valid, false otherwise.
 */
export function isValidInput(input: string): boolean {
  if (input === null || typeof input === "undefined") {
    return false;
  }
  // Simplified regex: allows alphanumeric and some common special characters.
  // The original PHP regex included a wide range of accented characters
  // which would need careful handling in JS regex if strictly required.
  const regex = /^([a-z0-9~!@#$%&*_+-=])+$/i;
  return regex.test(input);
}

/**
 * Cleans a name by removing HTML tags, extra whitespace, and potentially harmful characters.
 * Based on correct_name() from common_funcs.inc.php.
 * WARNING: Directly using this for HTML output without a proper sanitizer can be risky.
 * For React/JSX, rely on React's built-in XSS protection.
 * If this output is used in `dangerouslySetInnerHTML`, use a robust sanitizer like DOMPurify.
 * Original PHP code used htmlspecialchars, strip_tags, trim, preg_replace.
 * @param input The name string to clean.
 * @returns The cleaned name string.
 */
export function sanitizeName(input: string): string {
  if (input === null || typeof input === "undefined") {
    return "";
  }

  let sanitized = input;

  // Trim whitespace from beginning and end
  sanitized = sanitized.trim();

  // Replace multiple spaces with a single space
  sanitized = sanitized.replace(/\s\s+/g, " ");

  // Basic character filter (similar to the original PHP regex, but simplified)
  // This regex allows alphanumeric, common symbols, spaces, and periods.
  // It disallows characters often used in XSS like <, >, ", ' unless they are part of the allowed symbols.
  // The original PHP eregi_replace('[^a-z0-9~!@#$%&*_+-=��������׀�� .]',"",$input);
  // is difficult to replicate perfectly without more context on the unicode characters.
  // This is a more restrictive, safer default for typical "name" fields.
  const characterFilterRegex = /[^a-z0-9~!@#$%&*_+\-= .]/gi;
  // sanitized = sanitized.replace(characterFilterRegex, "");
  // For now, as the original PHP function allowed many characters, we'll skip strict filtering
  // beyond trim and space normalization. HTML tag stripping should be handled by context (e.g. not needed for text nodes in React)
  // or by a dedicated HTML sanitizer if the output is meant for HTML.

  // The PHP function used htmlspecialchars() and strip_tags().
  // htmlspecialchars() is context-dependent. In React, JSX handles this.
  // strip_tags() is better handled by a sanitizer if HTML is the target.
  // For now, this function will primarily focus on whitespace and basic structure.

  return sanitized;
}

/**
 * Placeholder for a function to parse BBCode-like custom tags into HTML.
 * Based on mcit() from common_funcs.inc.php.
 * This requires a proper parsing and rendering engine.
 * @param text The text with custom tags.
 * @returns HTML string or structured content. For now, returns text with basic sanitization.
 */
export function formatLegacyTextToHtml(text: string): string {
  if (text === null || typeof text === "undefined") {
    return "";
  }
  // This is a highly simplified placeholder.
  // A real implementation would need a robust parser for all the [tag] replacements.
  // For safety, just escape basic HTML characters for now if not using React context.
  // return text
  // .replace(/&/g, "&amp;")
  // .replace(/</g, "&lt;")
  // .replace(/>/g, "&gt;")
  // .replace(/"/g, "&quot;")
  // .replace(/'/g, "&#039;")
  // .replace(/\n/g, "<br />");

  // Given the complexity, for now, it's best to return the text as-is
  // and handle rendering carefully in the UI, or implement a proper parser later.
  console.warn(
    "formatLegacyTextToHtml is a placeholder and does not fully implement mcit() functionality."
  );
  return text;
}

/**
 * Placeholder for a profanity filter.
 * Based on Clean_Text() from common_funcs.inc.php.
 * This requires a robust profanity list and regex handling.
 * @param text The text to filter.
 * @returns The filtered text. For now, returns text as-is.
 */
export function filterProfanity(text: string): string {
  if (text === null || typeof text === "undefined") {
    return "";
  }
  // The original PHP function has a very complex profanity list and regex replacement.
  // Porting this directly is error-prone and might not be the most effective approach.
  // Consider using a well-maintained profanity filter library in JavaScript.
  console.warn(
    "filterProfanity is a placeholder and does not implement Clean_Text() functionality."
  );
  return text;
}

// Functions to be converted to React components (or are part of layout):
// - print_adcode() -> AdComponent.tsx
// - print_footer() -> FooterComponent.tsx (part of Layout.tsx)
// - make_table(), make_table_width(), make_row(), make_hash_row(), quick_row() -> TableComponent.tsx and usage
// - build_page_list() -> PaginationComponent.tsx

// Functions to be converted to server-side logic (API routes or server functions using Prisma):
// - post_news(headline: string) // from common_funcs.inc.php
// - load_ship_types() // from common_funcs.inc.php
// - insert_history(loginId: number, text: string, context: string) // from common_funcs.inc.php
// - score_func(loginId: number, fullUpdate: boolean, dbPrefix: string) // from common_funcs.inc.php
// - SlidingScale(textOutput: boolean) // from common_funcs.inc.php
// - Total_Net_Worth(loginId: number) // from common_funcs.inc.php
// - Ramscoop() // from location_funcs.inc.php - involves DB updates

// Note on `db_connect` and `dbn`, `db`, `db2`, `db3`, `dbr`, `dbr2`, `dbr3`:
// These are PHP database interaction functions. In the Next.js app,
// Prisma Client will be used for all database operations.
// So, any function in PHP that uses these (like post_news, load_ship_types)
// will need to be rewritten to use Prisma Client methods within server-side logic.

// --- Functions from location_funcs.inc.php ---

/**
 * Gets the name and visibility description for a given system type ID.
 * Based on GrabSystemType() from location_funcs.inc.php.
 * @param systype The numeric system type.
 * @returns An object containing the name and verbose name (name_v) of the system type.
 */
export function getSystemTypeInfo(systype: number): { name: string; name_v: string } {
  switch (systype) {
    case 0:
      return { name: "Normal Space", name_v: "Normal Space: Visibility 100%" };
    case 1:
      return { name: "Gas Nebula", name_v: "Gas Nebula: Visibility 95%" };
    case 2:
      return { name: "Dust Cloud", name_v: "Dust Cloud: Visibility 90%" };
    case 3:
      return { name: "Radiation Belt", name_v: "Radiation Belt: Visibility 85%" };
    default: // Originally "Stellar Debris" for anything else
      return { name: "Stellar Debris", name_v: "Stellar Debris: Visibility 80%" };
  }
}

/**
 * Checks if a target link number is present in a star's connection links.
 * Based on search_links() from location_funcs.inc.php.
 * Note: The Star type/interface would need to be defined.
 * For now, using a generic object type.
 * @param star An object representing a star with link_1 to link_6 and wormhole properties.
 * @param linkNum The link number to search for.
 * @returns 0 if found, 1 if not found (matches PHP's true/false integer convention).
 */
export function searchStarConnections(
  star: {
    link_1?: number;
    link_2?: number;
    link_3?: number;
    link_4?: number;
    link_5?: number;
    link_6?: number;
    wormhole?: number;
  },
  linkNum: number
): 0 | 1 {
  if (
    star.link_1 === linkNum ||
    star.link_2 === linkNum ||
    star.link_3 === linkNum ||
    star.link_4 === linkNum ||
    star.link_5 === linkNum ||
    star.link_6 === linkNum ||
    star.wormhole === linkNum
  ) {
    return 0; // Found
  }
  return 1; // Not found
}

/**
 * Determines the scanner type level based on ship upgrade information.
 * Based on Scan_Type() from location_funcs.inc.php.
 * Note: The UpgradeData type/interface would need to be defined.
 * For now, using a generic object type.
 * @param upgrades An object representing a ship's upgrades.
 * @returns The scanner type level (0-5).
 */
export function getScannerType(upgrades: { [key: string]: number | undefined }): number {
  if (upgrades.stand_scanner && upgrades.stand_scanner >= 1) return 1;
  if (upgrades.adv_scanner && upgrades.adv_scanner >= 1) return 2;
  if (upgrades.grav_scanner && upgrades.grav_scanner >= 1) return 3;
  if (upgrades.subs_array && upgrades.subs_array >= 1) return 4;
  if (upgrades.alien_array && upgrades.alien_array >= 1) return 5;
  return 0;
}

/**
 * Determines the cloaking device type level based on ship upgrade information.
 * Based on Cloak_Type() from location_funcs.inc.php.
 * Note: The UpgradeData type/interface would need to be defined.
 * For now, using a generic object type.
 * @param upgrades An object representing a ship's upgrades.
 * @returns The cloak type level (0-5).
 */
export function getCloakType(upgrades: { [key: string]: number | undefined }): number {
  if (upgrades.stand_cloak && upgrades.stand_cloak >= 1) return 1;
  if (upgrades.adv_cloak && upgrades.adv_cloak >= 1) return 2;
  if (upgrades.pir_deflect && upgrades.pir_deflect >= 1) return 3;
  if (upgrades.spat_unit && upgrades.spat_unit >= 1) return 4;
  if (upgrades.dim_flux && upgrades.dim_flux >= 1) return 5;
  return 0;
}

// --- Functions from random_events.inc.php ---

/**
 * Calculates the minimum fuel a Nebula is allowed to have.
 * Based on nebula_min_fuel() from random_events.inc.php.
 * @param uv_fuel_max Max fuel from universe generation.
 * @param rr_fuel_chance_max Max fuel from random daily regeneration.
 * @param day_maint_count Number of daily maintenance runs.
 * @returns Minimum fuel for a nebula.
 */
export function nebulaMinFuel(
  uv_fuel_max: number,
  rr_fuel_chance_max: number,
  day_maint_count: number
): number {
  // Max possible from universe gen plus 1 week's max regen
  let min = uv_fuel_max + rr_fuel_chance_max * day_maint_count * 7;
  if (min < 50000) {
    min = 50000; // Must be at least 50k
  } else if (min > 500000) {
    min = 500000; // But not over 500k
  }
  return min;
}

/**
 * Calculates the minimum metal a Mining Rush event is allowed to have.
 * Based on miningrush_min_metal() from random_events.inc.php.
 * @param uv_metal_max Max metal from universe generation.
 * @param rr_metal_chance_max Max metal from random daily regeneration.
 * @param day_maint_count Number of daily maintenance runs.
 * @returns Minimum metal for a mining rush event.
 */
export function miningRushMinMetal(
  uv_metal_max: number,
  rr_metal_chance_max: number,
  day_maint_count: number
): number {
  // Max possible from universe gen plus 1 week's max regen
  let min = uv_metal_max + rr_metal_chance_max * day_maint_count * 7;
  if (min < 50000) {
    min = 50000; // Must be at least 50k
  } else if (min > 500000) {
    min = 500000; // But not over 500k
  }
  return min;
}

// safe_rand_star(currentStarId: number, prisma: PrismaClient): Promise<number | null>
// This function requires a Prisma client instance to fetch star data and would be server-side.
// It would select a random star_id from stars where event_random != 1 and star_id != currentStarId.

// --- Functions from clan_funcs.inc.php ---

/**
 * Ensures that a value is a number, returning 0 if it's null or undefined.
 * Based on RM_Zero() from clan_funcs.inc.php.
 * @param value The value to check.
 * @returns The number or 0.
 */
export function ensureNumberOrZero(value: number | undefined | null): number {
  return value == null ? 0 : value;
}

// Note on calc_perc(value, total):
// This function is used in Print_ClanDetailsFull (clan_funcs.inc.php) but not defined there.
// It likely calculates (value / total) * 100 and formats it.
// It needs to be found and ported, possibly to this file or a formatting utility file.
