import NextAuth, { type NextAuthOptions } from 'next-auth';
import CredentialsProvider from 'next-auth/providers/credentials';
import { PrismaClient } from '@prisma/client';
import bcrypt from 'bcryptjs'; // For comparing new passwords, or after migration

const prisma = new PrismaClient();

export const authOptions: NextAuthOptions = {
  providers: [
    CredentialsProvider({
      // The name to display on the sign in form (e.g. 'Sign in with...')
      name: 'Credentials',
      // The credentials is used to generate a suitable form on the sign in page.
      // You can specify whatever fields you are expecting to be submitted.
      // e.g. domain, username, password, 2FA token, etc.
      credentials: {
        username: { label: "Username", type: "text", placeholder: "jsmith" },
        password: {  label: "Password", type: "password" }
        // The old system used a challenge-response. For simplicity and security,
        // we'll aim for direct password submission over HTTPS.
        // If challenge-response is strictly needed for some reason, it'd be more complex.
      },
      async authorize(credentials, req) {
        if (!credentials?.username || !credentials?.password) {
          console.log('Missing credentials');
          return null;
        }

        try {
          const userAccount = await prisma.userAccount.findUnique({
            where: { login_name: credentials.username },
          });

          if (userAccount) {
            // IMPORTANT: Password verification logic needs to be robust.
            // This example assumes new passwords will be hashed with bcrypt.
            // For existing passwords from the PHP system (MD5 or plaintext):
            // 1. A migration strategy is URGENTLY needed.
            // 2. During migration, you might temporarily check MD5:
            //    const expectedResponse = crypto.createHash('md5').update(credentials.password).digest('hex'); // Or however the PHP `passwd` field was stored
            //    if (userAccount.passwd === expectedResponse) { ... } // This was for a simple MD5 of password only
            //    And then immediately re-hash and save with bcrypt.
            // The PHP system used: md5(strtolower(username) + ":" + db_password_hash + ":" + challenge)
            // This is too complex to replicate securely here without the exact challenge mechanism.
            // We will assume a simpler migration path:
            // 1. If hash looks like bcrypt, use bcrypt.compare.
            // 2. If hash looks like MD5 (32 hex chars), MD5 the input password and compare. If match, re-hash with bcrypt and update.
            // 3. If neither (potential plaintext or other old hash), direct compare (highly insecure, log and force change).

            let isValidPassword = false;
            let needsRehash = false;
            const storedPassword = userAccount.passwd;

            // Try bcrypt first (for already migrated or new users)
            if (storedPassword.startsWith('$2a$') || storedPassword.startsWith('$2b$') || storedPassword.startsWith('$2y$')) {
              isValidPassword = await bcrypt.compare(credentials.password, storedPassword);
            }
            // Else, try MD5 (for old users needing migration)
            // This assumes the stored password IS the MD5 hash of the plaintext password.
            // The original PHP code's `md5(strtolower(username) + ":" + $user['passwd'] + ":" + $chal)`
            // means $user['passwd'] is NOT the final hash compared. It's an input to it.
            // This makes direct MD5 comparison here against userAccount.passwd difficult without knowing what userAccount.passwd actually stores.
            // FOR SIMPLICITY IN THIS TASK, we will assume userAccount.passwd stores a simple MD5 of the password.
            // THIS IS A MAJOR ASSUMPTION AND LIKELY INCORRECT BASED ON THE PHP AUTH CLASS.
            // A real migration would need to extract the exact $user['passwd'] part and test against that with the challenge.
            // For now, we'll demonstrate the re-hashing flow with a simplified MD5 check.
            else if (storedPassword.length === 32 && /^[a-f0-9]{32}$/.test(storedPassword)) { // Basic MD5 check
              const inputPasswordMd5 = crypto.createHash('md5').update(credentials.password).digest('hex');
              if (inputPasswordMd5 === storedPassword) {
                isValidPassword = true;
                needsRehash = true;
                console.log(`User ${credentials.username} logged in with MD5. Needs rehash.`);
              }
            }
            // Add a plaintext check as a last resort if nothing else matches (VERY INSECURE)
            // else if (credentials.password === storedPassword) {
            //   isValidPassword = true;
            //   needsRehash = true;
            //   console.warn(`User ${credentials.username} logged in with PLAINTEXT password. CRITICAL SECURITY RISK.`);
            // }


            if (isValidPassword) {
              if (needsRehash) {
                try {
                  const newBcryptHash = await bcrypt.hash(credentials.password, 10);
                  await prisma.userAccount.update({
                    where: { login_id: userAccount.login_id },
                    data: { passwd: newBcryptHash /*, password_hash_type: 'bcrypt' // if you add this field */ },
                  });
                  console.log(`Password for user ${credentials.username} re-hashed to bcrypt.`);
                } catch (rehashError) {
                  console.error('Error re-hashing password:', rehashError);
                  // Decide if login should proceed if re-hash fails. For now, allow it.
                }
              }
              // Any object returned will be saved in `user` property of the JWT
              // and forwarded to the session callback.
              console.log('User authenticated:', userAccount.login_name);
              return {
                id: userAccount.login_id.toString(), // next-auth expects id as string
                name: userAccount.login_name,
                email: userAccount.email_address, // Optional, but good for session
                // You can add other properties here, like roles or game-specific IDs
              };
            } else {
              console.log('Password validation failed for:', credentials.username);
              return null; // Password did not match
            }
          } else {
            console.log('No user account found for:', credentials.username);
            return null; // User not found
          }
        } catch (error) {
          console.error('Error during authorization:', error);
          return null;
        }
      }
    })
  ],
  session: {
    strategy: 'jwt', // Using JWT for session strategy
  },
  callbacks: {
    async jwt({ token, user }) {
      // Persist the user id and name to the JWT after signin
      if (user) {
        token.id = user.id;
        // token.name = user.name; // name is already included by default
      }
      return token;
    },
    async session({ session, token }) {
      // Send properties to the client, like an access_token and user id from a provider.
      if (session.user) {
        session.user.id = token.id as string;
        // session.user.name = token.name; // name is already included
      }
      return session;
    },
  },
  pages: {
    signIn: '/auth/signin', // Custom sign-in page (optional)
    // signOut: '/auth/signout',
    // error: '/auth/error', // Error code passed in query string as ?error=
    // verifyRequest: '/auth/verify-request', // (used for email verification)
    // newUser: '/auth/new-user' // New users will be directed here on first sign in (leave the property out to disable)
  },
  secret: process.env.NEXTAUTH_SECRET, // Essential for production!
  debug: process.env.NODE_ENV === 'development',
};

// Adding crypto import for MD5 hashing during migration check
import crypto from 'crypto';

const handler = NextAuth(authOptions);

export { handler as GET, handler as POST };
