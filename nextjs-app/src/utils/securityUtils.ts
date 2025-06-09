import crypto from 'crypto';

/**
 * Generates a unique challenge token.
 * Similar to the PHP code: md5(uniqid(rand(), true))
 * This implementation uses Node.js crypto for a hex string, which is also suitable for tokens.
 * For stronger security needs, consider UUIDs or more entropy.
 * @returns A hexadecimal string token.
 */
export function generateChallengeToken(): string {
  // Create a random buffer, then convert to a hex string.
  // This is more standard and secure than uniqid/rand/md5 in modern JS/Node.
  const randomPart = crypto.randomBytes(8).toString('hex'); // 16 hex characters
  const timePart = Date.now().toString(36); // Time component, base36 encoded

  // Combine and hash if MD5-like properties (fixed length, specific hash) are desired,
  // otherwise, a sufficiently long random hex string is often good enough for unique tokens.
  // The original PHP implies a 32-character hex string from MD5.
  // To replicate that length and general feel, we can hash a unique string.
  const uniqueString = `${randomPart}-${timePart}-${crypto.randomBytes(4).toString('hex')}`;

  return crypto.createHash('md5').update(uniqueString).digest('hex');
}

// Example usage (not part of the utility, just for illustration):
// if (typeof require !== 'undefined' && require.main === module) {
//   console.log('Generated Challenge Token:', generateChallengeToken());
// }
