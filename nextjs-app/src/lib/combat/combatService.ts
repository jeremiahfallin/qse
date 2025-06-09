import type { Ship, User, Fleet, PrismaClient } from '@prisma/client';
// Utility functions for bonuses - these would eventually use Prisma
// For now, placeholders.
// import { getShipAttackBonus, getShipDefenseBonus } from '@/utils/upgradeUtils';

// --- Interfaces for Combat Simulation ---
export interface CombatShipStats {
  id: number;
  hp: number; // Represents fighters for now, or a dedicated health pool
  shields: number;
  maxShields: number;
  attackPower: number; // Base attack (e.g., from fighters or ship type)
  defensePower: number; // Base defense (e.g., from ship type or armor)
  ownerId: number;
  fleetId: number;
  isDestroyed: boolean;
  // Add other relevant stats: agility, weapon types, specific resistances etc. for richer combat
  // For initial pass, keeping it simpler.
  originalShipData: Ship; // To access other fields like name, class_name easily
}

export interface CombatFleet {
  id: number;
  ownerId: number;
  ships: CombatShipStats[];
  name: string;
}

export interface EngagementResult {
  attackerShipId: number;
  defenderShipId: number;
  damageToAttacker: number;
  damageToDefender: number;
  defenderDestroyed: boolean;
  attackerDestroyed: boolean; // If defender had a counter-attack in the same engagement
  log: string[]; // Combat log for this specific engagement
}

export interface CombatRoundResult {
  engagements: EngagementResult[];
  roundLog: string[];
}

export interface CombatOutcome {
  winnerFleetId?: number | null; // null if draw or mutual destruction
  survivingShipsAttacker: CombatShipStats[];
  survivingShipsDefender: CombatShipStats[];
  combatLog: string[]; // Overall combat log
  totalRounds: number;
}

// --- Combat Service ---

export class CombatService {
  // In a real scenario, PrismaClient would be passed to methods needing DB access,
  // or the service instance would be created per request with a PrismaClient instance.
  // For this simplified first pass, calculations are mostly in-memory based on input.

  /**
   * Simulates a single engagement between two ships.
   * Returns damage dealt and status.
   * This is a simplified version of Generate_Attack & Inflict_ShipDamage logic.
   */
  async resolveShipEngagement(
    attacker: CombatShipStats,
    defender: CombatShipStats,
    // attackerUser: User, // For context like bonuses from user stats (not used in this simplified version)
    // defenderUser: User,
    // prisma: PrismaClient // If fetching live upgrade bonuses per hit
  ): Promise<EngagementResult> {
    const log: string[] = [];
    let damageToDefender = 0;
    let damageToAttacker = 0; // Assuming counter-attack is part of this engagement resolution

    if (attacker.isDestroyed || defender.isDestroyed) {
      return { attackerShipId: attacker.id, defenderShipId: defender.id, damageToAttacker:0, damageToDefender:0, attackerDestroyed: attacker.isDestroyed, defenderDestroyed: defender.isDestroyed, log: ["One ship already destroyed."]};
    }

    // Attacker's turn
    // Simplified damage: base attack +/- randomness, then apply to shields, then HP
    // TODO: Incorporate getShipAttackBonus(attacker.id, prisma) and getShipDefenseBonus(defender.id, prisma)
    const attackerRawDamage = Math.max(0, attacker.attackPower + Math.floor(Math.random() * (attacker.attackPower * 0.3) - (attacker.attackPower * 0.15))); // +/- 15% randomness like original
    log.push(`${attacker.originalShipData.ship_name} (Attacker) attacks with potential ${attackerRawDamage} damage.`);

    let defenderEffectiveDefense = defender.defensePower; // TODO: Add defense bonuses
    let damageAfterDefense = Math.max(0, attackerRawDamage - defenderEffectiveDefense);

    let damageToDefenderShields = 0;
    if (defender.shields > 0) {
      damageToDefenderShields = Math.min(defender.shields, damageAfterDefense);
      defender.shields -= damageToDefenderShields;
      damageAfterDefense -= damageToDefenderShields;
      log.push(`${defender.originalShipData.ship_name} (Defender) shields absorb ${damageToDefenderShields} damage. Shields left: ${defender.shields}`);
    }

    damageToDefender = damageAfterDefense;
    if (damageToDefender > 0) {
      defender.hp -= damageToDefender;
      log.push(`${defender.originalShipData.ship_name} (Defender) takes ${damageToDefender} HP damage. HP left: ${Math.max(0, defender.hp)}`);
    }

    if (defender.hp <= 0) {
      defender.isDestroyed = true;
      log.push(`${defender.originalShipData.ship_name} (Defender) is destroyed!`);
    }

    // Defender's counter-attack (simplified, happens immediately if defender not destroyed)
    if (!defender.isDestroyed) {
      const defenderCounterRawDamage = Math.max(0, defender.attackPower + Math.floor(Math.random() * (defender.attackPower * 0.3) - (defender.attackPower * 0.15)));
      log.push(`${defender.originalShipData.ship_name} (Defender) counter-attacks with potential ${defenderCounterRawDamage} damage.`);

      let attackerEffectiveDefense = attacker.defensePower; // TODO: Add defense bonuses
      let counterDamageAfterDefense = Math.max(0, defenderCounterRawDamage - attackerEffectiveDefense);

      let damageToAttackerShields = 0;
      if (attacker.shields > 0) {
        damageToAttackerShields = Math.min(attacker.shields, counterDamageAfterDefense);
        attacker.shields -= damageToAttackerShields;
        counterDamageAfterDefense -= damageToAttackerShields;
        log.push(`${attacker.originalShipData.ship_name} (Attacker) shields absorb ${damageToAttackerShields} damage. Shields left: ${attacker.shields}`);
      }

      damageToAttacker = counterDamageAfterDefense;
      if (damageToAttacker > 0) {
        attacker.hp -= damageToAttacker;
        log.push(`${attacker.originalShipData.ship_name} (Attacker) takes ${damageToAttacker} HP damage. HP left: ${Math.max(0, attacker.hp)}`);
      }

      if (attacker.hp <= 0) {
        attacker.isDestroyed = true;
        log.push(`${attacker.originalShipData.ship_name} (Attacker) is destroyed by counter-attack!`);
      }
    }

    return {
      attackerShipId: attacker.id,
      defenderShipId: defender.id,
      damageToAttacker,
      damageToDefender: damageToDefenderShields + damageToDefender, // Total including shield
      defenderDestroyed: defender.isDestroyed,
      attackerDestroyed: attacker.isDestroyed,
      log,
    };
  }

  /**
   * Simulates fleet vs. fleet combat.
   * Returns the overall outcome and logs.
   */
  async initiateFleetCombat(
    attackerFleetInput: CombatFleet,
    defenderFleetInput: CombatFleet,
    // prisma: PrismaClient // For fetching full ship stats, user stats if needed during rounds
  ): Promise<CombatOutcome> {
    const combatLog: string[] = [];
    let totalRounds = 0;
    const maxRounds = 50; // Prevent infinite loops

    // Deep clone ship arrays to modify them during simulation
    const attackerFleet: CombatFleet = { ...attackerFleetInput, ships: attackerFleetInput.ships.map(s => ({...s})) };
    const defenderFleet: CombatFleet = { ...defenderFleetInput, ships: defenderFleetInput.ships.map(s => ({...s})) };

    combatLog.push(`Combat initiated between Fleet ${attackerFleet.name} (Owner: ${attackerFleet.ownerId}) and Fleet ${defenderFleet.name} (Owner: ${defenderFleet.ownerId})`);

    while (totalRounds < maxRounds) {
      totalRounds++;
      combatLog.push(`\n--- Round ${totalRounds} ---`);

      const activeAttackerShips = attackerFleet.ships.filter(s => !s.isDestroyed);
      const activeDefenderShips = defenderFleet.ships.filter(s => !s.isDestroyed);

      if (activeAttackerShips.length === 0 || activeDefenderShips.length === 0) {
        combatLog.push("One fleet has no active ships remaining.");
        break;
      }

      // Targeting: Strongest vs Strongest (based on current HP which represents fighters)
      activeAttackerShips.sort((a, b) => b.hp - a.hp);
      activeDefenderShips.sort((a, b) => b.hp - a.hp);

      const attackerShip = activeAttackerShips[0];
      const defenderShip = activeDefenderShips[0];

      combatLog.push(`${attackerShip.originalShipData.ship_name} engages ${defenderShip.originalShipData.ship_name}.`);

      const engagementResult = await this.resolveShipEngagement(attackerShip, defenderShip);
      combatLog.push(...engagementResult.log.map(l => `  ${l}`));

      if (engagementResult.defenderDestroyed) {
        const destroyedShip = defenderFleet.ships.find(s => s.id === engagementResult.defenderShipId);
        if(destroyedShip) destroyedShip.isDestroyed = true;
      }
      if (engagementResult.attackerDestroyed) {
        const destroyedShip = attackerFleet.ships.find(s => s.id === engagementResult.attackerShipId);
        if(destroyedShip) destroyedShip.isDestroyed = true;
      }
    }

    const survivingAttackerShips = attackerFleet.ships.filter(s => !s.isDestroyed);
    const survivingDefenderShips = defenderFleet.ships.filter(s => !s.isDestroyed);
    let winnerFleetId: number | null = null;

    if (survivingAttackerShips.length > 0 && survivingDefenderShips.length === 0) {
      winnerFleetId = attackerFleet.id;
      combatLog.push(`\n--- Combat End: Fleet ${attackerFleet.name} is victorious! ---`);
    } else if (survivingDefenderShips.length > 0 && survivingAttackerShips.length === 0) {
      winnerFleetId = defenderFleet.id;
      combatLog.push(`\n--- Combat End: Fleet ${defenderFleet.name} is victorious! ---`);
    } else if (survivingAttackerShips.length === 0 && survivingDefenderShips.length === 0) {
      combatLog.push("\n--- Combat End: Mutual Destruction! ---");
    } else {
       combatLog.push(`\n--- Combat End: Max rounds (${maxRounds}) reached. Stalemate or ongoing. ---`);
       // Could determine winner by remaining HP or ship count here if desired
       if (survivingAttackerShips.length > survivingDefenderShips.length) winnerFleetId = attackerFleet.id;
       else if (survivingDefenderShips.length > survivingAttackerShips.length) winnerFleetId = defenderFleet.id;
    }

    return {
      winnerFleetId,
      survivingShipsAttacker: survivingAttackerShips,
      survivingShipsDefender: survivingDefenderShips,
      combatLog,
      totalRounds,
    };
  }

  // handleShipDestruction, applyCombatResultsToDB would be other methods here.
  // For now, they are implicitly part of the API route that calls initiateFleetCombat.
}
