/**
 * Security Notes for Next.js Application Conversion
 */

// --- Password Hashing and Migration ---
// The original PHP application (based on qlib/auth.class.php) likely used MD5 for password handling,
// possibly in conjunction with a challenge-response mechanism for transmission. Stored passwords
// in the `user_accounts.passwd` field are assumed to be either:
//   a) Plaintext (highly insecure)
//   b) Simple MD5 hashes of the plaintext password (insecure)
//   c) MD5 hashes used in a more complex challenge-response system (still relies on MD5's weaknesses).

// **Critical Requirement: Migrate to a Strong Hashing Algorithm (bcrypt or Argon2)**
// `bcryptjs` has been chosen for the Next.js application.

// **Migration Strategy (On-the-fly during login):**
// 1.  **Identify Hash Type:** When a user attempts to log in via `/api/auth/[...nextauth].ts` (CredentialsProvider):
//     *   Retrieve the `userAccount` record from the database using Prisma.
//     *   Examine the `userAccount.passwd` field.
//         *   **bcrypt hash:** Typically starts with a prefix like `$2a$`, `$2b$`, or `$2y$`, followed by cost factor and a longer hash. Length is usually around 60 characters.
//         *   **MD5 hash:** A 32-character hexadecimal string.
//         *   **Plaintext:** If it doesn't match bcrypt or MD5 patterns, it might be plaintext. This requires careful checking.
//     *   To facilitate this, a new field could be added to `UserAccount` during a preliminary migration script, e.g., `password_hash_type: 'md5' | 'bcrypt' | 'plaintext'`. If not, rely on format/length heuristics (less reliable).

// 2.  **Password Verification & Re-hash:**
//     *   **If `password_hash_type` is 'bcrypt' (or looks like bcrypt):**
//         *   Use `await bcrypt.compare(providedPassword, storedHash)` to verify.
//     *   **If `password_hash_type` is 'md5' (or looks like MD5):**
//         *   Calculate `md5(providedPassword)`.
//         *   Compare with the stored MD5 hash.
//         *   **If verification succeeds:**
//             *   Immediately re-hash the `providedPassword` using `await bcrypt.hash(providedPassword, saltRounds)`.
//             *   Update the `userAccount.passwd` with the new bcrypt hash.
//             *   Optionally, update `userAccount.password_hash_type` to 'bcrypt'.
//             *   Log the successful migration for this user.
//     *   **If `password_hash_type` is 'plaintext':**
//         *   Directly compare `providedPassword === storedPassword`.
//         *   **If verification succeeds (HIGHLY INSECURE STATE):**
//             *   Immediately re-hash and update as with MD5. This is top priority.
//     *   **If any verification fails, deny login.**

// 3.  **New Signups:**
//     *   All new passwords submitted via the signup API route (`/api/auth/signup`) MUST be hashed using `bcryptjs.hash()` before being stored in `UserAccount.passwd`.
//     *   The `password_hash_type` for new users should be set to 'bcrypt'.

// **Risks and Considerations:**
// *   **MD5 is broken:** Passwords hashed with MD5 are vulnerable to collision attacks and rainbow table attacks. The migration window where MD5 hashes are still checked is a period of increased risk.
// *   **Plaintext passwords:** If any passwords are in plaintext, this is a critical vulnerability. Migration must be immediate.
// *   **User Communication:** Inform users about the security upgrade. Encourage users (especially those migrated from MD5/plaintext) to update their passwords proactively after their first successful login post-migration, even though their hash was upgraded. This is because their original password might have been compromised if the old database was ever breached.
// *   **Salt Rounds for bcrypt:** Use an appropriate number of salt rounds for `bcryptjs.hash()` (e.g., 10-12) to balance security and performance.

// --- Other Security Enhancements with NextAuth.js & General Practices ---
// *   **HTTPS:** Essential for all communication, especially login and signup, to protect credentials in transit. NextAuth.js sessions (even JWTs) should only be transmitted over HTTPS.
// *   **CSRF Protection:** NextAuth.js provides built-in CSRF protection for its routes. Ensure any custom API routes handling form submissions (especially those changing state) also implement CSRF protection if not using NextAuth.js for that specific form handling.
// *   **Session Management:**
//     *   NextAuth.js handles session tokens (JWTs or database sessions via Prisma Adapter).
//     *   Use secure, HttpOnly cookies for session tokens.
//     *   Regularly rotate `NEXTAUTH_SECRET`.
// *   **Input Validation:** Thoroughly validate all user inputs on both client and server (API routes) to prevent XSS, SQL injection (Prisma helps significantly here), and other injection attacks. Use libraries like Zod or Yup for schema validation.
// *   **Error Handling:** Avoid leaking sensitive information in error messages.
// *   **Rate Limiting:** Implement rate limiting on authentication routes (login, signup, password reset) to protect against brute-force attacks.
// *   **Challenge-Response Replacement:** The old PHP challenge-response mechanism (dependent on `md5` and a session-specific challenge) is being replaced by standard HTTPS for secure credential transmission and strong server-side hashing with bcrypt.

// --- Email Confirmation for Signup ---
// *   If `appConfig.sendmailValidationRequired` is true:
//     *   The `/api/auth/signup` route should generate a unique, time-limited verification token.
//     *   Store this token securely (e.g., hashed in the database, associated with the user).
//     *   Send an email to the user with a verification link containing this token.
//     *   The `UserAccount` should have an `emailVerified: false` status until confirmed.
//     *   An API route like `GET /api/auth/verify-email?token={token}` will handle token validation and activate the account.
//     *   This prevents takeover with unverified email addresses.
