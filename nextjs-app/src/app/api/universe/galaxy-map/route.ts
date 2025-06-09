import { NextResponse } from 'next/server';
import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

export async function GET(request: Request) {
  try {
    const stars = await prisma.star.findMany({
      select: {
        star_id: true,
        star_name: true,
        x_loc: true,
        y_loc: true,
        sys_type: true,
        link_1: true,
        link_2: true,
        link_3: true,
        link_4: true,
        link_5: true,
        link_6: true,
        wormhole: true,
        // We might not need event_random for map display, unless it changes appearance
      },
      // Add orderBy if needed, e.g., orderBy: { star_id: 'asc' }
    });

    const uvUniverseSizeVar = await prisma.dbVar.findUnique({
      where: { name: 'uv_universe_size' },
      select: { value: true },
    });

    const uvShowWarpNumbersVar = await prisma.dbVar.findUnique({
      where: { name: 'uv_show_warp_numbers' },
      select: { value: true },
    });

    // Consider if $nether logic (negative star_ids) needs to be handled.
    // The Prisma schema currently defines star_id as Int @id @default(autoincrement())
    // which typically means positive integers. If negative IDs are a feature,
    // the schema and queries might need adjustment. For now, assuming positive IDs.

    const responseData = {
      stars,
      universeSize: uvUniverseSizeVar ? parseInt(uvUniverseSizeVar.value, 10) : 500, // Default from PHP
      showWarpNumbers: uvShowWarpNumbersVar ? uvShowWarpNumbersVar.value === '1' : true, // Default from PHP
    };

    return NextResponse.json(responseData, { status: 200 });

  } catch (error) {
    console.error('Error fetching galaxy map data:', error);
    return NextResponse.json({ message: 'An error occurred while fetching galaxy map data.' }, { status: 500 });
  } finally {
    await prisma.$disconnect();
  }
}
