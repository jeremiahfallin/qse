# Manual API Testing Checklist

This document outlines a checklist for manually testing key API routes implemented in the Next.js application. It should be used to verify functionality after implementation and before deployment.

## Authentication (`/api/auth/...`)

### 1. `POST /api/auth/signup`
   - **Auth:** Public
   - **Request Body:**
     ```json
     {
       "loginName": "newuser",
       "password": "password123",
       "passwordVerify": "password123",
       "email": "newuser@example.com",
       "emailVerify": "newuser@example.com",
       "firstName": "Test",
       "lastName": "User",
       "disclaimerAgreed": true
     }
     ```
   - **Success Conditions:**
     - Returns `201 Created` status.
     - Response body includes `{ message: 'User account created successfully.', userId: <number> }`.
     - `UserAccount` table in DB has a new entry with correctly hashed password.
     - Default `Permission` and `UserOption` records created if applicable.
   - **Error Conditions:**
     - Missing required fields (e.g., `loginName`, `password`, `email`) -> `400 Bad Request`.
     - Passwords do not match -> `400 Bad Request`.
     - Emails do not match -> `400 Bad Request`.
     - Disclaimer not agreed -> `400 Bad Request`.
     - Invalid `loginName` (too short, invalid chars) -> `400 Bad Request`.
     - Invalid `password` (too short, same as loginName) -> `400 Bad Request`.
     - Invalid `email` format -> `400 Bad Request`.
     - `loginName` already exists -> `409 Conflict`.
     - `email` already exists -> `409 Conflict`.

### 2. Login via `[...nextauth].ts` (effectively `POST /api/auth/callback/credentials`)
   - **Auth:** Public (handled by NextAuth sign-in page)
   - **Request (indirectly via NextAuth form/client):** `username`, `password`
   - **Success Conditions:**
     - User is redirected to the intended page after login.
     - Session cookie/JWT is set.
     - `GET /api/auth/session` returns the authenticated user's session.
     - If an old MD5/plaintext password was used, it's updated to bcrypt in `UserAccount.passwd`.
   - **Error Conditions:**
     - Invalid username -> NextAuth error page / message.
     - Incorrect password -> NextAuth error page / message.

### 3. Logout via `[...nextauth].ts` (effectively `POST /api/auth/signout`)
   - **Auth:** Authenticated User (handled by NextAuth sign-out page/client call)
   - **Success Conditions:**
     - User is signed out.
     - Session cookie/JWT is cleared.
     - `GET /api/auth/session` returns unauthenticated status.

## Game-Specific Authentication

### 1. `POST /api/game/join`
   - **Auth:** Authenticated User (Global NextAuth session)
   - **Request Body:** `{ "gameDbName": "your_game_db_name_from_SeGame_table", "adminGamePassword"?: "game_admin_pass_if_user_is_global_admin" }`
   - **Success Conditions:**
     - Regular user, first time: Returns `200 OK` with `{ status: 'needs_setup', gameDbName, ... }`. Game-specific `User` record created.
     - Regular user, returning: Returns `200 OK` with `{ status: 'joined', gameDbName, needsSetup: false, user: {...} }`. `User.game_login_count` incremented.
     - Global admin, correct game password: Returns `200 OK` (similar to regular user, may have different permissions within game context).
   - **Error Conditions:**
     - User not authenticated (global) -> `401 Unauthorized`.
     - `gameDbName` missing -> `400 Bad Request`.
     - Invalid `gameDbName` (not in `SeGame` table) -> `404 Not Found`.
     - Global admin provides incorrect `adminGamePassword` -> `403 Forbidden`.
     - User banned from this game (`User.banned_time`) -> `403 Forbidden`.

## Player Information & Options

### 1. `GET /api/player/info/[userId]`
   - **Auth:** Public (some fields might be restricted based on viewer)
   - **Params:** `userId` (path parameter)
   - **Success Conditions:**
     - Returns `200 OK` with player profile data (from `UserAccount` and game `User` table).
     - Sensitive fields (email, IP) only present if viewer is self or admin.
   - **Error Conditions:**
     - Invalid `userId` format -> `400 Bad Request`.
     - Player not found -> `404 Not Found`.

### 2. `GET /api/player/stats/[userId]`
   - **Auth:** Public
   - **Params:** `userId` (path parameter)
   - **Success Conditions:**
     - Returns `200 OK` with player game statistics from `User` table.
   - **Error Conditions:**
     - Invalid `userId` format -> `400 Bad Request`.
     - Player stats not found -> `404 Not Found`.

### 3. `GET /api/player/options`
   - **Auth:** Authenticated User (self)
   - **Success Conditions:**
     - Returns `200 OK` with combined options from `UserOption`, `User.sig`, and `UserAccount` (IMs, newsletter).
   - **Error Conditions:**
     - User not authenticated -> `401 Unauthorized`.

### 4. `POST /api/player/options`
   - **Auth:** Authenticated User (self)
   - **Request Body:** `{ gameOptions?: {...}, signature?: "...", profile?: {...}, theme?: "..." }`
   - **Success Conditions:**
     - Returns `200 OK` with `{ message: 'Options updated successfully.' }`.
     - Relevant fields in `UserOption`, `User`, `UserAccount` are updated.
   - **Error Conditions:**
     - User not authenticated -> `401 Unauthorized`.
     - Invalid input (e.g., signature too long) -> `400 Bad Request`.
     - `UserOption` record not found for update (if attempting to update non-existent options) -> `404 Not Found`.

### 5. `POST /api/player/change-password`
   - **Auth:** Authenticated User (self)
   - **Request Body:** `{ "oldPassword": "...", "newPassword": "...", "newPassword2": "..." }`
   - **Success Conditions:**
     - Returns `200 OK` with `{ message: 'Password changed successfully.' }`.
     - `UserAccount.passwd` is updated with bcrypt hash of new password.
   - **Error Conditions:**
     - User not authenticated -> `401 Unauthorized`.
     - Missing fields -> `400 Bad Request`.
     - New passwords do not match -> `400 Bad Request`.
     - New password too short, same as old, or same as login name -> `400 Bad Request`.
     - Old password incorrect -> `400 Bad Request`.

## Universe, Map, Location

### 1. `GET /api/universe/galaxy-map`
   - **Auth:** Public or Authenticated User (depending on game rules)
   - **Success Conditions:**
     - Returns `200 OK` with `{ stars: [...], universeSize: ..., showWarpNumbers: ... }`.
   - **Error Conditions:**
     - Server error if DB query fails -> `500 Internal Server Error`.

### 2. `GET /api/universe/star-system/[systemId]`
   - **Auth:** Public or Authenticated User
   - **Params:** `systemId` (path parameter)
   - **Success Conditions:**
     - Returns `200 OK` with detailed data for the star system (star info, planets, ports, ships summary, etc.).
   - **Error Conditions:**
     - Invalid `systemId` format -> `400 Bad Request`.
     - System not found -> `404 Not Found`.

### 3. `GET /api/player/location`
   - **Auth:** Authenticated User (self)
   - **Success Conditions:**
     - Returns `200 OK` with player's current location context and system details.
   - **Error Conditions:**
     - User not authenticated -> `401 Unauthorized`.
     - Player location not found (e.g., user not fully set up in game) -> `404 Not Found`.

### 4. `POST /api/player/move`
   - **Auth:** Authenticated User (self)
   - **Request Body:** `{ "targetSystemId": <number> }`
   - **Success Conditions:**
     - Returns `200 OK` with `{ message: "Successfully moved...", newLocation: ..., turns: ..., eventDetails: ... }`.
     - `User.location` and `User.turns` updated in DB.
     - Event logic (if triggered) processed correctly (e.g., shield depletion, scatter).
   - **Error Conditions:**
     - User not authenticated -> `401 Unauthorized`.
     - `targetSystemId` missing or invalid -> `400 Bad Request`.
     - Target system not directly accessible -> `400 Bad Request`.
     - Not enough turns -> `400 Bad Request`.

## Admin Panel APIs (`/api/admin/...`)
   *(All require Admin authentication)*

### 1. `GET /api/admin/users`
   - **Auth:** Admin
   - **Query Params:** `page`, `limit`, `searchLogin`, `searchEmail`, `sortBy`, `sortOrder`
   - **Success Conditions:**
     - Returns `200 OK` with paginated list of `UserAccount`s including `Permission` data.
   - **Error Conditions:**
     - Not admin -> `403 Forbidden`.

### 2. `GET /api/admin/game-variables`
   - **Auth:** Admin
   - **Query Params:** `type` (optional filter)
   - **Success Conditions:**
     - Returns `200 OK` with list of `DbVar` records.
   - **Error Conditions:**
     - Not admin -> `403 Forbidden`.
     - Invalid `type` filter -> `400 Bad Request`.

### 3. `PUT /api/admin/game-variables`
   - **Auth:** Admin
   - **Request Body:** `{ variables: Array<{ name: string, value: string }> }`
   - **Success Conditions:**
     - Returns `200 OK` with `{ message: 'Game variables updated successfully.' }`.
     - `DbVar` records updated in DB.
   - **Error Conditions:**
     - Not admin -> `403 Forbidden`.
     - Invalid request body structure -> `400 Bad Request`.
     - Validation errors for specific variables (out of range, wrong type) -> `400 Bad Request` with error details.

## Combat APIs (`/api/combat/...`)

### 1. `POST /api/combat/fleet-attack`
   - **Auth:** Authenticated User
   - **Request Body:** `{ "attackingFleetId": <number>, "targetFleetId": <number> }`
   - **Success Conditions:**
     - Returns `200 OK` with combat outcome and log.
     - Database updated: ship HPs/shields, destroyed ships removed/marked, user stats updated, empty fleets deleted.
     - Turns deducted from attacker.
   - **Error Conditions:**
     - User not authenticated -> `401 Unauthorized`.
     - Missing IDs, attacking own fleet -> `400 Bad Request`.
     - Fleet not found, not owned by user, fleets not in same system, no ships in fleet -> `403`/`404`/`400`.
     - Not enough turns -> `400 Bad Request`.

*(Add more routes as they are implemented)*
