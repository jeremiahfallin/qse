import { NextResponse } from 'next/server';
import { getServerSession } from 'next-auth/next';
import { authOptions } from '@/app/api/auth/[...nextauth]'; // Adjust path
import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

interface PlayerInfoParams {
  params: {
    userId: string;
  };
}

export async function GET(request: Request, { params }: PlayerInfoParams) {
  try {
    const session = await getServerSession(authOptions);
    const viewingUserId = session?.user?.id ? parseInt(session.user.id, 10) : null;
    const targetUserId = parseInt(params.userId, 10);

    if (isNaN(targetUserId)) {
      return NextResponse.json({ message: 'Invalid user ID.' }, { status: 400 });
    }

    // Fetch game-specific user data (most details)
    const gameUser = await prisma.user.findUnique({
      where: { login_id: targetUserId },
      include: {
        clan: { // Include clan symbol and color
          select: {
            clan_sym: true,
            sym_color: true,
            clan_name: true, // For displaying clan name
          }
        },
        // Add other relations if needed for display, e.g., planets, ships (counts or brief summaries)
      },
    });

    if (!gameUser) {
      return NextResponse.json({ message: 'Player not found in this game.' }, { status: 404 });
    }

    // Fetch global user account data (for less sensitive or generally public info)
    const userAccount = await prisma.userAccount.findUnique({
      where: { login_id: targetUserId },
      select: {
        login_name: true, // Usually public
        first_name: true, // May or may not be public depending on privacy settings
        last_name: true,  // May or may not be public
        signed_up: true,  // Global account creation date
        // Email is sensitive, only show to self or admin
        email_address: viewingUserId === targetUserId || viewingUserId === 1 ? true : false, // Assuming user_id 1 is admin
        icq: true, // Consider privacy
        aim: true, // Consider privacy
        msn: true, // Consider privacy
        yim: true, // Consider privacy
        last_ip: viewingUserId === targetUserId || viewingUserId === 1 ? true : false, // Sensitive
        num_games_joined: true, // Global stat
      },
    });

    if (!userAccount) {
        // This case should ideally not happen if a gameUser record exists, due to FK constraints.
        // But as a safeguard:
        return NextResponse.json({ message: 'Global player account not found.' }, { status: 404 });
    }


    // Determine view level / what data to expose
    // For now, we are fetching based on some basic privacy (email, last_ip)
    // A more complex system might have user-defined privacy settings.
    const isSelf = viewingUserId === targetUserId;
    const isAdminViewing = viewingUserId === 1; // Assuming user_id 1 is admin

    // Combine and structure the response
    // Only include fields that are appropriate for public/semi-public viewing
    // or for self/admin viewing.

    const profileData = {
      // From UserAccount (Global)
      login_name: userAccount.login_name,
      generic_l_name: userAccount.login_name, // As used in PHP `player_info`
      first_name: userAccount.first_name,
      last_name: userAccount.last_name,
      signed_up_global: userAccount.signed_up, // Renamed to avoid clash with gameUser.joined_game
      num_games_joined: userAccount.num_games_joined,

      // From User (Game-Specific)
      login_id: gameUser.login_id, // This is the ID for game specific context
      race: gameUser.race, // TODO: Convert race ID to race name via Race table lookup
      joined_game: gameUser.joined_game,
      last_request: gameUser.last_request,
      game_login_count: gameUser.game_login_count,
      turns: gameUser.turns,
      turns_run: gameUser.turns_run,
      cash: String(gameUser.cash), // Convert BigInt to string for JSON
      tech: gameUser.tech,
      ships_killed: gameUser.ships_killed,
      ships_lost: gameUser.ships_lost,
      ships_killed_points: gameUser.ships_killed_points,
      ships_lost_points: gameUser.ships_lost_points,
      fighters_killed: gameUser.fighters_killed,
      fighters_lost: gameUser.fighters_lost,
      score: gameUser.score,
      last_attack: gameUser.last_attack,
      last_attack_by: gameUser.last_attack_by,
      bounty: gameUser.bounty,
      politics: gameUser.politics, // TODO: Convert ID to political rank name
      genesis: gameUser.genesis,
      alpha: gameUser.alpha, // Alpha bombs
      gamma: gameUser.gamma, // Gamma bombs
      delta: gameUser.delta, // Delta bombs (boolean in schema, was int in PHP)
      sn_effect: gameUser.sn_effect, // Supernova effector (boolean in schema, was int)
      clan: gameUser.clan ? {
        name: gameUser.clan.clan_name,
        symbol: gameUser.clan.clan_sym,
        color: gameUser.clan.sym_color,
      } : null,
      // Sensitive fields - only for self or admin
      ...( (isSelf || isAdminViewing) && {
          email_address: userAccount.email_address,
          icq: userAccount.icq,
          aim: userAccount.aim,
          msn: userAccount.msn,
          yim: userAccount.yim,
          last_ip: userAccount.last_ip,
      })
      // TODO: Add counts for ships/planets or brief summaries if needed directly in profile
      // Full lists would be separate API calls or part of more detailed sub-sections.
    };

    // TODO: Fetch race name from Race table using gameUser.race
    // TODO: Convert politics ID to string using Politics table/logic

    return NextResponse.json(profileData, { status: 200 });

  } catch (error) {
    console.error(`Error fetching player info for user ID ${params.userId}:`, error);
    return NextResponse.json({ message: 'An error occurred while fetching player information.' }, { status: 500 });
  } finally {
    await prisma.$disconnect();
  }
}
