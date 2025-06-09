/**
 * Utility functions for data validation.
 */

/**
 * Checks if the provided string is a syntactically valid email address.
 * Based on is_valid_email() from Q_ValidateSignup class in qlib/auth.class.php.
 * Original PHP regex: /^([a-z0-9\-_\.\&]+)@([a-z0-9\-]+\.)+[a-z]+$/
 * This regex is fairly basic. For production, consider a more comprehensive
 * email validation library or a more robust regex if needed, but this matches the original.
 * @param email The email string to validate.
 * @returns True if the email format is valid, false otherwise.
 */
export function isValidEmailFormat(email: string): boolean {
  if (!email) {
    return false;
  }
  // This regex is adapted from the PHP source.
  // It allows:
  // - User part: a-z, 0-9, hyphen, underscore, dot, ampersand
  // - Domain part: a-z, 0-9, hyphen, dot, followed by a TLD of letters
  const emailRegex = /^([a-z0-9\-_.&]+)@([a-z0-9\-]+\.)+[a-z]+$/i;
  // Added 'i' flag for case-insensitivity, as email local parts can be case-sensitive
  // but domain parts are not. The original PHP preg_match without 'i' would be case-sensitive.
  // However, common practice is to treat email addresses as case-insensitive for validation.
  return emailRegex.test(email);
}

/**
 * Placeholder for validating login names based on original PHP regex.
 * Original PHP regex from Q_ValidateSignup: eregi("[^a-z0-9~!@#$%&*_+-=£§¥²³µ¶Þ× ]",$l_name)
 * This checked for *invalid* characters. A validation function should check for *valid* characters.
 * The unicode characters are difficult to translate directly without knowing their specific intent.
 * @param loginName The login name to validate.
 * @returns True if valid, false otherwise.
 */
export function isValidLoginName(loginName: string): boolean {
  if (!loginName || loginName.length < 3) { // Original PHP had strlen($l_name) < 3 check
    return false;
  }
  // Simplified regex focusing on common characters from the original, excluding most Unicode for now.
  // The original PHP regex was: eregi("[^a-z0-9~!@#$%&*_+-=£§¥²³µ¶Þ× ]", $l_name)
  // This means the login name is INVALID if it contains any character NOT in the set:
  // a-z, 0-9, space, and ~!@#$%&*_+-=£§¥²³µ¶Þ×
  // We will construct a regex that ensures all characters ARE in this set.
  // The unicode characters £§¥²³µ¶Þ× are included directly.
  // The check `strcmp($l_name,htmlspecialchars($l_name))` also implies that
  // characters like '<', '>', '&' (if not part of an allowed entity like &amp;) are disallowed.
  // The regex below should generally prevent raw HTML special characters if they aren't listed.
  const loginNameRegex = /^[a-z0-9 ~!@#$%&*_+\-=£§¥²³µ¶Þ×™]+$/i;
  if (!loginNameRegex.test(loginName)) {
    return false;
  }

  // Additionally, the original PHP code checked `strcmp($l_name,htmlspecialchars($l_name))`.
  // This effectively checks if htmlspecialchars would change the string.
  // If it would, it means there are characters like <, >, &, ", ' that would be encoded.
  // We can simulate this by checking if the string contains these characters directly,
  // as our regex above doesn't explicitly forbid them if they were part of the unicode block by mistake.
  // However, the specific symbols like '&' are in the allowed list.
  // A simple check for '<' and '>' should be sufficient to prevent basic HTML tag injection.
  if (/[<>]/.test(loginName)) {
    return false;
  }

  return true;
}

/**
 * Validates password complexity based on original criteria.
 * - Not same as login name
 * - Minimum length (e.g., 5 characters)
 * @param password The password string.
 * @param loginName The login name string, to check against.
 * @returns True if valid, false otherwise.
 */
export function isValidPassword(password: string, loginName: string): boolean {
  if (!password || password.length < 5) { // Original PHP: strlen($passwd) < 5
    return false;
  }
  if (password === loginName) { // Original PHP: $passwd == $l_name
    return false;
  }
  // Add other complexity rules here if needed (e.g., uppercase, number, special char)
  return true;
}
