import { NextResponse } from 'next/server';
import { getServerSession } from 'next-auth/next';
import { authOptions } from '@/app/api/auth/[...nextauth]'; // Adjust path as needed
import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

export async function GET(request: Request) {
  try {
    const session = await getServerSession(authOptions);
    if (!session || !session.user || !session.user.id) {
      return NextResponse.json({ message: 'User not authenticated.' }, { status: 401 });
    }
    const globalUserId = parseInt(session.user.id, 10);

    // Fetch UserOptions (game-specific options)
    // Assumes the current Prisma client is configured for the game DB context
    // or UserOption table has a way to distinguish if multiple games share this table (not typical for UserOption)
    const userOptions = await prisma.userOption.findUnique({
      where: { login_id: globalUserId },
    });

    // Fetch game-specific User data (for signature)
    const gameUser = await prisma.user.findUnique({
      where: { login_id: globalUserId },
      select: { sig: true },
    });

    // Fetch global UserAccount data (for newsletter, IM details)
    const userAccount = await prisma.userAccount.findUnique({
      where: { login_id: globalUserId },
      select: {
        newsletter: true,
        aim: true,
        icq: true,
        yim: true,
        msn: true,
      },
    });

    if (!userOptions && !gameUser && !userAccount) {
      return NextResponse.json({ message: 'User not found or no options set.' }, { status: 404 });
    }

    // Combine the data
    // If userOptions is null (e.g., new user who hasn't saved options yet),
    // provide default values or an empty object for options part.
    // The Prisma schema for UserOption has defaults, so it should generally exist after first save.
    // If it can be null, the client needs to handle it.
    const combinedOptions = {
      gameOptions: userOptions || {}, // send empty object if no options record found
      signature: gameUser?.sig || '',
      profile: {
        newsletter: userAccount?.newsletter || false,
        aim: userAccount?.aim || '',
        icq: userAccount?.icq || 0,
        yim: userAccount?.yim || '',
        msn: userAccount?.msn || '',
      },
    };

    return NextResponse.json(combinedOptions, { status: 200 });

  } catch (error) {
    console.error('Get Player Options error:', error);
    return NextResponse.json({ message: 'An error occurred while fetching player options.' }, { status: 500 });
  } finally {
    await prisma.$disconnect();
  }
}

// POST handler for updating options
export async function POST(request: Request) {
  try {
    const session = await getServerSession(authOptions);
    if (!session || !session.user || !session.user.id) {
      return NextResponse.json({ message: 'User not authenticated.' }, { status: 401 });
    }
    const globalUserId = parseInt(session.user.id, 10);

    const body = await request.json();
    const {
      gameOptions, // Expected to be an object like { color_scheme: 1, show_sigs: true, ... }
      signature,   // string
      profile,     // Expected to be an object like { newsletter: true, aim: "...", ... }
      theme,       // string, for theme selection specifically if handled separately
    } = body;

    // --- Update Game-Specific UserOptions ---
    if (gameOptions && typeof gameOptions === 'object') {
      // TODO: Add validation against OptionList table for each key/value in gameOptions
      // For now, directly updating what's provided.
      await prisma.userOption.update({
        where: { login_id: globalUserId },
        data: gameOptions, // Assumes keys in gameOptions match UserOption field names
      });
    }

    // --- Update Theme (if provided separately, otherwise it's part of gameOptions) ---
    if (typeof theme === 'string') {
        await prisma.userOption.update({
            where: { login_id: globalUserId },
            data: { theme: theme },
        });
    }


    // --- Update Signature (in game-specific User table) ---
    if (typeof signature === 'string') {
      // Add length validation for signature (e.g., max 100 chars as per PHP)
      if (signature.length > 100) {
        return NextResponse.json({ message: 'Signature exceeds maximum length of 100 characters.' }, { status: 400 });
      }
      await prisma.user.update({
        where: { login_id: globalUserId },
        data: { sig: signature },
      });
    }

    // --- Update Profile Info (IMs, newsletter in global UserAccount table) ---
    if (profile && typeof profile === 'object') {
      const userAccountUpdateData: { [key: string]: any } = {};
      if (typeof profile.newsletter === 'boolean') userAccountUpdateData.newsletter = profile.newsletter;
      if (typeof profile.aim === 'string') userAccountUpdateData.aim = profile.aim;
      if (typeof profile.icq === 'number' || typeof profile.icq === 'string') userAccountUpdateData.icq = parseInt(String(profile.icq)) || 0; // Ensure it's a number
      if (typeof profile.yim === 'string') userAccountUpdateData.yim = profile.yim;
      if (typeof profile.msn === 'string') userAccountUpdateData.msn = profile.msn;

      if (Object.keys(userAccountUpdateData).length > 0) {
        await prisma.userAccount.update({
          where: { login_id: globalUserId },
          data: userAccountUpdateData,
        });
      }
    }

    return NextResponse.json({ message: 'Options updated successfully.' }, { status: 200 });

  } catch (error) {
    console.error('Update Player Options error:', error);
    if ((error as any)?.code === 'P2025') { // Prisma error code for record not found on update
        return NextResponse.json({ message: 'User options record not found. Could not update.' }, { status: 404 });
    }
    return NextResponse.json({ message: 'An error occurred while updating player options.' }, { status: 500 });
  } finally {
    await prisma.$disconnect();
  }
}
