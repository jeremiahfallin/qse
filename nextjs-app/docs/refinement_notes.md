# Code Refinement & Improvement Notes

This document lists areas in the codebase that could benefit from refactoring, further development, or more detailed review as the project progresses.

## API Routes (`src/app/api/`)

1.  **Error Handling Consistency:**
    *   While basic try-catch blocks are in place, establish a more standardized error response format across all API routes. Consider a global error handler or a utility function for creating consistent error JSON payloads.
    *   Ensure HTTP status codes are consistently and correctly used for different error types (400 for bad request/validation, 401 for unauthenticated, 403 for unauthorized, 404 for not found, 500 for server errors).

2.  **Input Validation:**
    *   Many routes have basic input validation (e.g., checking for required fields). Systematically review all routes, especially `POST` and `PUT` handlers, to ensure comprehensive validation of all incoming data.
    *   Consider using a dedicated validation library (e.g., Zod, Yup) for more complex validation schemas, which can provide better type safety and more detailed error messages. This is especially important for complex objects like `gameOptions` in `/api/player/options`.

3.  **Prisma Query Efficiency:**
    *   Review complex Prisma queries, especially those with multiple `include` statements or those used in frequently accessed routes (e.g., `/api/player/location`, `/api/universe/star-system/[systemId]`).
    *   Identify potential N+1 query problems or areas where fetching less data (using `select`) would be more efficient.
    *   For routes returning lists (e.g., `/api/admin/users`, `/api/forums/game/threads`), ensure pagination is correctly implemented and efficient, especially `count()` queries for total pages.

4.  **Service Layer Abstraction:**
    *   **Combat Logic:** The `CombatService` is a good start. Expand it to include more detailed game mechanics (upgrade bonuses, ship-specific abilities, diverse targeting). The service should ideally handle all combat calculations and return a deterministic result based on inputs, with the API route then handling Prisma transactions and side effects (news, messages).
    *   **User Actions:** For complex user actions involving multiple database updates (e.g., `POST /api/player/retire`, `POST /api/ships/purchase`), consider moving the core logic into service functions to keep API route handlers cleaner and more focused on request/response.
    *   **Configuration Management:** The `DbVar` updates in `/api/admin/game-variables` need robust type checking and validation based on `DbVar.type`, `min`, `max`. This logic could be part of a `GameConfigService`. The regeneration of a static config file (PHP `build_vars.php` equivalent) needs implementation.

5.  **Security Enhancements:**
    *   **Password Migration:** The current password migration logic in `[...nextauth].ts` makes a simplifying assumption about MD5 hashes. A more robust solution would involve analyzing the exact format of `$user['passwd']` from the PHP system to ensure accurate verification during the transition. A field like `UserAccount.password_hash_type` would be beneficial.
    *   **Session Invalidation:** Implement robust session invalidation strategies, especially after sensitive actions like password changes. For NextAuth.js with JWTs, this might involve managing a "password changed at" timestamp in the JWT or using a database session adapter for easier server-side invalidation.
    *   **Admin Privileges:** The current admin check (`userId === 1`) is basic. Transition to a role-based system using the `Permission` table for more granular control, and ensure the `next-auth` session reflects these roles/permissions.
    *   **Rate Limiting:** Implement rate limiting on sensitive endpoints like login, signup, password reset, and potentially high-traffic game actions to prevent abuse.

6.  **TODOs in Code:**
    *   Systematically search for and address all `// TODO:` comments left during development. Examples:
        *   Full escape pod logic in combat.
        *   Detailed combat log creation.
        *   Fetching last reply details for forum thread lists.
        *   Updating thread metadata (last post, reply count) upon new replies.
        *   Type-specific validation for `DbVar` updates.
        *   Race name and Politics rank name lookups in API responses.

7.  **File/Code Organization:**
    *   As the number of utility functions grows, consider further splitting them into more domain-specific files within `src/utils/` or `src/lib/` (e.g., `gameMechanicsUtils.ts`, `formatUtils.ts`).
    *   Ensure consistent naming conventions for files, functions, and variables.

8.  **Multi-Game Database Context (if applicable):**
    *   The assumption that Prisma client connects to a single game's operational DB was made. If the system needs to support multiple active game databases selected by `gameDbName`, the Prisma interaction strategy needs significant refactoring (e.g., dynamic datasources, separate Prisma clients per game, or a multi-tenant schema design). This is a major architectural consideration.

9.  **Error Reporting and Logging:**
    *   Implement a more structured server-side logging solution for errors and important events (e.g., admin actions, critical failures).
    *   The `send_alert` functionality from PHP for admin notifications needs a proper replacement (e.g., email service, dedicated logging platform).

10. **Code Duplication:**
    *   Review areas where similar logic might be duplicated (e.g., fetching user data in multiple API routes) and consolidate into reusable functions or services.

## Utility Functions (`src/utils/`, `src/lib/`)

1.  **`validationUtils.ts`:**
    *   `isValidLoginName`: The Unicode character handling in the regex is still a direct port and might not be optimal or fully compatible with JavaScript's regex engine nuances for all cases. Review and test thoroughly.
    *   Add more specific password strength validation if desired beyond current checks.
2.  **`commonUtils.ts`:**
    *   `formatLegacyTextToHtml` (from `mcit`) and `filterProfanity` (from `Clean_Text`) are currently placeholders. They require significant implementation or replacement with robust libraries.
    *   The missing `calc_perc` function (used in PHP `Print_ClanDetailsFull`) needs to be found or reimplemented.
3.  **`planetUtils.ts`:**
    *   `canUserPerformPlanetTransfer`: Ensure the logic for `minTransferTimeDays` correctly reflects game rules.
4.  **`combatService.ts`:**
    *   This is a very simplified first pass. It needs significant enhancements to include:
        *   Accurate damage formulas from the original game (considering ship types, weapon types from upgrades, armor, etc.).
        *   Integration of `getShipAttackBonus` and `getShipDefenseBonus` (which themselves need to be fully implemented using Prisma to fetch and calculate from `UpgradeUnit` and `UpgradeList`).
        *   More sophisticated targeting logic if desired (not just strongest vs. strongest).
        *   Handling of ship-specific abilities or configurations (`Ship.config`).
        *   Full ship destruction logic including escape pod creation (`handleShipDestruction` calling ported `Provide_EscapePod` logic).
        *   Saving detailed combat logs.

## Prisma Schema (`prisma/schema.prisma`)
*   Review relations for correctness and ensure cascading deletes or other referential actions are appropriate if not already set.
*   Consider if `Bytes` fields for forum text (`GameForumMessage.text`, `ClanForumMessage.text`) should indeed be `String @db.Text` or `@db.LongText` if they are always intended to be UTF-8 text. `Bytes` is safer for unknown binary/encoding but less convenient for text manipulation. (Current implementation of API routes decodes them to UTF-8).
*   The `User.login_id` being both `@id` and an FK to `UserAccount.login_id` is a valid way to do a 1-to-1 (or 1-to-0..1 if User can be missing for a UserAccount in a game context). Ensure this is the intended long-term design for game-specific user data.
