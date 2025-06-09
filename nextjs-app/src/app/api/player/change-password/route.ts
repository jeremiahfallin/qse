import { NextResponse } from 'next/server';
import { getServerSession } from 'next-auth/next';
import { authOptions } from '@/app/api/auth/[...nextauth]'; // Adjust path as needed
import { PrismaClient } from '@prisma/client';
import bcrypt from 'bcryptjs';
import crypto from 'crypto'; // For MD5 comparison during migration phase
import { isValidPassword } from '@/utils/validationUtils'; // For new password validation

const prisma = new PrismaClient();

export async function POST(request: Request) {
  try {
    const session = await getServerSession(authOptions);
    if (!session || !session.user || !session.user.id) {
      return NextResponse.json({ message: 'User not authenticated.' }, { status: 401 });
    }
    const globalUserId = parseInt(session.user.id, 10);

    const body = await request.json();
    const { oldPassword, newPassword, newPassword2 } = body;

    if (!oldPassword || !newPassword || !newPassword2) {
      return NextResponse.json({ message: 'Missing required fields (oldPassword, newPassword, newPassword2).' }, { status: 400 });
    }

    if (newPassword !== newPassword2) {
      return NextResponse.json({ message: 'New passwords do not match.' }, { status: 400 });
    }

    const userAccount = await prisma.userAccount.findUnique({
      where: { login_id: globalUserId },
    });

    if (!userAccount) {
      // Should not happen if session is valid
      return NextResponse.json({ message: 'User account not found.' }, { status: 404 });
    }

    // Validate new password (length, not same as login name)
    // Note: isValidPassword from utils was (password, loginName). We might not want to pass loginName here
    // or adjust the utility. For now, just basic length check as per original PHP options.php
    if (newPassword.length < 5) {
         return NextResponse.json({ message: 'New password must be at least 5 characters long.' }, { status: 400 });
    }
    if (newPassword === oldPassword) {
        return NextResponse.json({ message: 'New password cannot be the same as the old password.' }, { status: 400 });
    }
     if (userAccount.login_name === newPassword) { // Check against login_name
      return NextResponse.json({ message: 'Password cannot be the same as your login name.' }, { status: 400 });
    }


    // Verify oldPassword
    let currentPasswordIsValid = false;
    const storedPassword = userAccount.passwd;

    if (storedPassword.startsWith('$2a$') || storedPassword.startsWith('$2b$') || storedPassword.startsWith('$2y$')) {
      currentPasswordIsValid = await bcrypt.compare(oldPassword, storedPassword);
    } else if (storedPassword.length === 32 && /^[a-f0-9]{32}$/.test(storedPassword)) {
      // Fallback for MD5 (simplified check, see auth route for detailed comments on MD5 strategy)
      const oldPasswordMd5 = crypto.createHash('md5').update(oldPassword).digest('hex');
      if (oldPasswordMd5 === storedPassword) {
        currentPasswordIsValid = true;
        // Password will be re-hashed to bcrypt below
      }
    } else if (oldPassword === storedPassword) { // Potential plaintext, highly insecure
        currentPasswordIsValid = true;
        console.warn(`User ${userAccount.login_name} verified with PLAINTEXT password during password change. CRITICAL SECURITY RISK.`);
    }


    if (!currentPasswordIsValid) {
      return NextResponse.json({ message: 'The old password is not correct.' }, { status: 400 });
    }

    // Hash the new password
    const newHashedPassword = await bcrypt.hash(newPassword, 10);

    // Update the password in the database
    await prisma.userAccount.update({
      where: { login_id: globalUserId },
      data: { passwd: newHashedPassword /*, password_hash_type: 'bcrypt' // if using this field */ },
    });

    // TODO: Invalidate other active sessions for this user.
    // This is complex with JWTs. One strategy is to change the NEXTAUTH_SECRET,
    // but that invalidates everyone. Another is to use database sessions with next-auth
    // and clear them, or maintain a "passwordChangedAt" timestamp in the user record
    // and check it in the JWT callback to force re-login if token issued_at < passwordChangedAt.

    // For admin user (ID 1), original PHP also updated se_games.admin_pw if they changed their own password
    // This logic might need to be re-evaluated. If admin_pw for a game is meant to be distinct
    // from the admin's global account password, it should be changed via a separate admin panel interface.
    // For now, this API only changes the UserAccount password.

    return NextResponse.json({ message: 'Password changed successfully.' }, { status: 200 });

  } catch (error) {
    console.error('Change Password error:', error);
    return NextResponse.json({ message: 'An error occurred while changing the password.' }, { status: 500 });
  } finally {
    await prisma.$disconnect();
  }
}
