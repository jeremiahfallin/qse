/**
 * Utility functions related to planets, ported from includes/planet_funcs.inc.php.
 */

// Assuming types are defined elsewhere, e.g., in a prisma-generated types or custom types file.
// For now, using basic interfaces.
interface PlanetData {
  colon: number;
  alloc_fight: number;
  alloc_elect: number;
  alloc_organ: number;
  // other planet fields...
  planet_name?: string; // Used in warnings
  fighters?: number; // Used in warnings
  fighter_set?: number; // Used in warnings (0 for passive, 1 for hostile, 2 for super hostile)
}

interface UserData {
  login_id: number;
  signed_up: number; // Timestamp
  // other user fields...
}

/**
 * Calculates the number of idle colonists on a planet.
 * Based on idle_colonists() from planet_funcs.inc.php.
 * @param planet The planet data object.
 * @returns The number of idle colonists.
 */
export function calculateIdleColonists(planet: PlanetData): number {
  if (!planet) return 0;
  return (planet.colon || 0) - (planet.alloc_fight || 0) - (planet.alloc_elect || 0) - (planet.alloc_organ || 0);
}

/**
 * Checks if a user meets certain conditions to perform actions like transferring items/resources
 * involving a planet they don't own.
 * Based on conditions() from planet_funcs.inc.php.
 * @param user The user data object.
 * @param planet The planet data object.
 * @param minTransferTimeDays The minimum number of days a user must be registered before certain actions are allowed.
 *                            This replaces the global $min_before_transfer.
 * @returns True if conditions are met (action allowed), false otherwise.
 */
export function canUserPerformPlanetTransfer(
  user: UserData,
  planet: PlanetData & { owner_id: number; fighters: number },
  minTransferTimeDays: number
): boolean {
  if (!user || !planet) return false;

  // Condition: user signed up longer ago than min_before_transfer days,
  // AND (user is not the planet owner AND planet has fighters)
  // AND user is not admin (login_id 1)
  // The original PHP returned 1 if conditions were met for RESTRICTION (meaning action NOT allowed).
  // This function will return true if action IS allowed (conditions for restriction are NOT met).

  const isRestricted =
    user.signed_up > (Math.floor(Date.now() / 1000) - (minTransferTimeDays * 86400)) &&
    user.login_id !== planet.owner_id &&
    planet.fighters !== 0 &&
    user.login_id !== 1;

  return !isRestricted;
}

/**
 * Formats a warning message for an illegal planet attack attempt.
 * Based on illegal_planet_attack_warning() from planet_funcs.inc.php.
 * @param planetAttacked The planet data object for the planet that was attacked.
 * @returns A string message.
 */
export function formatIllegalPlanetAttackWarning(planetAttacked: PlanetData): string {
  return (
    `You may not attack planet ${planetAttacked.planet_name || 'Unknown Planet'} while other planets ` +
    `in this system have more hostile fighters.<br />\n` +
    `Please ensure you are only using the provided interface to initiate your attacks.<br />\n`
  );
}

/**
 * Formats a warning message for an illegal planet claim attempt.
 * Based on illegal_planet_claim_warning() from planet_funcs.inc.php.
 * @param planetClaimed The planet data object for the planet that was attempted to be claimed.
 * @returns A string message.
 */
export function formatIllegalPlanetClaimWarning(planetClaimed: PlanetData): string {
  return (
    `You may not claim planet ${planetClaimed.planet_name || 'Unknown Planet'} while other planets ` +
    `in this system have hostile fighters.<br />\n` +
    `Please ensure you are only using the provided interface to initiate your attacks.<br />\n`
  );
}

// Note on get_planet(): This PHP function fetched global $planet data.
// In Next.js, components or API routes will fetch planet data using Prisma directly when needed.

// Note on report_illegal_planet_attack() and report_illegal_planet_claim():
// These functions called a send_alert() function. This implies a server-side admin notification system.
// This would be reimplemented as a server-side logging/alerting mechanism.

// Note on do_damage(): This is complex combat logic.
// It will be part of an API route handling attacks (e.g., /api/combat/attack-planet or a general /api/combat/resolve-attack).
// All database operations within it will use Prisma.
// See apiRouteNotes.ts for more details.
