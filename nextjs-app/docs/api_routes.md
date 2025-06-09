/**
 * This file contains notes on PHP scripts or procedural logic from the original codebase
 * that should be reimplemented as API Route Handlers in the Next.js application.
 * This often involves handling form submissions, actions, or operations that
 * require server-side processing and database interaction.
 */

// --- Authentication API Routes (mapped from login.php, logout.php, qlib/auth.class.php) ---

// Login, Logout, Session Handling (Managed by NextAuth.js):
// Route: /api/auth/[...nextauth].ts (catch-all route for next-auth)
// - This single Next.js route handles multiple authentication actions:
//   - POST /api/auth/callback/credentials (Handles actual login submission from CredentialsProvider)
//   - GET/POST /api/auth/signin (Serves default sign-in page, or handles custom page submission)
//   - GET/POST /api/auth/signout (Handles logout)
//   - GET /api/auth/session (Provides session data to client)
//   - GET /api/auth/csrf (Provides CSRF token)
//   - etc.
// - Login Logic (within CredentialsProvider `authorize` function in `[...nextauth].ts`):
//   - Fetches `UserAccount` by `login_name` using Prisma.
//   - **Password Verification & Migration:**
//     - If stored password hash is MD5 (or plaintext), verify using that method.
//     - If successful, immediately re-hash password with bcryptjs and update the `UserAccount` record in DB.
//     - If stored password hash is already bcrypt, verify using `bcryptjs.compare()`.
//   - Returns user object (`{ id, name, email }`) on success for session creation.
// - Session Management: JWTs by default, database sessions via Prisma Adapter is an option.
// - CSRF Protection: Handled by NextAuth.js for relevant routes.
// - Replaces: `login.php` (core logic), `logout.php`, PHP session parts of `qlib/auth.class.php`.

// Signup API Route:
// API Route: POST /api/auth/signup
// Request Body: { loginName, password, passwordVerify, email, emailVerify, firstName?, lastName?, disclaimerAgreed, ...other_optional_fields_from_UserAccount }
//   - `firstName`, `lastName` are optional but can be provided.
//   - `disclaimerAgreed` (boolean) is expected from the client.
//   - Other fields like icq, aim, country, etc., could be added if the signup form collects them.
// Logic (based on `Q_ValidateSignup` class from `qlib/auth.class.php`):
//   1. Comprehensive input validation (use `validationUtils.ts` for loginName, password, email).
//   2. Check uniqueness of `loginName` and `email` (Prisma: `UserAccount` model).
//   3. Securely hash the password using `bcryptjs.hash()`.
//   4. Create new `UserAccount` record (Prisma).
//   5. If email confirmation is enabled (appConfig.sendmailValidationRequired):
//      a. Generate, store, and email a verification token.
//      b. User marked as unverified until token is used.
//   6. Return success or error JSON.

// Email Verification API Route (if email confirmation is enabled):
// API Route: GET /api/auth/verify-email?token={verificationToken} (or POST)
// Logic:
//   1. Validate token.
//   2. Mark `UserAccount.emailVerified` as true (Prisma).
//   3. Invalidate/delete token.
//   4. Redirect or return success.

// --- Forum API Routes (mapped from forum.php, forum_clan.php, forum_game.php, posting.php) ---
// Using GameForumMessage for general game forums and ClanForumMessage for clan forums.
// A message with reply_to = 0 (or a designated initial value like NULL if schema allows) is a thread starter.

// General Game Forums:
// API Route: GET /api/forums/game/threads?page={page_num}&limit={limit_num}
// Auth: User authenticated.
// Logic:
//   1. Fetch `GameForumMessage` where `reply_to == 0` (or designated thread starter value).
//   2. Include author details (Prisma: `User` -> `UserAccount` for name).
//   3. Include count of replies and last reply timestamp/author for each thread (requires subqueries or aggregation).
//   4. Implement pagination.
//   5. Return list of threads.
//
// API Route: GET /api/forums/game/threads/[threadId]?page={page_num}&limit={limit_num}
// Auth: User authenticated.
// Logic:
//   1. Fetch the main thread post (`GameForumMessage` where `message_id == threadId`). Include author.
//   2. Fetch replies (`GameForumMessage` where `reply_to == threadId`). Paginate replies. Include author.
//   3. Return thread starter post and its paginated replies.
//
// API Route: POST /api/forums/game/threads
// Auth: User authenticated.
// Request Body: { subject: string, text: string }
// Logic:
//   1. Validate inputs.
//   2. Create new `GameForumMessage` with `reply_to = 0` (or designated starter value), `subject`, `text`, `sender_id` (from session), `sender_name` (from session user).
//   3. Return new thread data.
//
// API Route: POST /api/forums/game/threads/[threadId]/reply
// Auth: User authenticated.
// Request Body: { text: string }
// Logic:
//   1. Validate `threadId` exists.
//   2. Validate `text`.
//   3. Create new `GameForumMessage` with `reply_to = threadId`, `text`, `sender_id`, `sender_name`. `subject` can be empty or "Re: [original_subject]".
//   4. Update last reply timestamp/info on the parent thread (original `GameForumMessage` with `message_id == threadId`). This might require adding fields like `last_reply_at`, `last_reply_by_id` to `GameForumMessage`.
//   5. Return new post data.

// Clan Forums:
// API Route: GET /api/clans/[clanId]/forum/threads?page={page_num}&limit={limit_num}
// Auth: User authenticated and member of `clanId` (or admin).
// Logic: Similar to game forum threads, but scoped to `ClanForumMessage.clan_id == clanId`.
//
// API Route: GET /api/clans/[clanId]/forum/threads/[threadId]?page={page_num}&limit={limit_num}
// Auth: User authenticated and member of `clanId` (or admin).
// Logic: Similar to game forum thread view, but for `ClanForumMessage`.
//
// API Route: POST /api/clans/[clanId]/forum/threads
// Auth: User authenticated and member of `clanId`.
// Request Body: { subject: string, text: string }
// Logic: Creates `ClanForumMessage` with `reply_to = 0`, `clan_id`.
//
// API Route: POST /api/clans/[clanId]/forum/threads/[threadId]/reply
// Auth: User authenticated and member of `clanId`.
// Request Body: { text: string }
// Logic: Creates `ClanForumMessage` with `reply_to = threadId`, `clan_id`. Updates parent thread.

// Common Post Actions (Game & Clan forums):
// API Route: PUT /api/forums/posts/[postId]?type={game|clan}
// Auth: User authenticated, owner of post or moderator/admin.
// Request Body: { text: string, subject?: string (if it's a thread starter) }
// Logic: Updates `text` (and `subject` if applicable) of `GameForumMessage` or `ClanForumMessage`.
//
// API Route: DELETE /api/forums/posts/[postId]?type={game|clan}
// Auth: User authenticated, owner of post or moderator/admin.
// Logic: Deletes `GameForumMessage` or `ClanForumMessage`. Consider soft delete.
//        If a thread starter is deleted, decide on handling replies (cascade delete or mark as orphaned).
//
// API Route: POST /api/forums/posts/[postId]/log-to-diary?type={game|clan}
// Auth: User authenticated.
// Logic: Fetches post content and creates a new `DiaryEntry` for the user.

// Game-Specific Authentication / Joining a Game:
// API Route: POST /api/game/join
// Request Body: { gameDbName: string, adminGamePassword?: string }
// Auth: Requires active NextAuth.js session (global `UserAccount` must be logged in).
// Logic (based on `AuthUserGame` method from `qlib/auth.class.php`):
//   1. Retrieve `userAccount.login_id` from the NextAuth.js session.
//   2. Validate `gameDbName`: Check if a `SeGame` record with this `db_name` exists. If not, error.
//      (Assumption: Prisma is configured for the specific game DB context if multiple DBs are involved,
//       OR `gameDbName` is used to filter queries if User, Ship etc. tables for multiple games are in one DB,
//       prefixed by `gameDbName` - the former is more complex with Prisma, the latter requires schema adjustment.
//       For now, assume current Prisma context IS the target game, `gameDbName` is for validation against `SeGame` table).
//   3. Handle Admin Login to Game (if `userAccount.login_id === 1` or a global admin role):
//      a. If `adminGamePassword` is provided:
//         - Fetch `SeGame.admin_pw` for the `gameDbName`.
//         - Compare. If incorrect, return error.
//      b. If `adminGamePassword` is NOT provided (original PHP showed a form if `nd_ad_log` was not set):
//         - This API should expect the password directly. Client-side would handle prompting if needed.
//      c. On success:
//         - Update `SeGame.session_id` with the NextAuth session token/identifier if this cross-reference is maintained.
//         - Proceed to fetch/create game-specific `User` record for admin (see step 4).
//   4. Handle Regular User Login to Game:
//      a. Fetch game-specific `User` record using `userAccount.login_id` (and `gameDbName` if multi-tenant DB).
//      b. If `User` record exists:
//         - Check `banned_time`. If banned, return error with reason and ban expiry.
//         - Increment `game_login_count`.
//         - Update `User.last_login` timestamp (if this field is on the game-specific User table).
//         - Call game-specific score update function (`score_func` logic).
//         - Return success: `{ status: 'joined', gameDbName, needsSetup: false }`.
//      c. If `User` record DOES NOT exist (first time this `UserAccount` is joining this `gameDbName`):
//         - Create the game-specific `User` record, linking it to `userAccount.login_id`.
//           Initialize default values (turns, cash, tech, location, ship_id=1 for initial state before ship naming).
//           Set `game_login_count = 1`.
//         - Return success: `{ status: 'needs_setup', gameDbName, message: 'Welcome! Please name your first ship and fleet.' }`.
//           Client uses this to redirect to ship/fleet naming UI.
//   5. (Optional) Update NextAuth.js session: Add/update `activeGameDbName: gameDbName` and any relevant
//      game-specific roles or minimal user state to the JWT/session for easier client-side access.

// (Optional) Password Migration Status Check:
// API Route: GET /api/auth/migration-status
// Auth: Requires active NextAuth.js session.
// Logic:
//  1. Get user from session.
//  2. Check their `UserAccount.password_hash_type` (or however MD5/bcrypt status is stored).
//  3. Return status, e.g., { needsMigration: true/false }. Client can use this to prompt proactive password updates.

// From includes/location.inc.php:
// - Retire player (retire, sure, what_to_do, leader_id logic):
//   API Route: POST /api/player/retire
//   Request Body: { sure: boolean, clanAction?: 'disband' | 'assign', newLeaderId?: number }
//   Handles player retirement, including clan leadership transfer or disbanding.
//
// - Change fleet links (chng_lnks, lnks logic):
//   API Route: POST /api/fleet/links
//   Request Body: { commandFleetId: number, linkedFleetIds: number[] }
//   Updates fleet_link associations for selected fleets.
//
// - Command different ship (command GET param - deprecated in PHP):
//   This functionality is likely superseded by new_command for fleets.
//   If individual ship command is still needed, it would be a new API route.
//
// - Command different fleet (new_command GET param):
//   API Route: POST /api/player/command-fleet
//   Request Body: { fleetId: number }
//   Sets the user's active ship_id and location based on the new command fleet's flagship.
//
// - Toggle Ramscoop (ramfleet GET param):
//   API Route: POST /api/fleet/toggle-ramscoop
//   Request Body: { fleetId: number }
//   Toggles the ramscoop status for a given fleet.
//
// - SS0 Bug Fix (sszero GET param):
//   This is likely an admin/debug function.
//   API Route: POST /api/admin/fix-ss0 (requires admin authentication)
//   Request Body: { userId: number }
//   Relocates a user if they are stuck or their ship is at location 0.

// From includes/chalset.inc.php:
// - Challenge generation and storage:
//   API Route: GET /api/challenge/generate (or POST if it takes parameters like sessid)
//   Generates a unique challenge token, stores it in qbase_challenge_record,
//   and returns the token to the client.
//   The original script echoed the challenge; an API route would return it in JSON.
//   The `sessid` was empty in the PHP script; if it's needed, it should be part of the request or session.

// From includes/random_events.inc.php:
// - random_event_checker(star, user, autowarp):
//   This logic should be integrated into the main player movement API route (e.g., /api/player/move).
//   After a move is calculated and the destination star is known, the API route should:
//     1. Check `star.event_random`.
//     2. If Black Hole (type 1) and user is NOT a newbie:
//        - Trigger the black hole processing (see below). This might involve redirecting the player
//          or returning specific data indicating the black hole event occurred and what the outcome was.
//     3. If Nebula/Solar Storm (type 2 or 12):
//        - Update player's ships' shields to 0 in the current location (using Prisma).
//        - Return this status change in the API response for the move.
//     4. If Black Hole (type 1) and IS a newbie:
//        - The API response for the move should indicate this specific scenario so the client can show
//          the <NewbieBlackHoleWarning /> component.
//
// - black_hole(user, star, multi):
//   This describes the server-side processing for a black hole event. It shouldn't be a separate API route
//   but rather a part of the player movement logic or a dedicated server-side function called by it.
//   Server-Side Function: handleBlackHoleEvent(user, star, prisma)
//   Involves:
//     - Calculating a new random star location using `safe_rand_star`.
//     - Updating locations for all of the user's fleets and ships in the black hole system (Prisma).
//     - Updating the user's own location if their command ship moved (Prisma).
//     - Calculating and applying damage to affected ships (shields, fighters) (Prisma).
//     - Posting a news item about the event (Prisma).
//     - The result (new location, damage report) should be returned to the client as part of the
//       response from the initial move action that triggered the black hole.
//
// - safe_rand_star(current_star_id):
//   Server-Side Utility: getSafeRandomStar(currentStarId: number, prisma: PrismaClient): Promise<number | null>
//   Fetches star_ids from the Star model where event_random != 1 and star_id != currentStarId,
//   then returns a random one. To be used by `handleBlackHoleEvent`.

// From includes/fleet_funcs.inc.php:
// - Transfer_ClaimedShip():
//   API Route: POST /api/ships/{shipId}/claim
//   Updates a ship's owner, clan, and fleet ID after being claimed.
//   Request Body: (Likely just needs authentication context for the new owner)
//
// - Check_FleetMax() / Check_FleetMax_FleetCommand():
//   This is server-side validation logic, not a standalone API route.
//   It should be integrated into any API route that adds a ship to a user's fleet
//   (e.g., purchasing, transferring, claiming).
//   If fleet limits are exceeded, the API route should return a specific error
//   (e.g., { error: "fleet_full", reason: "max_warships" | "max_total_ships" }).
//   The client then uses this error to trigger the Ask_CreateNewFleet UI flow.
//
// - Check_ToCreateFleet(fleetnum):
//   API Route: POST /api/fleets
//   Request Body: { fleetName: string, fleetNum?: number } (fleetNum could be optional, server assigns if not provided)
//   Checks if fleet number is used, then creates a new fleet for the user.
//   Returns the new fleet data. This API is called after Ask_CreateNewFleet form submission.

// From includes/ship_loading.inc.php:
// - Load_Resource(fill_option, cap_option, resource, fill_type, file, user_amount, source_id):
//   API Route: POST /api/ships/load-resource
//   Request Body: { fillOption: 0|1|2, capacityOption: 0|1|2, resource: string, sourceType: 0|1, amount?: number, sourceId?: number }
//   Handles the complex logic of loading resources onto ships/fleets from starports or planets.
//   Involves:
//     - Validating user turns.
//     - Calculating available capacity on ships/fleets.
//     - Determining actual amount to load based on user input, capacity, credits, and source availability.
//     - Updating ship cargo (Prisma: Ship model).
//     - Deducting credits (Prisma: User model, using helper like take_cash).
//     - Deducting resources from source (Prisma: Planet or Port model).
//     - Charging turns (Prisma: User model, using helper like charge_turns).
//     - Returning a status message.
//
// - Unload_Resource(fill_option, cap_option, resource, fill_type, file, user_amount, user_credits, source_id):
//   API Route: POST /api/ships/unload-resource
//   Request Body: { fillOption: 0|1|2, capacityOption: 0|1|2, resource: string, targetType: 0|1, amount?: number, creditsToGain?: number, targetId?: number }
//   Handles logic for unloading/selling resources from ships/fleets to starports or planets.
//   Involves:
//     - Validating user turns.
//     - Calculating available resources on ships/fleets.
//     - Determining actual amount to unload based on user input, available resources, or desired credits.
//     - Updating ship cargo (Prisma: Ship model).
//     - Adding credits (Prisma: User model, using helper like give_cash).
//     - Adding resources to target (Prisma: Planet or Port model).
//     - Charging turns (Prisma: User model).
//     - Returning a status message.

// General Notes for API Routes:
// - All routes performing mutations (POST, PUT, DELETE) should be protected against CSRF.
// - Authentication and authorization checks are critical for all routes, especially those
//   modifying data or performing sensitive actions.
// - Input validation should be performed on all request bodies and parameters.
// - Use Prisma Client for all database interactions.
// - Return appropriate HTTP status codes and JSON responses.

// From includes/planet_funcs.inc.php:
// - do_damage(amount, fromUser, targetUser, targetShip):
//   This is core combat resolution logic, likely part of a larger attack handling API route
//   (e.g., POST /api/combat/attack-planet or POST /api/combat/attack-ship if generic).
//   Involves:
//     - Calculating damage distribution (shields vs fighters).
//     - Updating attacker and target user stats (kills, losses, points) (Prisma: User model).
//     - Updating target ship's fighters and shields (Prisma: Ship model).
//     - Handling ship destruction:
//       - If escape pod: delete ship, update user to basic state (Prisma: Ship, User models).
//       - If normal ship: scatter resources (Prisma: Star model), delete ship (Prisma: Ship model),
//         assign new ship to target user (find existing or create escape pod), update user stats (Prisma: User model).
//   This function is highly dependent on accurate game state and context (attacker, defender, ships involved).
//
// - report_illegal_planet_attack / report_illegal_planet_claim:
//   These functions trigger an admin alert (send_alert).
//   Server-Side Action: Implement a robust logging/alerting system (e.g., logging to a file/service,
//   emailing admins). This could be a utility function called from relevant API routes
//   when suspicious activity is detected during validation (e.g., in an API route that handles planet attacks/claims).
//   Example: reportSuspiciousActivity(type: 'illegal_planet_attack' | 'illegal_planet_claim', details: object)
//
// - conditions(user, planet) -> canUserPerformPlanetTransfer (in planetUtils.ts):
//   This validation logic (checking user signup time against min_transfer_time) should be used
//   within API routes that handle actions like transferring items to/from planets not owned by the user.

// From includes/clan_funcs.inc.php:
// - update_clans():
//   Server-Side Maintenance Logic (e.g., called by a cron job or admin panel action).
//   Could be an API Route like POST /api/admin/cron/update-clan-stats (requires admin auth).
//   Logic:
//     1. Fetch all clans (Prisma: Clan model).
//     2. For each clan:
//        a. Aggregate `score` and `fighters_killed` from its members (Prisma: User model).
//        b. Count total members (Prisma: User model).
//        c. Apply normalization factor (from appConfig.clanMemberLimit * 10).
//        d. Update the Clan record with these new aggregated stats (Prisma: Clan model).
//   This function is crucial for keeping clan leaderboards and stats accurate.

// --- Admin Panel API Routes (from admincp/* files) ---
// All admin routes must be protected by `ensureAdmin()` util.

// Dashboard Summary (from admincp/index.php):
// API Route: GET /api/admin/dashboard-summary
// Auth: Admin only.
// Logic:
//   1. Fetch game status (paused, rejoin delay status from `SeGame` and `DbVar`).
//   2. Potentially fetch other summary stats (e.g., number of players, active players - from `User`, `UserAccount`).
//   3. Return summary data.

// Pause/Unpause Game (from admincp/index.php):
// API Route: POST /api/admin/game/pause
// Auth: Admin only.
// Request Body: { pause: boolean } (true to pause, false to unpause)
// Logic:
//   1. Update `SeGame.paused` status (Prisma).
//   2. Post news item (`News` model).
//   3. If unpausing, potentially trigger email to users (original PHP `mail_users`). This requires email service.
//   4. Return success.

// Toggle Rejoin Delay (from admincp/index.php):
// API Route: POST /api/admin/settings/toggle-rejoin-delay
// Auth: Admin only.
// Request Body: { enable: boolean }
// Logic:
//   1. Update `DbVar` for `rejoin_delay` (Prisma).
//   2. **NOTE:** Original PHP added/dropped a column from `user_accounts` table (`ALTER TABLE`).
//      This is highly problematic for Prisma and schema management.
//      A better approach: The `rejoin_delay` DbVar controls the *logic* of checking rejoin delay.
//      The actual timestamp of when a user can rejoin after retiring would be stored in a dedicated
//      field on the `UserAccount` or a separate `RetiredUser` table. No ALTER TABLE needed.
//      This API should only toggle the DbVar. Manual DB migration or a different strategy is needed for the timestamp field.
//   3. Return success.

// User Management:
// API Route: GET /api/admin/users?page={pageNum}&limit={limitNum}&searchLogin={query}&searchEmail={query}&sortBy={field}&sortOrder={asc|desc}
// Auth: Admin only.
// Logic:
//   1. Fetch `UserAccount` records with pagination, filtering (by login_name, email), and sorting.
//   2. Include related `Permission` data.
//   3. Return list of users and pagination details.
//
// API Route: GET /api/admin/users/[userId]
// Auth: Admin only.
// Logic:
//   1. Fetch specific `UserAccount` by `userId`. Include related `Permission`, game-specific `User` data (if relevant for admin view).
//   2. Return user details.
//
// API Route: PUT /api/admin/users/[userId]
// Auth: Admin only.
// Request Body: { /* fields from UserAccount to update */, permissions: { /* fields from Permission */ } }
// Logic:
//   1. Validate input.
//   2. Update `UserAccount` record (Prisma).
//   3. Update related `Permission` record (Prisma).
//   4. Consider auditing this change.
//   5. Return updated user details.
//
// API Route: DELETE /api/admin/users/[userId]
// Auth: Admin only.
// Logic:
//   1. Soft delete or hard delete `UserAccount` (and related game `User` records, handle cascades/cleanup).
//   2. Consider implications (e.g., what happens to their planets, ships, posts). Full deletion is complex.
//      Soft delete (e.g., marking as inactive) is often safer.
//   3. Return success.

// Game Variables Management (from admincp/admin_vars.php):
// API Route: GET /api/admin/game-variables
// Auth: Admin only.
// Logic:
//   1. Fetch all records from `DbVar` table (Prisma).
//   2. Return list of game variables.
//
// API Route: PUT /api/admin/game-variables
// Auth: Admin only.
// Request Body: { variables: Array<{ name: string, value: string }> }
// Logic:
//   1. For each variable in the array:
//      a. Validate `name` exists in `DbVar`.
//      b. Validate `value` against `DbVar.min`, `DbVar.max`, `DbVar.type` (requires careful type conversion and validation as `DbVar.value` is string).
//      c. Update `DbVar.value` for that variable (Prisma).
//   2. Preferably perform updates in a transaction.
//   3. Return success or updated variables.

// --- Universe, Map, and Location API Routes (from main_map.php, star_map.php, location.php) ---

// Galaxy Map Data:
// API Route: GET /api/universe/galaxy-map
// Auth: Public or protected depending on game rules.
// Logic:
//   1. Fetch all (or a relevant subset of) `Star` records (star_id, name, x_loc, y_loc, sys_type, links, wormhole).
//   2. Fetch relevant `DbVar` values like `uv_universe_size`, `uv_show_warp_numbers`.
//   3. Return data for client-side map rendering.
//      (Replaces data fetching in `main_map.php`)

// Star System Information:
// API Route: GET /api/universe/star-system/[systemId]
// Auth: Public or protected.
// Logic:
//   1. Get `systemId` from path.
//   2. Fetch `Star` record for `systemId`.
//   3. Include related data: `Planet`s in system, `Port`s, `Bmrkt`s, `Shipyard`s.
//   4. Include a summary of `Ship`s in system (e.g., counts, basic details, limited list).
//   5. Fetch wormhole destination details if `Star.wormhole` is set.
//   6. Return comprehensive system data.
//      (Replaces data fetching for `location.php`'s main display and potentially parts of `star_map.php`)

// Player's Current Location & System Details:
// API Route: GET /api/player/location
// Auth: Protected (must be self).
// Logic:
//   1. Get authenticated user's `login_id`.
//   2. Fetch `User.location` (current `star_id`).
//   3. Fetch detailed information for that `star_id` (similar to `GET /api/universe/star-system/[systemId]`).
//   4. Return player-specific context (e.g., current ship ID) and system details.
//      (Provides data for `location.php` from player's perspective)

// Player Movement:
// API Route: POST /api/player/move
// Auth: Protected (must be self).
// Request Body: { targetSystemId: number }
// Logic:
//   1. Get authenticated user's `login_id` and current `User.location`.
//   2. Validate `targetSystemId` (is it linked to current system? from `Star` links).
//   3. Validate turns/fuel (simplified: check basic turn cost; full logic is complex).
//   4. **Perform Random Event Check at Destination (critical):**
//      - Fetch `Star.event_random` for `targetSystemId`.
//      - If event (Black Hole, Nebula, etc.):
//        - Trigger event-specific logic (e.g., scatter player if Black Hole & not newbie, deplete shields if Nebula).
//        - This might change the `finalSystemId` or apply effects.
//        - This logic is based on `random_event_checker()` and `black_hole()` from `random_events.inc.php`.
//   5. Update `User.location` to `finalSystemId` and deduct turns (Prisma). Increment `User.turns_run`.
//   6. Return new location, user status, and any event messages/outcomes.
//      (Replaces navigation logic from `location.php`)

// From includes/ship_purchase_funcs.inc.php:
// - Main Ship Purchase Flow (combining Add_ShipToDatabase, Check_Shipyard, Add_ShipUpgrades, Update_DatabaseForMassPurchase):
//   API Route: POST /api/ships/purchase
//   Request Body: { shipTypeId: number, name?: string, fleetId?: number, quantity: number (for mass purchase) }
//                 (plus context like bmrkt_id or asyrd_id if applicable, from client state)
//   Logic:
//     1. `Check_Shipyard` logic: Validate if the ship type can be purchased at the user's current location/shop type.
//        Return error if validation fails. Note if black market fine applies from the return.
//     2. Validate user can afford (cash, tech for the ship type and quantity) (Prisma: User, ShipType models).
//     3. Validate fleet capacity (using logic from `Check_FleetMax` in fleet_funcs, which needs to be available as a server-side service/utility).
//        If fleet is full, return error indicating new fleet creation is needed (client handles this flow, potentially calling POST /api/fleets).
//     4. For each ship to purchase (e.g., loop for mass purchase, though original PHP seems to do one-by-one and accumulate totals for a final message):
//        a. `Add_ShipToDatabase` logic: Create the new ship record (Prisma: Ship model).
//           - Determine ship name (user provided or generated default like "Quotes 01").
//           - Assign to user, target fleet, user's current location.
//           - Copy base stats from ShipType. Set timestamp.
//        b. `Add_ShipUpgrades` logic: Create associated UpgradeUnit record and apply default upgrades
//           (Prisma: UpgradeUnit, DefaultUpgradeLoad, UpgradeList models).
//     5. `Update_DatabaseForMassPurchase` logic (adapted for single or multiple ships):
//        a. Deduct total cash and tech from user (Prisma: User model - using helper like `take_cash`, `take_tech`).
//        b. If user was in an escape pod (`user_ship.ship_categ == 0`), update their `ship_id` to the new ship
//           and update the `ship_id` of their current fleet (Prisma: User, Fleet models).
//        c. Apply black market visit penalty if applicable (`bm_visit` logic - needs to be a callable server-side function).
//     6. Return success response with details of purchased ship(s) and updated user stats.
//   All database operations for a single purchase transaction should be within a Prisma transaction if possible, especially for mass purchases.

// From includes/combat_funcs.inc.php:
// - Generate_Attack(uship, tship, enemy_fleet_id):
//   Main orchestrator for ship-to-ship combat.
//   API Route: POST /api/combat/attack-ship  (or perhaps /api/fleets/{fleetId}/attack-ship/{targetShipId})
//   Request Body: { targetShipId: number, (possibly targetFleetId: number if not in path) }
//   Logic:
//     1. Pre-combat validation (user turns, target presence, newbie safety, ship capabilities, self-attack, clan-mate check). Return specific errors if checks fail.
//     2. Charge user turns (Prisma: User model).
//     3. Fetch attacker/defender ship and user data, including upgrade-based bonuses (Prisma, calling utility functions like converted CheckShip_ForAttack/Defence).
//     4. Calculate initial attack damage and counter-attack damage (porting PHP math, mt_rand).
//     5. Call a "CombatService.resolveRound" function (see below) for attack and counter-attack.
//     6. Return detailed combat log / results as JSON.
//
// - Core Combat Logic (to be encapsulated in a server-side CombatService module using Prisma):
//   - resolveAttackRound(attackerUser, defenderUser, attackingShip, defendingShip, rawDamage, isRaid):
//     - Based on Inflict_ShipDamage.
//     - Applies damage to shields, then fighters.
//     - Handles ship destruction:
//       - Updates stats (kills, losses, points for users).
//       - If raiding: weakens ship, destroys offensive/defensive upgrades.
//       - If not raiding and ship destroyed:
//         - Calls handleShipDestruction (see below).
//     - Returns outcome (damaged, destroyed, escape_pod_destroyed, raid_complete).
//   - handleShipDestruction(defenderUser, destroyedShip, attackerUser):
//     - Based on Combat_SwitchShips.
//     - Finds new command ship for defender or calls provideEscapePod.
//     - Updates user's ship_id, location.
//     - Handles bounty logic (alternate_bounty_sys).
//   - provideEscapePod(defenderUser):
//     - Based on Provide_EscapePod and Add_EP_ToDatabase.
//     - Creates new fleet (fleet_num 0) for EP.
//     - Creates new Ship record with EP stats.
//     - Updates defenderUser's ship_id and location to the EP.
//   - (Ported) CheckShip_ForAttack / CheckShip_ForDefence: These utilities would be part of this service or called by it.
//
// - Attack_Ship (PHP function):
//   This PHP function wraps Inflict_ShipDamage and formats output. The output formatting will be client-side.
//   The news posting and messaging logic within it should be part of the API route after combat resolution.
//   - Post news of significant events (ship destroyed, EP destroyed) (Prisma: News model).
//   - Send messages to players (Prisma: Message model - if applicable, though not explicitly shown in Attack_Ship).

// --- Player Profile, Stats, and Options API Routes (from player_info.php, player_stat.php, options.php) ---

// Player Information / Profile:
// API Route: GET /api/player/profile/[userId]  (userId can be the current user or another user)
// Auth: Protected if viewing sensitive parts of own profile, or if profiles are not public.
//       Publicly viewable parts might be less restrictive.
// Logic:
//   1. Get `userId` from path. Get current authenticated user ID from session.
//   2. Determine view level (self, admin, clan-mate, public) based on session user vs. target userId.
//   3. Fetch data for `targetUserId` from `UserAccount` (global info like login_name, first_name, last_name, email - only if allowed by privacy rules/view level)
//      and game-specific `User` table (race, join date, last request, login count, stats, bounty, political rank, special items like bombs/devices).
//   4. Fetch lists of planets and ships owned by `targetUserId` from `Planet` and `Ship` tables (Prisma).
//   5. Return a combined JSON object. Filter sensitive fields based on view level.
//      (Replaces data aggregation in `player_info.php`)

// Player Action History:
// API Route: GET /api/player/history/[userId]?sortBy={field}&order={asc|desc}&limit={number}
// Auth: Protected. Only self or admin can view history.
// Logic:
//   1. Get `userId` from path. Validate viewer permissions.
//   2. Fetch records from `UserHistory` table for `userId`, applying sorting and limit.
//   3. Return history records.
//      (Replaces history viewing part of `player_info.php`)

// Self-Destruct Ships:
// API Route: POST /api/ships/self-destruct
// Auth: Protected (must be self).
// Request Body: { shipIds: number[] }
// Logic:
//   1. Validate user owns all `shipIds` and none are the current command ship.
//   2. Check user has enough turns (1 per ship).
//   3. Delete ships from `Ship` table (Prisma).
//   4. Post news item (Prisma: News model).
//   5. Deduct turns (Prisma: User model).
//   6. (Optional) Give bonus cash if many ships destroyed.
//   7. Return success/error.
//      (Replaces self_destruct part of `player_info.php`)

// Transfer Cash/Tech:
// API Route: POST /api/player/transfer
// Auth: Protected (must be self).
// Request Body: { targetUserId: number, amount: number, resourceType: 'cash' | 'tech' }
// Logic:
//   1. Validate `amount > 0`.
//   2. Check user has enough cash/tech (Prisma: User model).
//   3. Check `min_before_transfer` game rule (fetch from `DbVar` or appConfig).
//   4. Deduct from self, add to target user (Prisma: User model - needs transaction).
//   5. Send in-game message to target (Prisma: Message model).
//   6. Log to `UserHistory` (Prisma).
//   7. Return success/error.
//      (Replaces transfer logic in `player_info.php`)

// Retire Player (Admin Action):
// API Route: POST /api/admin/retire-player
// Auth: Protected (admin only).
// Request Body: { userId: number }
// Logic: Calls a server-side function that encapsulates `retire_user` logic (deleting/archiving user and related data).
//      (Replaces retire link in `player_info.php`)

// Player Statistics / Rankings:
// API Route: GET /api/stats/ranking?sortBy={field}&order={asc|desc}&filter={all|alive|misc_category}
// Auth: Public or protected depending on game rules.
// Logic:
//   1. Fetch users from `User` table, applying sorting and filtering.
//   2. Augment with `Race.race_name` and `Clan.clan_sym` if needed.
//   3. Return ranked list.
//      (Replaces `player_stat.php`)

// Get Player Options:
// API Route: GET /api/player/options
// Auth: Protected (must be self).
// Logic:
//   1. Get current user ID from session.
//   2. Fetch data from `UserOption` for this user.
//   3. Fetch `User.sig` (signature).
//   4. Fetch relevant fields from `UserAccount` (newsletter status, IM details).
//   5. Return combined options object.
//      (Replaces data fetching for `options.php`)

// Update Player Info (IMs, newsletter, signature):
// API Route: POST /api/player/info
// Auth: Protected (must be self).
// Request Body: { newsletter?: boolean, aim?: string, icq?: string, yim?: string, msn?: string, signature?: string }
// Logic:
//   1. Get current user ID from session.
//   2. Validate inputs.
//   3. Update `UserAccount` for newsletter, AIM, ICQ, YIM, MSN (Prisma).
//   4. Update `User.sig` for signature (Prisma).
//   5. Return success/error.
//      (Replaces `player_op == 2` part of `options.php`)

// Update Player Game Options:
// API Route: POST /api/player/options
// Auth: Protected (must be self).
// Request Body: { optionName1: value1, optionName2: value2, ... } (dynamic key-value pairs for game options)
// Logic:
//   1. Get current user ID from session.
//   2. For each option in request body:
//      a. Validate option name against `OptionList` table.
//      b. Validate value against `OptionList.option_min` and `OptionList.option_max`.
//      c. Update `UserOption` record for the user (Prisma).
//   3. Return success/error.
//      (Replaces `save_vars` part of `options.php`)

// Change Password:
// API Route: POST /api/player/change-password
// Auth: Protected (must be self).
// Request Body: { oldPassword: string, newPassword: string, newPassword2: string }
// Logic:
//   1. Get current user ID from session.
//   2. Validate `newPassword` (length, matches `newPassword2`, not same as login name, not same as old).
//   3. Fetch `UserAccount.passwd` (and potentially `UserAccount.login_name` for validation).
//   4. **Verify `oldPassword`**:
//      - If stored hash is bcrypt, use `bcryptjs.compare(oldPassword, storedHash)`.
//      - If stored hash is MD5 (requires identifying it, e.g. via length or a `password_hash_type` field):
//        - Compare `md5(oldPassword)` with stored MD5.
//   5. If `oldPassword` is valid:
//      - Hash `newPassword` using `bcryptjs.hash()`.
//      - Update `UserAccount.passwd` with the new bcrypt hash (Prisma).
//      - (Important) Invalidate other active sessions for this user (e.g., by changing a security stamp in UserAccount if using DB sessions with next-auth, or just rely on JWT short expiry).
//   6. Return success/error.
//      (Replaces password change part of `options.php`)
