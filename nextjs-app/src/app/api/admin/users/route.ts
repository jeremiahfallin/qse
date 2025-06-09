import { NextResponse } from 'next/server';
import { PrismaClient } from '@prisma/client';
import { ensureAdmin } from '@/lib/auth/adminUtils'; // Adjust path as needed
import appConfig from '@/config/appConfig'; // For default itemsPerPage

const prisma = new PrismaClient();

export async function GET(request: Request) {
  const adminAuthResponse = await ensureAdmin();
  if (adminAuthResponse) {
    return adminAuthResponse;
  }

  try {
    const { searchParams } = new URL(request.url);
    const page = parseInt(searchParams.get('page') || '1', 10);
    const limit = parseInt(searchParams.get('limit') || appConfig.adminUsersPerPage || '20', 10);
    const skip = (page - 1) * limit;

    const searchLogin = searchParams.get('searchLogin');
    const searchEmail = searchParams.get('searchEmail');
    const sortBy = searchParams.get('sortBy') || 'login_id'; // Default sort field
    const sortOrder = searchParams.get('sortOrder') || 'asc'; // Default sort order

    const whereClause: any = {};
    if (searchLogin) {
      whereClause.login_name = { contains: searchLogin, mode: 'insensitive' }; // mode 'insensitive' for case-insensitivity if supported by DB
    }
    if (searchEmail) {
      whereClause.email_address = { contains: searchEmail, mode: 'insensitive' };
    }

    const users = await prisma.userAccount.findMany({
      where: whereClause,
      skip,
      take: limit,
      orderBy: {
        [sortBy]: sortOrder,
      },
      include: {
        permissions: true, // Include related permissions
        // Do not include game-specific User record here by default, it can be large or context-dependent
      },
    });

    const totalUsers = await prisma.userAccount.count({
      where: whereClause,
    });

    return NextResponse.json({
      users,
      currentPage: page,
      totalPages: Math.ceil(totalUsers / limit),
      totalUsers,
    }, { status: 200 });

  } catch (error) {
    console.error('Admin Get Users error:', error);
    return NextResponse.json({ message: 'An error occurred while fetching users.' }, { status: 500 });
  } finally {
    await prisma.$disconnect();
  }
}
