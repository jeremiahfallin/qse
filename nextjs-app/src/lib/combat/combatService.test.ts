import { CombatService, type CombatShipStats } from './combatService'; // Adjust path as needed
import type { Ship } from '@prisma/client'; // For mocking originalShipData

// Helper to create mock Ship data (subset)
const mockShipData = (id: number, name: string, fighters = 100, shields = 50): Partial<Ship> => ({
  ship_id: id,
  ship_name: name,
  fighters,
  shields,
  max_shields: shields, // Assuming max_shields is same as initial shields for simplicity
  // Add other fields if CombatShipStats or its creation logic depends on them
  num_ot: 0, // default offensive turrets
  num_dt: 0, // default defensive turrets
});


describe('CombatService', () => {
  let combatService: CombatService;

  beforeEach(() => {
    combatService = new CombatService();
  });

  describe('resolveShipEngagement', () => {
    let attacker: CombatShipStats;
    let defender: CombatShipStats;

    beforeEach(() => {
      // Default attacker and defender for each test
      // Cast to Ship as we are only providing partial data needed for CombatShipStats mapping
      const attackerOriginal = mockShipData(1, 'AttackerShip', 100, 50) as Ship;
      const defenderOriginal = mockShipData(2, 'DefenderShip', 80, 40) as Ship;

      attacker = {
        id: 1, hp: 100, shields: 50, maxShields: 50, attackPower: 75, defensePower: 10,
        ownerId: 101, fleetId: 1, isDestroyed: false, originalShipData: attackerOriginal
      };
      defender = {
        id: 2, hp: 80, shields: 40, maxShields: 40, attackPower: 60, defensePower: 8,
        ownerId: 102, fleetId: 2, isDestroyed: false, originalShipData: defenderOriginal
      };
    });

    it('should correctly calculate damage and apply to defender shields then HP', async () => {
      attacker.attackPower = 50; // Approx (50 - 8 def) = 42 damage
      defender.shields = 30;
      defender.hp = 100;

      const result = await combatService.resolveShipEngagement(attacker, defender);

      expect(result.defenderDestroyed).toBe(false);
      expect(defender.shields).toBe(0); // 30 shields absorb 30 damage
      expect(defender.hp).toBe(100 - (42 - 30)); // Remaining 12 damage to HP
      expect(result.log).toContain(expect.stringContaining('shields absorb 30 damage'));
      expect(result.log).toContain(expect.stringContaining('takes 12 HP damage'));
    });

    it('should destroy defender if damage exceeds shields and HP', async () => {
      attacker.attackPower = 200; // High damage: (200 - 8 def) = 192
      defender.shields = 50;
      defender.hp = 50;

      const result = await combatService.resolveShipEngagement(attacker, defender);

      expect(result.defenderDestroyed).toBe(true);
      expect(defender.isDestroyed).toBe(true);
      expect(defender.hp).toBeLessThanOrEqual(0);
      expect(defender.shields).toBe(0); // Shields gone
      expect(result.log).toContain(expect.stringContaining('is destroyed!'));
    });

    it('should handle defender shields absorbing all damage', async () => {
      attacker.attackPower = 30; // Low damage: (30 - 8 def) = 22
      defender.shields = 50;
      defender.hp = 80;

      const result = await combatService.resolveShipEngagement(attacker, defender);

      expect(result.defenderDestroyed).toBe(false);
      expect(defender.shields).toBe(50 - 22);
      expect(defender.hp).toBe(80); // HP untouched
      expect(result.log).toContain(expect.stringContaining('shields absorb 22 damage'));
      expect(result.log).not.toContain(expect.stringContaining('HP damage'));
    });

    it('should handle counter-attack damaging the attacker', async () => {
      attacker.attackPower = 50; // Attacker: (50 - 8 def) = 42 damage to defender
      defender.attackPower = 60; // Defender: (60 - 10 def) = 50 damage to attacker
      defender.shields = 30; defender.hp = 100; // Defender will survive
      attacker.shields = 20; attacker.hp = 100; // Attacker shields

      const result = await combatService.resolveShipEngagement(attacker, defender);

      expect(result.defenderDestroyed).toBe(false);
      expect(defender.hp).toBe(100 - (42 - 30));

      expect(result.attackerDestroyed).toBe(false);
      expect(attacker.shields).toBe(0); // 20 shields absorb 20 of 50 counter-damage
      expect(attacker.hp).toBe(100 - (50 - 20)); // Remaining 30 counter-damage to HP
      expect(result.log).toContain(expect.stringContaining('counter-attacks'));
      expect(result.log).toContain(expect.stringContaining('Attacker) shields absorb 20 damage'));
      expect(result.log).toContain(expect.stringContaining('Attacker) takes 30 HP damage'));
    });

    it('should handle attacker being destroyed by counter-attack', async () => {
      attacker.attackPower = 10; // Low attack from original attacker
      defender.attackPower = 200; // High counter-attack
      attacker.shields = 10; attacker.hp = 10;

      const result = await combatService.resolveShipEngagement(attacker, defender);

      expect(result.attackerDestroyed).toBe(true);
      expect(attacker.isDestroyed).toBe(true);
      expect(result.log).toContain(expect.stringContaining('Attacker) is destroyed by counter-attack!'));
    });

    it('should do no damage if attacker is already destroyed', async () => {
      attacker.isDestroyed = true;
      const initialDefenderHp = defender.hp;
      const initialDefenderShields = defender.shields;

      const result = await combatService.resolveShipEngagement(attacker, defender);

      expect(result.damageToDefender).toBe(0);
      expect(defender.hp).toBe(initialDefenderHp);
      expect(defender.shields).toBe(initialDefenderShields);
    });
  });
});
