import { NextResponse } from 'next/server';
import { getServerSession } from 'next-auth/next';
import { authOptions } from '@/app/api/auth/[...nextauth]';
import { PrismaClient, Ship, Fleet, User } from '@prisma/client';
import { CombatService, CombatFleet, CombatShipStats } from '@/lib/combat/combatService'; // Adjust path as needed

const prisma = new PrismaClient();

// Helper to transform Prisma Ship to CombatShipStats
// This is a simplified mapping. A real version would incorporate ShipType base stats, upgrades, etc.
function mapShipToCombatShipStats(ship: Ship, ownerId: number): CombatShipStats {
  // For simplicity, HP is fighters, attackPower is proportional to fighters, defensePower from shields.
  // This needs to be much more detailed based on game mechanics.
  return {
    id: ship.ship_id,
    hp: ship.fighters, // Assuming fighters are the primary health pool for now
    shields: ship.shields,
    maxShields: ship.max_shields, // Needed for shield logic
    // Simplified attack/defense. Real values would come from ship_types, upgrades.
    attackPower: Math.floor(ship.fighters * 0.75) + (ship.num_ot * 50), // Example: fighters + offensive turrets
    defensePower: Math.floor(ship.shields * 0.1) + (ship.num_dt * 30),  // Example: part of shields + defensive turrets
    ownerId: ownerId, // Ensure this is correctly passed
    fleetId: ship.fleet_id ?? 0, // Ensure fleet_id is not null
    isDestroyed: ship.fighters <= 0, // Or a dedicated status field
    originalShipData: ship,
  };
}


export async function POST(request: Request) {
  try {
    const session = await getServerSession(authOptions);
    if (!session || !session.user || !session.user.id) {
      return NextResponse.json({ message: 'User not authenticated.' }, { status: 401 });
    }
    const attackerGlobalUserId = parseInt(session.user.id, 10);

    const body = await request.json();
    const { attackingFleetId, targetFleetId } = body;

    if (!attackingFleetId || !targetFleetId) {
      return NextResponse.json({ message: 'Attacking fleet ID and target fleet ID are required.' }, { status: 400 });
    }
    if (attackingFleetId === targetFleetId) {
      return NextResponse.json({ message: 'Cannot attack your own fleet.' }, { status: 400 });
    }

    // --- Fetch attacker and defender game user data ---
    const attackerGameUser = await prisma.user.findUnique({ where: { login_id: attackerGlobalUserId } });
    if (!attackerGameUser) {
      return NextResponse.json({ message: 'Attacker game user not found.' }, { status: 404 });
    }

    // --- Fetch Attacking Fleet and its Ships ---
    const attackerFleetPrisma = await prisma.fleet.findUnique({
      where: { fleet_id: attackingFleetId },
      include: { ships: true },
    });
    if (!attackerFleetPrisma || attackerFleetPrisma.login_id !== attackerGlobalUserId) {
      return NextResponse.json({ message: 'Attacking fleet not found or not owned by user.' }, { status: 403 });
    }
    if (attackerFleetPrisma.ships.length === 0) {
      return NextResponse.json({ message: 'Attacking fleet has no ships.' }, { status: 400 });
    }

    // --- Fetch Defending Fleet and its Ships ---
    const defenderFleetPrisma = await prisma.fleet.findUnique({
      where: { fleet_id: targetFleetId },
      include: {
        ships: true,
        owner: true // To get defender's game user data
      },
    });
    if (!defenderFleetPrisma) {
      return NextResponse.json({ message: 'Target fleet not found.' }, { status: 404 });
    }
    if (defenderFleetPrisma.ships.length === 0) {
      return NextResponse.json({ message: 'Target fleet has no ships to defend itself.' }, { status: 400 });
    }
    const defenderGameUser = defenderFleetPrisma.owner;
     if (!defenderGameUser) {
      return NextResponse.json({ message: 'Defender game user not found for target fleet.' }, { status: 404 });
    }


    // --- Basic Combat Validations ---
    if (attackerFleetPrisma.location !== defenderFleetPrisma.location) {
      return NextResponse.json({ message: 'Fleets are not in the same star system.' }, { status: 400 });
    }
    // TODO: Add clan/alliance checks to prevent friendly fire if applicable

    const spaceAttackTurnCostVar = await prisma.dbVar.findUnique({ where: { name: 'space_attack_turn_cost' } });
    const spaceAttackTurnCost = spaceAttackTurnCostVar ? parseInt(spaceAttackTurnCostVar.value, 10) : 2;
    if (attackerGameUser.turns < spaceAttackTurnCost) {
      return NextResponse.json({ message: `Not enough turns to attack. Requires ${spaceAttackTurnCost}.` }, { status: 400 });
    }

    // --- Prepare data for CombatService ---
    const attackerCombatFleet: CombatFleet = {
      id: attackerFleetPrisma.fleet_id,
      ownerId: attackerFleetPrisma.login_id,
      name: attackerFleetPrisma.fleet_name,
      ships: attackerFleetPrisma.ships.map(ship => mapShipToCombatShipStats(ship, attackerFleetPrisma.login_id)),
    };
    const defenderCombatFleet: CombatFleet = {
      id: defenderFleetPrisma.fleet_id,
      ownerId: defenderFleetPrisma.login_id,
      name: defenderFleetPrisma.fleet_name,
      ships: defenderFleetPrisma.ships.map(ship => mapShipToCombatShipStats(ship, defenderFleetPrisma.login_id)),
    };

    // --- Execute Combat ---
    const combatService = new CombatService();
    const combatOutcome = await combatService.initiateFleetCombat(attackerCombatFleet, defenderCombatFleet);

    // --- Persist Combat Results (Simplified) ---
    // This needs to be transactional and more robust in a real application
    await prisma.$transaction(async (tx) => {
      // Update attacker's ships
      for (const shipStat of combatOutcome.survivingShipsAttacker) {
        await tx.ship.update({
          where: { ship_id: shipStat.id },
          data: { fighters: Math.max(0, shipStat.hp), shields: shipStat.shields },
        });
      }
      const attackerDestroyedShipIds = attackerCombatFleet.ships
        .filter(s => !combatOutcome.survivingShipsAttacker.find(survivor => survivor.id === s.id))
        .map(s => s.id);
      for (const shipId of attackerDestroyedShipIds) {
        // TODO: Implement full handleShipDestruction (escape pods, etc.)
        await tx.ship.delete({ where: { ship_id: shipId }}); // Simplified: delete
      }

      // Update defender's ships
      for (const shipStat of combatOutcome.survivingShipsDefender) {
        await tx.ship.update({
          where: { ship_id: shipStat.id },
          data: { fighters: Math.max(0, shipStat.hp), shields: shipStat.shields },
        });
      }
      const defenderDestroyedShipIds = defenderCombatFleet.ships
        .filter(s => !combatOutcome.survivingShipsDefender.find(survivor => survivor.id === s.id))
        .map(s => s.id);
      for (const shipId of defenderDestroyedShipIds) {
        await tx.ship.delete({ where: { ship_id: shipId }}); // Simplified: delete
      }

      // Update user stats (simplified)
      const attackerShipsLost = attackerDestroyedShipIds.length;
      const defenderShipsLost = defenderDestroyedShipIds.length;

      await tx.user.update({
        where: { login_id: attackerGlobalUserId },
        data: {
          turns: { decrement: spaceAttackTurnCost },
          ships_killed: { increment: defenderShipsLost },
          ships_lost: { increment: attackerShipsLost },
          last_attack: Math.floor(Date.now() / 1000),
          last_attack_by: 'Fleet Combat', // Generic for now
        },
      });
      await tx.user.update({
        where: { login_id: defenderGameUser.login_id },
        data: {
          ships_killed: { increment: attackerShipsLost },
          ships_lost: { increment: defenderShipsLost },
          last_attack: Math.floor(Date.now() / 1000),
          last_attack_by: attackerGameUser.login_name,
        },
      });

      // Delete empty fleets
      if (combatOutcome.survivingShipsAttacker.length === 0) {
        await tx.fleet.delete({ where: { fleet_id: attackerFleetPrisma.fleet_id } });
      }
      if (combatOutcome.survivingShipsDefender.length === 0) {
        await tx.fleet.delete({ where: { fleet_id: defenderFleetPrisma.fleet_id } });
      }

      // TODO: Create combat log entries in a new CombatLog table
    });

    return NextResponse.json({
      message: "Combat resolved.",
      outcome: combatOutcome,
    }, { status: 200 });

  } catch (error) {
    console.error('Fleet attack error:', error);
    return NextResponse.json({ message: 'An error occurred during fleet combat.' }, { status: 500 });
  } finally {
    await prisma.$disconnect();
  }
}
