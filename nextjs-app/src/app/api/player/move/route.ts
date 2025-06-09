import { NextResponse } from 'next/server';
import { getServerSession } from 'next-auth/next';
import { authOptions } from '@/app/api/auth/[...nextauth]'; // Adjust path
import { PrismaClient, User } from '@prisma/client';
// import { nebulaMinFuel, miningRushMinMetal } from '@/utils/commonUtils'; // For event logic later
// import { getSafeRandomStar } from '@/utils/serverUtils'; // Assuming server-only utils

const prisma = new PrismaClient();

// Placeholder for random event checking logic
// This would eventually call ported versions of random_event_checker, black_hole, etc.
// and interact with Prisma. For now, it's a simplified placeholder.
async function checkForAndProcessRandomEvents(user: User, targetStarId: number, prisma: PrismaClient): Promise<{ eventOccurred: boolean; message?: string; newLocation?: number, shieldUpdate?: boolean }> {
  const targetStar = await prisma.star.findUnique({ where: { star_id: targetStarId } });
  if (!targetStar || !targetStar.event_random || targetStar.event_random === 0) {
    return { eventOccurred: false };
  }

  const eventType = targetStar.event_random;
  const turnsSafe = (await prisma.dbVar.findUnique({where: {name: 'turns_safe'}}))?.value ?? '50';
  const isNewbie = user.turns_run < parseInt(turnsSafe);

  if (eventType === 1) { // Black Hole
    if (isNewbie) {
      return {
        eventOccurred: true,
        message: `You approached a black hole in system ${targetStar.star_name} (#${targetStarId})! Your newbie status protected you this time. Be wary!`
      };
    } else {
      // Complex black hole logic: move player to a random safe star, damage ships
      // For now, just a message and prevent entry to the targetStar itself.
      // A real implementation would call a "handleBlackHoleEvent" service.
      // const safeStarId = await getSafeRandomStar(targetStarId, prisma); // This utility needs to be created
      const safeStarId = 1; // Fallback to Sol for now

      await prisma.user.update({
          where: { login_id: user.login_id },
          data: { location: safeStarId }
      });
      // TODO: Damage ships, update fleet locations, post news
      return {
        eventOccurred: true,
        message: `You encountered a black hole in ${targetStar.star_name} (#${targetStarId})! Your fleet was scattered to system #${safeStarId} and sustained damage.`,
        newLocation: safeStarId,
      };
    }
  } else if (eventType === 2 || eventType === 12) { // Nebula or Solar Storm
    await prisma.ship.updateMany({
      where: { login_id: user.login_id, location: targetStarId }, // Should be current location BEFORE move, but for simplicity, assume it's target.
      data: { shields: 0 },
    });
    return {
      eventOccurred: true,
      message: `You entered ${targetStar.star_name} (#${targetStarId}) and encountered ${eventType === 2 ? 'a nebula' : 'a solar storm'}. All ship shields in the system were depleted!`,
      shieldUpdate: true,
    };
  }
  return { eventOccurred: false };
}


export async function POST(request: Request) {
  try {
    const session = await getServerSession(authOptions);
    if (!session || !session.user || !session.user.id) {
      return NextResponse.json({ message: 'User not authenticated.' }, { status: 401 });
    }
    const userId = parseInt(session.user.id, 10);

    const body = await request.json();
    const { targetSystemId } = body;

    if (typeof targetSystemId !== 'number') {
      return NextResponse.json({ message: 'targetSystemId (number) is required.' }, { status: 400 });
    }

    const user = await prisma.user.findUnique({ where: { login_id: userId } });
    if (!user || user.location === null) {
      return NextResponse.json({ message: 'Player location not found or user not in game.' }, { status: 404 });
    }

    const currentStar = await prisma.star.findUnique({ where: { star_id: user.location } });
    if (!currentStar) {
      return NextResponse.json({ message: 'Current star system data not found.' }, { status: 500 });
    }

    // Basic Validation: Is targetSystemId a direct link?
    const validLinks = [
      currentStar.link_1, currentStar.link_2, currentStar.link_3,
      currentStar.link_4, currentStar.link_5, currentStar.link_6,
      currentStar.wormhole, // Assuming wormhole is a direct jump for now
    ].filter(id => id !== null && id !== 0);

    if (!validLinks.includes(targetSystemId)) {
      return NextResponse.json({ message: 'Target system is not directly accessible.' }, { status: 400 });
    }

    // Turn Cost (simplified for now)
    // TODO: Implement ship_warp_cost logic from db_vars, which can be -1 for per-ship type cost
    const defaultMoveCost = 1; // Placeholder
    if (user.turns < defaultMoveCost) {
      return NextResponse.json({ message: `Not enough turns to move. Requires ${defaultMoveCost}.` }, { status: 400 });
    }

    // Check for random events at the destination *before* committing the move
    // This is a simplified approach. A more robust one might move then resolve event.
    const eventResult = await checkForAndProcessRandomEvents(user, targetSystemId, prisma);

    let finalLocation = targetSystemId;
    if (eventResult.eventOccurred && eventResult.newLocation) {
        finalLocation = eventResult.newLocation; // Black hole moved player
    }

    // Update player location and deduct turns
    const updatedUser = await prisma.user.update({
      where: { login_id: userId },
      data: {
        location: finalLocation,
        turns: { decrement: defaultMoveCost },
        turns_run: { increment: defaultMoveCost } // Assuming move cost also counts as turns_run
      },
    });

    // If event resulted in shield update, and player wasn't moved by black hole to a different system
    if (eventResult.shieldUpdate && finalLocation === targetSystemId) {
        // The shield update was already done in checkForAndProcessRandomEvents
        // but we might want to refetch ship data if returning it.
    }

    return NextResponse.json({
      message: `Successfully moved to system #${finalLocation}. ${eventResult.message || ''}`,
      newLocation: finalLocation,
      turns: updatedUser.turns,
      eventDetails: eventResult.eventOccurred ? eventResult : null,
    }, { status: 200 });

  } catch (error) {
    console.error('Player move error:', error);
    return NextResponse.json({ message: 'An error occurred during player movement.' }, { status: 500 });
  } finally {
    await prisma.$disconnect();
  }
}
