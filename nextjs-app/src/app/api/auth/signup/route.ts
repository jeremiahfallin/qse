import { NextResponse } from 'next/server';
import { PrismaClient } from '@prisma/client';
import bcrypt from 'bcryptjs';
import { isValidEmailFormat, isValidLoginName, isValidPassword } from '@/utils/validationUtils'; // Assuming utils is aliased to src/utils

const prisma = new PrismaClient();

export async function POST(request: Request) {
  try {
    const body = await request.json();
    const {
      loginName,
      password,
      passwordVerify,
      email,
      emailVerify,
      firstName, // optional based on form
      lastName,  // optional
      // Add other fields from UserAccount as needed, e.g.:
      // icq, aim, msn, yim, country, hear_from, birth_date, sex, hint_question, hint_answer
      disclaimerAgreed, // Assuming this is a boolean sent from client
    } = body;

    // --- Basic Validation ---
    if (!loginName || !password || !passwordVerify || !email || !emailVerify) {
      return NextResponse.json({ message: 'Missing required fields.' }, { status: 400 });
    }

    if (password !== passwordVerify) {
      return NextResponse.json({ message: 'Passwords do not match.' }, { status: 400 });
    }

    if (email !== emailVerify) {
      return NextResponse.json({ message: 'Email addresses do not match.' }, { status: 400 });
    }

    // Assuming disclaimerAgreed is mandatory, like in the original PHP
    if (!disclaimerAgreed) {
        return NextResponse.json({ message: 'You must agree to the server rules & disclaimer.' }, { status: 400 });
    }

    // --- Detailed Field Validation ---
    if (!isValidLoginName(loginName)) {
      return NextResponse.json({ message: 'Invalid login name. Ensure it has at least 3 characters and no forbidden characters (like <, >).' }, { status: 400 });
    }
    if (!isValidPassword(password, loginName)) {
      return NextResponse.json({ message: 'Invalid password. Ensure it is at least 5 characters long and not the same as your login name.' }, { status: 400 });
    }
    if (!isValidEmailFormat(email)) {
      return NextResponse.json({ message: 'Invalid email format.' }, { status: 400 });
    }

    // --- Check for Existing User/Email ---
    const existingUserByLoginName = await prisma.userAccount.findUnique({
      where: { login_name: loginName },
    });
    if (existingUserByLoginName) {
      return NextResponse.json({ message: 'Login name already taken.' }, { status: 409 }); // 409 Conflict
    }

    const existingUserByEmail = await prisma.userAccount.findUnique({
      where: { email_address: email },
    });
    if (existingUserByEmail) {
      return NextResponse.json({ message: 'Email address already in use.' }, { status: 409 });
    }

    // --- Hash Password ---
    const hashedPassword = await bcrypt.hash(password, 10); // 10 salt rounds

    // --- Create User Account ---
    // Default values for fields not provided in this basic signup form
    // should match your Prisma schema defaults or be set here.
    const newUserAccount = await prisma.userAccount.create({
      data: {
        login_name: loginName,
        passwd: hashedPassword, // Store the hashed password
        email_address: email,
        first_name: firstName || '',
        last_name: lastName || '',
        signed_up: Math.floor(Date.now() / 1000), // Unix timestamp
        last_login: 0, // Per original PHP, for email verification logic later
        session_exp: 0,
        session_id: 0, // Will be updated on first login by NextAuth
        login_count: 0,
        auth: 0, // Default auth level, might need adjustment or email verification token
        // Initialize other non-optional fields from UserAccount model with defaults
        icq: 0,
        aim: '',
        msn: '',
        yim: '',
        last_ip: '', // Can be captured from request if needed
        num_games_joined: 0,
        total_score: 0,
        con_speed: 2, // Default from SQL
        default_color_scheme: 1, // Default from SQL
        country: '', // Or body.country if provided
        hear_from: '', // Or body.hear_from if provided
        e_list: true, // Default from SQL (was 1)
        birth_date: 0, // Or body.birth_date if provided
        sex: 0, // Or body.sex if provided
        hint_question: '', // Or body.hint_question if provided
        hint_answer: '', // Or body.hint_answer if provided (should also be hashed)
        newsletter: false, // Default from SQL (was 0)
      },
    });

    // TODO (Optional for this subtask): Implement email verification flow
    // 1. Generate a verification token.
    // 2. Store token associated with newUserAccount.login_id (e.g., in UserAccount or separate table).
    // 3. Send email with verification link (requires email service integration).
    // For now, user is created as active.

    return NextResponse.json({
      message: 'User account created successfully.',
      userId: newUserAccount.login_id
    }, { status: 201 });

  } catch (error) {
    console.error('Signup error:', error);
    // Check for Prisma-specific errors if needed, e.g., P2002 for unique constraint
    if ((error as any)?.code === 'P2002') {
        // Fields that could cause this are login_name or email_address due to earlier checks,
        // but this is a fallback.
        return NextResponse.json({ message: 'Login name or email already exists.' }, { status: 409 });
    }
    return NextResponse.json({ message: 'An error occurred during signup.' }, { status: 500 });
  } finally {
    await prisma.$disconnect();
  }
}
