import { NextResponse } from 'next/server';
import { getServerSession } from 'next-auth/next';
import { authOptions } from '@/app/api/auth/[...nextauth]'; // Adjust path
import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

export async function GET(request: Request) {
  try {
    const session = await getServerSession(authOptions);
    if (!session || !session.user || !session.user.id) {
      return NextResponse.json({ message: 'User not authenticated.' }, { status: 401 });
    }
    const userId = parseInt(session.user.id, 10);

    // Fetch the user's game-specific data, which includes their current location (star_id)
    const gameUser = await prisma.user.findUnique({
      where: { login_id: userId },
      select: {
        location: true, // This is the star_id
        ship_id: true,  // Current ship ID for context
        // Potentially other user-specific location context if needed
      },
    });

    if (!gameUser || gameUser.location === null) {
      return NextResponse.json({ message: 'Player location not found or user not in game.' }, { status: 404 });
    }

    const currentStarId = gameUser.location;

    // Now fetch the details of the star system the player is in.
    // This reuses the logic similar to /api/universe/star-system/[systemId]
    // but is specific to the player's current location.
    const starSystem = await prisma.star.findUnique({
      where: { star_id: currentStarId },
      include: {
        planets_in_system: true,
        ports_in_system: true,
        bmrkts_in_star: true,
        shipyards_in_star: true,
        ships_in_system: {
          select: {
            ship_id: true,
            ship_name: true,
            login_id: true,
            login_name: true,
            class_name_abbr: true,
            fighters: true,
            owner: { select: { clan: { select: { clan_id: true, clan_sym: true, sym_color: true } } } }
          },
          take: 50, // Limit for overview
        },
      },
    });

    if (!starSystem) {
      // This would be unusual if gameUser.location is valid
      return NextResponse.json({ message: 'Current star system data not found.' }, { status: 404 });
    }

    let wormholeDestinationInfo = null;
    if (starSystem.wormhole && starSystem.wormhole !== 0) {
        const destStar = await prisma.star.findUnique({
            where: { star_id: starSystem.wormhole },
            select: { star_id: true, star_name: true }
        });
        if (destStar) {
            wormholeDestinationInfo = destStar;
        }
    }

    const responseData = {
      playerContext: {
        current_ship_id: gameUser.ship_id,
      },
      currentSystem: {
        ...starSystem,
        wormhole_destination: wormholeDestinationInfo,
      }
    };

    return NextResponse.json(responseData, { status: 200 });

  } catch (error) {
    console.error('Error fetching player location data:', error);
    return NextResponse.json({ message: 'An error occurred while fetching player location data.' }, { status: 500 });
  } finally {
    await prisma.$disconnect();
  }
}
