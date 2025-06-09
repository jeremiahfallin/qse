import { NextResponse } from 'next/server';
import { getServerSession } from 'next-auth/next';
import { authOptions } from '@/app/api/auth/[...nextauth]'; // Adjust path as needed
import { PrismaClient } from '@prisma/client';
import appConfig from '@/config/appConfig'; // For default values if needed

const prisma = new PrismaClient();

// Placeholder for score_func logic - would need to be properly implemented/ported
async function updatePlayerScoreInGame(userId: number, gameDbName: string) {
  console.log(`Placeholder: Updating score for user ${userId} in game ${gameDbName}`);
  // Actual implementation would involve Prisma calls based on ported score_func logic
}

export async function POST(request: Request) {
  try {
    const session = await getServerSession(authOptions);
    if (!session || !session.user || !session.user.id) {
      return NextResponse.json({ message: 'User not authenticated.' }, { status: 401 });
    }
    const globalUserId = parseInt(session.user.id, 10);

    const body = await request.json();
    const { gameDbName, adminGamePassword } = body;

    if (!gameDbName) {
      return NextResponse.json({ message: 'gameDbName is required.' }, { status: 400 });
    }

    // 1. Validate gameDbName (check if SeGame record exists)
    //    This step assumes SeGame table contains metadata about different games,
    //    even if Prisma client is connected to one specific game's operational tables.
    const gameMeta = await prisma.seGame.findFirst({ // findFirst if db_name is not unique, or findUnique if it is
      where: { db_name: gameDbName },
    });

    if (!gameMeta) {
      return NextResponse.json({ message: 'Invalid game specified.' }, { status: 404 });
    }

    // 2. Handle Admin Login to Game
    //    Assuming globalUserId 1 is the super admin or a specific admin role check.
    //    The original PHP code had specific handling for login_id == 1.
    if (globalUserId === 1) { // Or check a role from session.user if available
      if (adminGamePassword) {
        if (gameMeta.admin_pw !== adminGamePassword) {
          // Log failed admin game password attempt
          // await prisma.userHistory.create({ data: { login_id: globalUserId, action: `Failed admin login to game: ${gameDbName}`, timestamp: Math.floor(Date.now()/1000), user_IP: '' } });
          return NextResponse.json({ message: 'Incorrect admin password for this game.' }, { status: 403 });
        }
        // Admin password correct for this game
        console.log(`Admin user ${globalUserId} authenticated for game ${gameDbName}`);
      } else {
        // Original PHP prompted for password if not provided. API should expect it.
        return NextResponse.json({ message: 'Admin game password required for admin users.' }, { status: 400 });
      }
    }

    // 3. Handle Regular User Login to Game
    let gameUser = await prisma.user.findUnique({
      where: { login_id: globalUserId }, // Assumes User.login_id is the same as UserAccount.login_id
    });

    if (gameUser) {
      // User exists in this game
      if ((gameUser.banned_time && gameUser.banned_time === -1) || (gameUser.banned_time && gameUser.banned_time > Math.floor(Date.now() / 1000))) {
        let banReason = "No reason specified.";
        try {
            // banned_reason is Bytes, attempt to decode as UTF-8
            banReason = Buffer.from(gameUser.banned_reason).toString('utf-8');
        } catch (e) { /* ignore decode error, use default */ }

        return NextResponse.json({
          message: `You are banned from this game. Reason: ${banReason}. Expires: ${gameUser.banned_time === -1 ? 'Permanent' : new Date(gameUser.banned_time * 1000).toLocaleString()}`,
        }, { status: 403 });
      }

      gameUser = await prisma.user.update({
        where: { login_id: globalUserId },
        data: {
          game_login_count: { increment: 1 },
          // last_login: Math.floor(Date.now() / 1000), // If User table has last_login
        },
      });

      await updatePlayerScoreInGame(globalUserId, gameDbName); // Call ported score_func

      // TODO: Update NextAuth session with activeGameDbName if desired
      return NextResponse.json({
        message: 'Successfully joined game.',
        gameDbName,
        needsSetup: false,
        user: { // Return some basic game user info
            login_id: gameUser.login_id,
            login_name: gameUser.login_name,
            location: gameUser.location,
            ship_id: gameUser.ship_id,
        }
      }, { status: 200 });

    } else {
      // First time this UserAccount is joining this gameDbName
      // Create the game-specific User record
      // Default values should align with what a new user gets (e.g., from db_vars or config)
      const defaultTurns = (await prisma.dbVar.findUnique({ where: { name: 'start_turns' } }))?.value ?? '50';
      const defaultCash = (await prisma.dbVar.findUnique({ where: { name: 'start_cash' } }))?.value ?? '10000';
      const defaultTech = (await prisma.dbVar.findUnique({ where: { name: 'start_tech' } }))?.value ?? '0';
      const defaultShipId = (await prisma.dbVar.findUnique({ where: { name: 'start_ship' } }))?.value ?? '4'; // Example default ship_id (e.g. Merchant Freighter)

      // We need the UserAccount's login_name and email for the game-specific User table as well
      const userAccount = await prisma.userAccount.findUnique({ where: { login_id: globalUserId }});
      if (!userAccount) {
        // This should not happen if user has an active session
        return NextResponse.json({ message: 'Global user account not found.' }, { status: 500 });
      }

      gameUser = await prisma.user.create({
        data: {
          login_id: globalUserId, // Link to UserAccount
          login_name: userAccount.login_name, // Copy from UserAccount
          email_address: userAccount.email_address, // Copy from UserAccount
          race: userAccount.race ?? 0, // Assuming race is on UserAccount or default
          joined_game: Math.floor(Date.now() / 1000),
          game_login_count: 1,
          turns: parseInt(defaultTurns, 10),
          cash: BigInt(defaultCash),
          tech: parseInt(defaultTech, 10),
          location: 1, // Default starting location (e.g., Sol)
          ship_id: 1,  // Default ship_id = 1 indicates "Ship Destroyed" or "Escape Pod" initially before proper ship creation.
                        // The PHP code redirects to ship_build.php to name first ship.
                        // For API, we signal client to do this.
          // Initialize other fields as per your User model defaults or game logic
          last_attack: 0,
          last_attack_by: '',
          banned_reason: Buffer.from(''), // Initialize Bytes field
          // ... other User fields
        },
      });

      return NextResponse.json({
        message: 'Welcome! Please set up your character for this game.',
        gameDbName,
        needsSetup: true, // Client uses this to redirect to ship/fleet naming UI
        user: {
            login_id: gameUser.login_id,
            login_name: gameUser.login_name,
        }
      }, { status: 200 });
    }

  } catch (error) {
    console.error('Game join error:', error);
    return NextResponse.json({ message: 'An error occurred while joining the game.' }, { status: 500 });
  } finally {
    await prisma.$disconnect();
  }
}
