import { NextResponse } from 'next/server';
import { getServerSession } from 'next-auth/next';
import { authOptions } from '@/app/api/auth/[...nextauth]'; // Adjust path as needed
import { PrismaClient } from '@prisma/client';
import appConfig from '@/config/appConfig'; // For itemsPerPage or other configs

const prisma = new PrismaClient();

export async function GET(request: Request) {
  try {
    const session = await getServerSession(authOptions);
    if (!session || !session.user || !session.user.id) {
      return NextResponse.json({ message: 'User not authenticated.' }, { status: 401 });
    }

    const { searchParams } = new URL(request.url);
    const page = parseInt(searchParams.get('page') || '1', 10);
    const limit = parseInt(searchParams.get('limit') || appConfig.forumThreadsPerPage || '20', 10); // Use a config value
    const skip = (page - 1) * limit;

    // Fetch threads (messages with reply_to === 0, assuming 0 indicates a thread starter)
    const threads = await prisma.gameForumMessage.findMany({
      where: {
        reply_to: 0, // Assuming 0 means it's a thread starter
      },
      skip,
      take: limit,
      orderBy: {
        timestamp: 'desc', // Or last_reply_timestamp if that field gets added
      },
      include: {
        // User is not directly on GameForumMessage, sender_name and sender_id are.
        // To get full user details, we'd need to query User based on sender_id.
        // For now, we'll rely on sender_name and sender_id.
        // A more advanced version could fetch User details for each sender_id.
        _count: { // Count replies
          select: { replies: true } // Assumes 'replies' is the relation name on GameForumMessage for self-relation
        }
      },
    });

    // Fetch total number of threads for pagination
    const totalThreads = await prisma.gameForumMessage.count({
      where: { reply_to: 0 },
    });

    // TODO: For each thread, fetch last reply info (timestamp, author name)
    // This would require an additional query per thread or a more complex aggregation.
    // For simplicity in this step, last reply info is omitted but noted.

    return NextResponse.json({
      threads: threads.map(t => ({
          ...t,
          text: undefined, // Don't send full text in thread list
          replyCount: t._count.replies
      })),
      currentPage: page,
      totalPages: Math.ceil(totalThreads / limit),
      totalThreads,
    }, { status: 200 });

  } catch (error) {
    console.error('Error fetching game forum threads:', error);
    return NextResponse.json({ message: 'An error occurred while fetching game forum threads.' }, { status: 500 });
  } finally {
    await prisma.$disconnect();
  }
}

export async function POST(request: Request) {
  try {
    const session = await getServerSession(authOptions);
    if (!session || !session.user || !session.user.id || !session.user.name) {
      return NextResponse.json({ message: 'User not authenticated or user name missing.' }, { status: 401 });
    }
    const userId = parseInt(session.user.id, 10);
    const userName = session.user.name;

    const body = await request.json();
    const { subject, text } = body;

    if (!subject || subject.trim() === '') {
      return NextResponse.json({ message: 'Subject is required.' }, { status: 400 });
    }
    if (!text || text.trim() === '') {
      return NextResponse.json({ message: 'Text body is required.' }, { status: 400 });
    }

    // mcit and Clean_Text from PHP would be applied here if ported.
    // For now, assuming raw text or client-side handling for BBCode-like features.
    // const processedText = formatLegacyTextToHtml(filterProfanity(text)); // Example
    const processedText = text; // Placeholder

    const newThread = await prisma.gameForumMessage.create({
      data: {
        sender_id: userId,
        sender_name: userName, // Store denormalized sender name
        timestamp: Math.floor(Date.now() / 1000),
        subject: subject,
        text: Buffer.from(processedText), // Prisma's Bytes type expects Buffer
        reply_to: 0, // Indicates a new thread
        clan_id: 0, // Default for game forum messages
        // last_reply_at: Math.floor(Date.now() / 1000), // Initialize last reply info
        // last_reply_by_id: userId,
        // last_reply_by_name: userName,
      },
    });

    return NextResponse.json(newThread, { status: 201 });

  } catch (error) {
    console.error('Error creating game forum thread:', error);
    return NextResponse.json({ message: 'An error occurred while creating the thread.' }, { status: 500 });
  } finally {
    await prisma.$disconnect();
  }
}
