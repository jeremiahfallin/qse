import { NextResponse } from 'next/server';
import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

interface PlayerStatsParams {
  params: {
    userId: string;
  };
}

export async function GET(request: Request, { params }: PlayerStatsParams) {
  try {
    const targetUserId = parseInt(params.userId, 10);

    if (isNaN(targetUserId)) {
      return NextResponse.json({ message: 'Invalid user ID.' }, { status: 400 });
    }

    // Fetch game-specific user data for statistics
    const gameUserStats = await prisma.user.findUnique({
      where: { login_id: targetUserId },
      select: {
        login_id: true,
        login_name: true, // For identification
        ships_killed: true,
        ships_lost: true,
        ships_killed_points: true,
        ships_lost_points: true,
        fighters_killed: true,
        fighters_lost: true,
        score: true,
        turns_run: true,
        bounty: true,
        // Include any other fields considered "stats" from User table
        // e.g., from player_stat.php context: cash, race (for context)
        cash: true,
        race: true,
        clan: { // Include basic clan info for context
            select: {
                clan_sym: true,
                sym_color: true,
                clan_name: true,
            }
        }
      },
    });

    if (!gameUserStats) {
      return NextResponse.json({ message: 'Player stats not found for this user in this game.' }, { status: 404 });
    }

    // Convert BigInt to string for JSON compatibility
    const responseStats = {
        ...gameUserStats,
        cash: String(gameUserStats.cash),
    };

    // TODO: Could augment with Race name from Race table if race ID is just a number.
    // Example: if (gameUserStats.race) {
    //   const raceInfo = await prisma.race.findUnique({ where: { race_ID: gameUserStats.race }}); // Assuming race_ID is the PK for Race table
    //   responseStats.race_name = raceInfo?.race_name;
    // }


    return NextResponse.json(responseStats, { status: 200 });

  } catch (error) {
    console.error(`Error fetching player stats for user ID ${params.userId}:`, error);
    return NextResponse.json({ message: 'An error occurred while fetching player statistics.' }, { status: 500 });
  } finally {
    await prisma.$disconnect();
  }
}
