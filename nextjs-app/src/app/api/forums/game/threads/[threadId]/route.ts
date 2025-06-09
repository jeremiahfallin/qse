import { NextResponse } from 'next/server';
import { getServerSession } from 'next-auth/next';
import { authOptions } from '@/app/api/auth/[...nextauth]'; // Adjust path as needed
import { PrismaClient } from '@prisma/client';
import appConfig from '@/config/appConfig';

const prisma = new PrismaClient();

interface ThreadViewParams {
  params: {
    threadId: string;
  };
}

export async function GET(request: Request, { params }: ThreadViewParams) {
  try {
    const session = await getServerSession(authOptions);
    if (!session || !session.user || !session.user.id) {
      return NextResponse.json({ message: 'User not authenticated.' }, { status: 401 });
    }

    const threadId = parseInt(params.threadId, 10);
    if (isNaN(threadId)) {
      return NextResponse.json({ message: 'Invalid thread ID.' }, { status: 400 });
    }

    const { searchParams } = new URL(request.url);
    const page = parseInt(searchParams.get('page') || '1', 10);
    const limit = parseInt(searchParams.get('limit') || appConfig.forumPostsPerPage || '15', 10); // Use a config value
    const skip = (page - 1) * limit;

    // Fetch the main thread post (the one with reply_to = 0 and message_id = threadId)
    const threadStarterPost = await prisma.gameForumMessage.findUnique({
      where: {
        message_id: threadId,
        // reply_to: 0, // Ensure it's actually a thread starter
      },
      // include: { user: { select: { login_name: true }} } // If sender_id linked to User table
    });

    if (!threadStarterPost) {
      return NextResponse.json({ message: 'Thread not found.' }, { status: 404 });
    }
    if (threadStarterPost.reply_to !== 0) {
        return NextResponse.json({ message: 'Invalid thread ID. This is a reply, not a thread starter.' }, { status: 400 });
    }


    // Fetch replies to this thread
    const replies = await prisma.gameForumMessage.findMany({
      where: {
        reply_to: threadId,
      },
      skip,
      take: limit,
      orderBy: {
        timestamp: 'asc', // Show replies in chronological order
      },
      // include: { user: { select: { login_name: true }} }
    });

    const totalReplies = await prisma.gameForumMessage.count({
      where: {
        reply_to: threadId,
      },
    });

    // Decode text from Buffer to string for client
    const decodeText = (post: typeof threadStarterPost | typeof replies[0]) => ({
      ...post,
      text: Buffer.from(post.text).toString('utf-8'), // Or appropriate encoding
    });

    return NextResponse.json({
      threadStarter: decodeText(threadStarterPost),
      replies: replies.map(decodeText),
      currentPage: page,
      totalPages: Math.ceil(totalReplies / limit),
      totalReplies,
    }, { status: 200 });

  } catch (error) {
    console.error(`Error fetching game forum thread ${params.threadId}:`, error);
    return NextResponse.json({ message: 'An error occurred while fetching the thread.' }, { status: 500 });
  } finally {
    await prisma.$disconnect();
  }
}
