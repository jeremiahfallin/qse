import { getServerSession } from 'next-auth/next';
import { authOptions } from '@/app/api/auth/[...nextauth]'; // Adjust path as needed
import { NextResponse } from 'next/server';
import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

export interface AdminSession {
  user: {
    id: string; // UserAccount login_id
    name?: string | null;
    email?: string | null;
    // Potentially add roles or specific permissions if fetched in session callback
    isAdmin?: boolean;
  };
}

/**
 * Checks if the current session belongs to an admin user.
 * For this game, admin is typically user with login_id = 1.
 * This could be expanded to check roles/permissions table if needed.
 * @returns The admin session if authorized, otherwise null.
 */
export async function getAdminSession(): Promise<AdminSession | null> {
  const session = await getServerSession(authOptions);

  if (!session || !session.user || !session.user.id) {
    return null;
  }

  // Primary check: User ID 1 is the super admin
  if (session.user.id === '1') {
    return session as AdminSession; // Cast, assuming structure matches
  }

  // Secondary check (more flexible): Check Permission table
  // This requires session callback to populate user.isAdmin or similar
  // For now, we rely on user.id === '1' as per original PHP.
  // Example for future:
  /*
  const permissions = await prisma.permission.findUnique({
    where: { login_id: parseInt(session.user.id, 10) },
  });
  if (permissions && (permissions.admin || permissions.god)) {
    return { ...session, user: { ...session.user, isAdmin: true } } as AdminSession;
  }
  */

  return null;
}

/**
 * Middleware-like function to protect API routes that require admin access.
 * To be called at the beginning of an admin API route handler.
 * @returns A NextResponse object if unauthorized, otherwise null.
 */
export async function ensureAdmin(): Promise<NextResponse | null> {
  const adminSession = await getAdminSession();
  if (!adminSession) {
    return NextResponse.json({ message: 'Access denied: Admin privileges required.' }, { status: 403 });
  }
  return null; // Indicates success, admin is present
}

/**
 * Higher-order function to wrap an API route handler with an admin check.
 * Not directly used now but demonstrates a pattern.
 */
/*
export function withAdminAuth(handler: Function) {
  return async (request: Request, context?: any) => {
    const adminSession = await getAdminSession();
    if (!adminSession) {
      return NextResponse.json({ message: 'Access denied: Admin privileges required.' }, { status: 403 });
    }
    return handler(request, context, adminSession); // Pass session to handler
  };
}
*/
