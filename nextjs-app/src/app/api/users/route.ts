import { PrismaClient } from '@prisma/client';
import { NextResponse } from 'next/server';

const prisma = new PrismaClient();

export async function GET() {
  try {
    const users = await prisma.user.findMany();
    return NextResponse.json(users);
  } catch (error) {
    console.error('Error fetching users:', error);
    // It's good practice to not expose raw error messages to the client
    // In a real application, you might want to log the error to a monitoring service
    return NextResponse.json(
      { error: 'Failed to fetch users. Please try again later.' },
      { status: 500 }
    );
  } finally {
    // It's important to disconnect from Prisma Client when it's no longer needed in serverless environments
    // or scripts that run and then exit. For long-running applications, this might not be necessary
    // if you manage the PrismaClient instance lifecycle differently (e.g., as a singleton).
    await prisma.$disconnect();
  }
}
