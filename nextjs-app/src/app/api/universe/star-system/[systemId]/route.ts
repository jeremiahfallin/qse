import { NextResponse } from 'next/server';
import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

interface StarSystemParams {
  params: {
    systemId: string;
  };
}

export async function GET(request: Request, { params }: StarSystemParams) {
  try {
    const systemId = parseInt(params.systemId, 10);

    if (isNaN(systemId)) {
      return NextResponse.json({ message: 'Invalid system ID.' }, { status: 400 });
    }

    const starSystem = await prisma.star.findUnique({
      where: { star_id: systemId },
      include: {
        // Entities within this star system
        planets_in_system: true, // Fetches all fields for related planets
        ports_in_system: true,   // Fetches all fields for related ports
        bmrkts_in_star: true,    // Fetches all fields for related black markets
        shipyards_in_star: true, // Fetches all fields for related shipyards

        // Ships in system: potentially many, so might need pagination or summarization later
        // For now, fetching some basic info. Consider if this is too much data for one call.
        ships_in_system: {
          select: {
            ship_id: true,
            ship_name: true,
            login_id: true, // Owner ID
            login_name: true, // Owner name (denormalized on Ship table)
            class_name: true,
            class_name_abbr: true,
            fighters: true,
            // Potentially add clan info if directly on ship or via owner
            owner: { // If relation to User (game-specific) is 'owner'
              select: {
                clan: { // If User has relation to Clan
                  select: {
                    clan_id: true,
                    clan_sym: true,
                    sym_color: true,
                  }
                }
              }
            }
          },
          take: 50, // Limit the number of ships returned for performance
        },
        // Consider adding wormhole destination star details if star.wormhole is a star_id
        // Example: Fetching the name of the wormhole destination
        // This requires a separate query or a more complex relation if not directly modeled.
      },
    });

    if (!starSystem) {
      return NextResponse.json({ message: 'Star system not found.' }, { status: 404 });
    }

    // If star.wormhole is a star_id, fetch its name
    let wormholeDestinationInfo = null;
    if (starSystem.wormhole && starSystem.wormhole !== 0) { // Assuming 0 or null means no wormhole
        const destStar = await prisma.star.findUnique({
            where: { star_id: starSystem.wormhole },
            select: { star_id: true, star_name: true }
        });
        if (destStar) {
            wormholeDestinationInfo = destStar;
        }
    }

    const responseData = {
      ...starSystem,
      wormhole_destination: wormholeDestinationInfo,
    };


    return NextResponse.json(responseData, { status: 200 });

  } catch (error) {
    console.error(`Error fetching star system data for ID ${params.systemId}:`, error);
    return NextResponse.json({ message: 'An error occurred while fetching star system data.' }, { status: 500 });
  } finally {
    await prisma.$disconnect();
  }
}
