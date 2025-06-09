/**
 * Utility functions related to ships, ported from various PHP include files.
 */
import type { PrismaClient, UpgradeUnit } from '@prisma/client'; // Assuming Prisma client types

/**
 * Checks if a ship has any scanner upgrade installed.
 * Based on CheckShip_ForScanner() from upgrade_funcs.inc.php.
 * @param shipId The ID of the ship.
 * @param prisma Prisma client instance.
 * @returns True if a scanner is installed, false otherwise.
 */
export async function hasScanner(shipId: number, prisma: PrismaClient): Promise<boolean> {
  const upgrades = await prisma.upgradeUnit.findUnique({
    where: { ship_id: shipId },
    select: {
      stand_scanner: true,
      adv_scanner: true,
      grav_scanner: true,
      subs_array: true,
      alien_array: true,
    },
  });

  if (!upgrades) return false;

  return (
    (upgrades.stand_scanner ?? 0) >= 1 ||
    (upgrades.adv_scanner ?? 0) >= 1 ||
    (upgrades.grav_scanner ?? 0) >= 1 ||
    (upgrades.subs_array ?? 0) >= 1 ||
    (upgrades.alien_array ?? 0) >= 1
  );
}

/**
 * Checks if a ship has any cloaking device upgrade installed.
 * Based on CheckShip_ForCloak() from upgrade_funcs.inc.php.
 * @param shipId The ID of the ship.
 * @param prisma Prisma client instance.
 * @returns True if a cloaking device is installed, false otherwise.
 */
export async function hasCloak(shipId: number, prisma: PrismaClient): Promise<boolean> {
  const upgrades = await prisma.upgradeUnit.findUnique({
    where: { ship_id: shipId },
    select: {
      stand_cloak: true,
      adv_cloak: true,
      pir_deflect: true,
      spat_unit: true,
      dim_flux: true,
    },
  });

  if (!upgrades) return false;

  return (
    (upgrades.stand_cloak ?? 0) >= 1 ||
    (upgrades.adv_cloak ?? 0) >= 1 ||
    (upgrades.pir_deflect ?? 0) >= 1 ||
    (upgrades.spat_unit ?? 0) >= 1 ||
    (upgrades.dim_flux ?? 0) >= 1
  );
}

/**
 * Gets the count of a specific upgrade installed on a ship.
 * Based on CheckShipCount_ForUpgrade() from upgrade_funcs.inc.php.
 * @param shipId The ID of the ship.
 * @param upgradeSqlName The SQL column name of the upgrade in the UpgradeUnit table.
 * @param prisma Prisma client instance.
 * @returns The number of specified upgrades installed.
 */
export async function getShipUpgradeCount(
  shipId: number,
  upgradeSqlName: keyof UpgradeUnit, // Ensures upgradeSqlName is a valid key of UpgradeUnit
  prisma: PrismaClient
): Promise<number> {
  const upgradeUnit = await prisma.upgradeUnit.findUnique({
    where: { ship_id: shipId },
    select: { [upgradeSqlName]: true },
  });

  if (!upgradeUnit) return 0;

  const count = upgradeUnit[upgradeSqlName];
  return typeof count === 'number' ? count : 0;
}
