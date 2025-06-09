import { NextResponse } from 'next/server';
import { getServerSession } from 'next-auth/next';
import { authOptions } from '@/app/api/auth/[...nextauth]'; // Adjust path as needed
import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

interface ReplyParams {
  params: {
    threadId: string;
  };
}

export async function POST(request: Request, { params }: ReplyParams) {
  try {
    const session = await getServerSession(authOptions);
    if (!session || !session.user || !session.user.id || !session.user.name) {
      return NextResponse.json({ message: 'User not authenticated or user name missing.' }, { status: 401 });
    }
    const userId = parseInt(session.user.id, 10);
    const userName = session.user.name;

    const threadId = parseInt(params.threadId, 10);
    if (isNaN(threadId)) {
      return NextResponse.json({ message: 'Invalid thread ID.' }, { status: 400 });
    }

    const body = await request.json();
    const { text } = body;

    if (!text || text.trim() === '') {
      return NextResponse.json({ message: 'Text body is required for a reply.' }, { status: 400 });
    }

    // Check if the parent thread exists and is actually a thread starter
    const parentThread = await prisma.gameForumMessage.findUnique({
      where: { message_id: threadId },
    });

    if (!parentThread) {
      return NextResponse.json({ message: 'Parent thread not found.' }, { status: 404 });
    }
    if (parentThread.reply_to !== 0) {
      return NextResponse.json({ message: 'Cannot reply to a reply. Invalid thread ID.' }, { status: 400 });
    }

    // mcit and Clean_Text from PHP would be applied here if ported.
    // For now, assuming raw text or client-side handling for BBCode-like features.
    const processedText = text; // Placeholder

    const newReply = await prisma.gameForumMessage.create({
      data: {
        sender_id: userId,
        sender_name: userName,
        timestamp: Math.floor(Date.now() / 1000),
        subject: `Re: ${parentThread.subject}`, // Or leave empty, or pass from client
        text: Buffer.from(processedText), // Prisma's Bytes type expects Buffer
        reply_to: threadId,
        clan_id: 0, // Default for game forum messages
      },
    });

    // TODO: Update last reply info on the parentThread (GameForumMessage with message_id == threadId)
    // This might involve adding fields like `last_reply_at`, `last_reply_by_id`, `last_reply_by_name`
    // and incrementing a `reply_count` on the GameForumMessage model for threads.
    // For now, this step is omitted for simplicity in this turn.
    // Example:
    // await prisma.gameForumMessage.update({
    //   where: { message_id: threadId },
    //   data: {
    //     last_reply_at: newReply.timestamp,
    //     last_reply_by_id: newReply.sender_id,
    //     last_reply_by_name: newReply.sender_name,
    //     // replies_count: { increment: 1 } // If such a field exists
    //   }
    // });


    return NextResponse.json(newReply, { status: 201 });

  } catch (error) {
    console.error(`Error creating reply for game forum thread ${params.threadId}:`, error);
    return NextResponse.json({ message: 'An error occurred while creating the reply.' }, { status: 500 });
  } finally {
    await prisma.$disconnect();
  }
}
