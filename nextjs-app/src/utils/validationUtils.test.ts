import { isValidEmailFormat, isValidLoginName, isValidPassword } from './validationUtils';

describe('validationUtils', () => {
  describe('isValidEmailFormat', () => {
    it('should return true for valid email addresses', () => {
      expect(isValidEmailFormat('test@example.com')).toBe(true);
      expect(isValidEmailFormat('user.name@example.co.uk')).toBe(true);
      expect(isValidEmailFormat('user_name@example-domain.com')).toBe(true);
      expect(isValidEmailFormat('user&name@example.com')).toBe(true); // As per original regex
    });

    it('should return false for invalid email addresses', () => {
      expect(isValidEmailFormat('testexample.com')).toBe(false);
      expect(isValidEmailFormat('test@example')).toBe(false);
      expect(isValidEmailFormat('@example.com')).toBe(false);
      expect(isValidEmailFormat('test@.com')).toBe(false);
      expect(isValidEmailFormat('test@example..com')).toBe(false);
      expect(isValidEmailFormat('')).toBe(false);
      // @ts-expect-error testing null explicitly
      expect(isValidEmailFormat(null)).toBe(false);
      // @ts-expect-error testing undefined explicitly
      expect(isValidEmailFormat(undefined)).toBe(false);
    });
  });

  describe('isValidLoginName', () => {
    it('should return true for valid login names', () => {
      expect(isValidLoginName('Player123')).toBe(true);
      expect(isValidLoginName('test_user-1')).toBe(true);
      expect(isValidLoginName('User With Spaces')).toBe(true); // Space is allowed by updated regex
      expect(isValidLoginName('~!@#$%&*_+-=£§¥²³µ¶Þ×™')).toBe(true); // Test special chars
    });

    it('should return false for invalid login names', () => {
      expect(isValidLoginName('u')).toBe(false); // Too short (min 3)
      expect(isValidLoginName('no<script>')).toBe(false); // Contains < or >
      expect(isValidLoginName('no>script<')).toBe(false);
      expect(isValidLoginName('')).toBe(false);
      // @ts-expect-error testing null explicitly
      expect(isValidLoginName(null)).toBe(false);
    });
  });

  describe('isValidPassword', () => {
    it('should return true for valid passwords', () => {
      expect(isValidPassword('password123', 'user123')).toBe(true);
      expect(isValidPassword('P@$$wOrd', 'anotherUser')).toBe(true);
    });

    it('should return false for invalid passwords', () => {
      expect(isValidPassword('pass', 'user123')).toBe(false); // Too short (min 5)
      expect(isValidPassword('user123', 'user123')).toBe(false); // Same as login name
      expect(isValidPassword('', 'user123')).toBe(false);
      // @ts-expect-error testing null explicitly
      expect(isValidPassword(null, 'user123')).toBe(false);
    });
  });
});
