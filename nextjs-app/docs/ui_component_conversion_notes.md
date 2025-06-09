/**
 * This file contains notes on PHP functions from the original codebase
 * that are primarily responsible for UI generation or use template engines.
 * These functions should be reimplemented as React components in the Next.js application.
 */

// From includes/common_funcs.inc.php:
// - print_adcode(): Should be an <AdComponent />.
// - print_footer(): Part of the main <Layout /> component's footer section.
// - make_table(), make_table_width(), make_row(), make_hash_row(), quick_row():
//   These are HTML table generation helpers. UI should use React components to build tables
//   (e.g., a generic <DataTable /> component or specific table structures within other components).
// - build_page_list(): Should be a <PaginationControls /> component.

// From includes/print_funcs.inc.php:
// - Create_PageTitleBlock(title, array, width):
//   Should be a <PageTitleBlock title={title} submenuItems={array} /> component.
// - Create_SubMenu(array):
//   Should be a <SubMenu items={array} /> component, likely used by <PageTitleBlock />.
// - Print_FleetName(id, name):
//   Could be <FleetLink id={id} name={name} /> component, possibly opening a modal or navigating to a fleet detail page.
//   Avoid direct JS popups if a better UX can be achieved with Next.js routing/modals.
// - Display_Resources(location, show_darkmatter):
//   Should be a <SystemResourcesDisplay locationId={location} showDarkMatter={show_darkmatter} /> component.
//   This component would fetch its own data server-side or via an API route using Prisma.
// - Print_FleetListing(fid, page_flag=0):
//   A major UI component, e.g., <FleetDetailView fleetId={fid} pageFlag={page_flag} />.
//   It will handle complex data fetching (ships, fleet info, totals) using Prisma and conditional rendering.
// - SystemMessage(text):
//   This functionality should be handled by a global notification/toast system (e.g., using react-toastify or similar)
//   or by passing message props to page components for inline display.
//   The Smarty template `system_message.tpl.html` would be replaced by this React component.

// From includes/ship_shops.inc.php:
// - Main procedural logic for displaying ship shops:
//   This will be a major React component, e.g., <ShipShopView shopLocationType={"earth" | "blackmarket" | "alienshipyard"} userRace={user.race} era={gameEra} contextId={bmrkt_id | asyrd_id | null} />.
//   It will fetch available ships via Prisma based on context, categorize them, and use sub-components to render each ship.
// - add_shiptopurchase(ship):
//   This function formats a single ship's details for display in the shop.
//   It will be a React component, e.g., <ShipShopCard ship={shipData} shopContext={...} />.
//   Popup links for ship info will be replaced by Next.js links to detail pages or modals.
//   Purchase links will trigger API calls.
// - Grab_Player_Shipyard():
//   The logic to determine the current shop type (Earth, Blackmarket, Alien Shipyard) will be part of the
//   page or component context that renders the <ShipShopView />.

// --- Forum Components (from forum.php, forum_clan.php, forum_game.php, posting.php) ---
// These components will interact with the forum API routes.
// The Prisma schema uses GameForumMessage and ClanForumMessage.
// A "thread" is conceptually a GameForumMessage/ClanForumMessage with reply_to = 0 (or specific starter ID).

// - ForumListPage:
//   - Purpose: Displays a list of threads for a given forum type (game or clan).
//   - Props: `forumType: 'game' | 'clan'`, `clanId?: number` (if clan forum).
//   - Fetches data from:
//     - `GET /api/forums/game/threads`
//     - `GET /api/clans/[clanId]/forum/threads`
//   - Displays: List of threads (subject, author, reply count, last post info). Each thread title links to `ThreadViewPage`.
//   - Includes: Link/button to `<CreateThreadForm />`.
//
// - ThreadViewPage:
//   - Purpose: Displays a single thread (the initial post) and its replies, with pagination for replies.
//   - Props: `threadId: number`, `forumType: 'game' | 'clan'`, `clanId?: number` (if clan forum).
//   - Fetches data from:
//     - `GET /api/forums/game/threads/[threadId]`
//     - `GET /api/clans/[clanId]/forum/threads/[threadId]`
//   - Displays: The main/initial post, followed by a paginated list of reply posts using `<ForumPostDisplay />`.
//   - Includes: `<ReplyFormComponent />` at the end.
//
// - ForumPostDisplay:
//   - Purpose: Renders a single forum post (either a thread starter or a reply).
//   - Props: `post: GameForumMessage | ClanForumMessage` (or a unified type), `currentUser: SessionUser`, `forumType: 'game' | 'clan'`.
//   - Displays: Author info (name, avatar if available), post content (parsed from BBCode-like format), timestamp.
//   - Conditional Actions: Shows Edit/Delete buttons if current user is author or moderator (calls respective APIs). Shows "Log to Diary" button (calls API). Shows "Reply" button (pre-fills `ReplyFormComponent`).
//
// - CreateThreadForm:
//   - Purpose: Form to create a new thread.
//   - Props: `forumType: 'game' | 'clan'`, `clanId?: number`.
//   - UI: Input for subject, textarea for post content (ideally a `<BBCodeEditorComponent />`).
//   - Submits to:
//     - `POST /api/forums/game/threads`
//     - `POST /api/clans/[clanId]/forum/threads`
//
// - ReplyFormComponent:
//   - Purpose: Form to reply to an existing thread.
//   - Props: `threadId: number`, `forumType: 'game' | 'clan'`, `clanId?: number`, `replyToSubject?: string`.
//   - UI: Textarea for post content (`<BBCodeEditorComponent />`). Subject might be pre-filled "Re: [original_subject]".
//   - Submits to:
//     - `POST /api/forums/game/threads/[threadId]/reply`
//     - `POST /api/clans/[clanId]/forum/threads/[threadId]/reply`
//
// - BBCodeEditorComponent:
//   - Replaces the JavaScript BBCode editor logic found in `posting.php` and the helper function `RequestMessage_GameForum`.
//   - Provides UI buttons for bold, italic, underline, quote, code, list, img, url, color, size.
//   - Interacts with a textarea or a more advanced rich text editor component.

// From includes/clan_funcs.inc.php:
// - Print_ClanDetailsFull():
//   Generates a large HTML block for displaying comprehensive clan statistics and details.
//   This will be a major React component, e.g., <ClanProfileView clanData={...} clanStats={...} />.
//   It relies on pre-fetched global variables in PHP; in Next.js, the necessary data
//   (clan details, aggregated member stats, planet/ship counts, etc.) will be fetched
//   server-side using Prisma and passed as props.
//   Uses helper functions `calc_perc()` (needs to be found and ported) and `RM_Zero()` (ported as `ensureNumberOrZero`).

// From includes/combat_funcs.inc.php:
// - Include_ContinueConfirm(target), Include_FirstConfirm(target):
//   These generate HTML forms for initiating or continuing combat.
//   In Next.js, this UI would be part of a combat interface component.
//   For example, <CombatInterface targetShipId={targetShipId} combatLog={...} />
//   would display combat status and provide "Attack/Continue" and "Disengage" buttons.
//   These buttons would trigger calls to the relevant combat API routes.

// From includes/fleet_funcs.inc.php:
// - Ask_CreateNewFleet():
//   This function generates an HTML form to get a new fleet name when a ship
//   purchase/transfer/claim cannot fit into existing fleets.
//   In Next.js, this would be a React component, e.g., <CreateNewFleetForm pendingActionData={...} />.
//   This form would be displayed conditionally based on an API response indicating fleet limits were met.
//   It would submit to an API route like POST /api/fleets (noted in apiRouteNotes.ts).
//   The `pendingActionData` prop would carry context about the original action (e.g., ship purchase details)
//   to be re-attempted after successful fleet creation.

// From includes/location_funcs.inc.php:
// - print_link(link_num), print_link2(link_num):
//   Should be a <StarLink toStarId={link_num} /> component. Fetches star name for display.
// - SystemInfo():
//   Major UI component, e.g., <SystemOverviewCard /> or similar.
//   Displays resources, starports, black markets, observatories, homeworlds in the current system.
//   Requires significant data fetching (current system details, facilities, user ship for mining status).
// - PlanetInfo(planets):
//   Should be a <PlanetCard planet={planetData} /> component, used within a list of planets.
//   Displays individual planet details and actions (land, attack, etc.).
// - UserShip_FullList(ships), UserShip_SummaryList(fleet):
//   These would be part of a <ShipListLocationBased /> component, displaying user's ships/fleets.
// - EnemyShip_FullList(ships), EnemyShip_SummaryList(fleet, enemy):
//   Part of an <EnemyShipListLocationBased /> component.
// - Display_News(show_news):
//   Should be <RecentNewsWidget /> or similar, fetching and displaying latest news headlines.
// - Print_FleetLinks():
//   Should be a <FleetLinkForm /> component for managing fleet links.

// From includes/template_funcs.inc.php:
// - Player_Status(user):
//   This is a large function rendering the left sidebar. It should become a major React component,
//   e.g., <PlayerStatusSidebar user={user} />.
//   It involves fetching various pieces of data (game info, online counts, user stats, ship info)
//   which the React component would handle via server-side data fetching (getServerSideProps / RSCs with Prisma).
//   The menu links within it would use Next.js <Link /> components.
//   The Smarty assignments ($tpl->assign) are replaced by React state/props and rendering logic.

// General Note on Template Engines (Smarty):
// All Smarty templates (.tpl files) will be replaced by React components.
// Logic within PHP functions that prepares data for Smarty ($tpl->assign(...))
// will be adapted into data fetching for React components or prop preparation.

// --- Admin Panel Components (from admincp/* files) ---

// - AdminLayoutComponent:
//   - Purpose: Provides the overall structure for the admin panel (e.g., header, sidebar navigation, main content area).
//   - Contains: Navigation links to different admin sections.
//   - Handles: Admin authentication/authorization context, ensuring only admins can access.
//
// - AdminDashboardPageComponent:
//   - Purpose: Displays the main admin dashboard (contents of `admincp/index.php`).
//   - Fetches data from: `GET /api/admin/dashboard-summary`.
//   - Displays: Summary statistics, game status toggles (pause, rejoin delay).
//   - Actions: Triggers API calls like `POST /api/admin/game/pause`, `POST /api/admin/settings/toggle-rejoin-delay`.
//
// - AdminUserManagementPageComponent:
//   - Purpose: For listing, viewing, editing, and potentially deleting users.
//   - Fetches data from: `GET /api/admin/users` (with pagination, search, sort).
//   - Displays: A table or list of users. Links to view/edit individual users.
//   - Components:
//     - `<UserListTable users={...} />`
//     - `<UserDetailsView userId={...} />` (fetches from `GET /api/admin/users/[userId]`)
//     - `<UserEditForm userData={...} />` (submits to `PUT /api/admin/users/[userId]`)
//
// - AdminGameVariablesPageComponent:
//   - Purpose: For viewing and editing game variables stored in the `DbVar` table.
//   - Fetches data from: `GET /api/admin/game-variables`.
//   - Displays: A list or form with all game variables, their descriptions, and current values.
//   - Actions: Allows editing values and submitting all changes via `PUT /api/admin/game-variables`.
//
// - Other Admin Page Components (based on other files in admincp/):
//   - As more admin PHP files are analyzed (e.g., `admin_planets.php`, `admin_clans.php`),
//     corresponding page components and CRUD interface components will be documented here.

// From main_map.php and star_map.php (Universe/Map display):
// - `main_map.php` (Galaxy Map Display):
//   This PHP script generates a PNG image of the galaxy map.
//   In Next.js, this will be replaced by a React component: <GalaxyMapComponent mapData={data_from_api} />.
//   This component will fetch data from `GET /api/universe/galaxy-map` and use a client-side
//   rendering library (e.g., PixiJS, D3.js, HTML5 Canvas/SVG) to draw the interactive map.
// - `star_map.php` (Minimap / Local System View for a specific star):
//   This PHP script generates a smaller PNG by cropping a pre-rendered full map.
//   In Next.js, this could be part of the <GalaxyMapComponent /> functionality (zooming/panning to a region)
//   or a separate <StarSystemMiniMapComponent systemId={...} /> that either uses the full client-side
//   map data or fetches specific regional data from a new API endpoint if needed.
//   The primary data source remains `GET /api/universe/galaxy-map`.

// From location.php (Display aspects of current star system):
// - The main view of `location.php` (displaying current system details, planets, ships, ports, etc.)
//   will be a major React component, e.g., <StarSystemViewComponent systemId={currentSystemId} /> or part of a
//   <PlayerLocationDashboardComponent />.
//   This component will primarily use data fetched from `GET /api/universe/star-system/[systemId]`
//   (for general system details) and `GET /api/player/location` (for player-specific context and current system ID).
//   It will integrate various sub-components previously noted:
//     - `<SystemResourcesDisplay />` (from `Display_Resources` in `print_funcs.inc.php`)
//     - `<SystemOverviewCard />` (from `SystemInfo` in `location_funcs.inc.php`)
//     - Lists of planets (using `<PlanetCard />`)
//     - Lists of player's and other players' ships/fleets (using `<ShipListLocationBased />`, `<EnemyShipListLocationBased />`)
//     - Links to facilities (starports, black markets, etc.)
//     - Navigation links to adjacent systems (using `<StarLink />`)
//     - Triggers for actions like mining (`<MiningInterfaceComponent />`) or fleet link management (`<FleetLinkForm />`).

// From player_info.php:
// - Main display logic:
//   Should be a <PlayerProfilePage userId={targetUserId} /> component.
//   This component will fetch data via `/api/player/profile/[userId]` and display:
//     - User details (global and game-specific).
//     - Lists of planets and ships (potentially with sorting options).
//     - Links for actions like messaging, transferring (if viewing self or conditions allow).
//     - Admin actions like "Retire Player" if viewer is admin.
// - Self-destruct ships UI:
//   Part of <PlayerProfilePage /> or a sub-component, e.g., <SelfDestructShipsForm ships={playerShips} />.
//   Handles selection and confirmation, then calls `POST /api/ships/self-destruct`.
// - Account history display:
//   Could be part of <PlayerProfilePage /> or a separate <PlayerHistoryView userId={targetUserId} /> component.
//   Fetches data via `GET /api/player/history/[userId]`.
// - Transfer cash/tech forms:
//   UI elements within <PlayerProfilePage /> (if viewing another player) or a dedicated <TransferResourcesPage />.
//   Submits to `POST /api/player/transfer`.

// From player_stat.php:
// - Main display logic:
//   Should be a <PlayerRankingPage initialSortBy={sortBy} initialFilter={filter} /> component.
//   Fetches data via `GET /api/stats/ranking` with query parameters for sorting/filtering.
//   Displays the ranked list of players in a table.

// From options.php:
// - Main options display and forms:
//   Should be a <PlayerOptionsPage /> component.
//   This page would be a container for several forms/sections:
//     - <ChangePlayerInfoForm userData={...} /> (for signature, IM details - submits to `POST /api/player/info`).
//     - <GameOptionsForm userOptions={...} optionDefinitions={...} /> (for game-specific boolean/numerical options - submits to `POST /api/player/options`).
//       `optionDefinitions` would come from `OptionList` table data.
//     - <ChangePasswordForm /> (submits to `POST /api/player/change-password`).
//     - <ThemeSelector currentTheme={...} availableThemes={...} /> (submits theme choice to `POST /api/player/options`).
//   Fetches initial data via `GET /api/player/options`.

// From includes/random_events.inc.php:
// - random_event_checker() (HTML part for newbie black hole warning):
//   This specific UI warning (including navigation links) should be a React component/view,
//   e.g., <NewbieBlackHoleWarning star={starData} autowarpPath={autowarpPath} />.
//   Data would be provided by the server after a move action.
// - black_hole() (HTML generation part):
//   The presentation of the black hole event outcome (ship damage, new location)
//   should be a React component/view, e.g., <BlackHoleEventResult eventData={...} />.

// From includes/ship_loading.inc.php:
// - Load_Resource_Form(action, resource, file):
//   Should be a React component, e.g. <ResourceTransferForm type="load" resourceType={resource} availableSources={...} />.
//   Handles UI for selecting ships (current, fleet, all in system) and amount/options for loading resources.
// - Unload_Resource_Form(action, resource, file):
//   Should be a React component, e.g. <ResourceTransferForm type="unload" resourceType={resource} destinations={...} />.
//   Handles UI for selecting ships and amount/options for unloading/selling resources.

// From includes/nocache.inc.php:
// - This file sends HTTP headers to prevent client-side caching.
// - In Next.js, this is handled by:
//   - Default behavior of `getServerSideProps` (pages are dynamically rendered).
//   - Setting headers explicitly in API routes or `getServerSideProps` if needed:
//     res.setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
//     res.setHeader('Pragma', 'no-cache');
//     res.setHeader('Expires', '0');
//   - Next.js caching strategies: https://nextjs.org/docs/app/building-your-application/caching
// - No direct porting of the PHP file is needed.

// Notes on includes/location.inc.php:
// This file contains procedural logic for handling actions, not just functions.
// This logic needs to be mapped to API Route Handlers in Next.js.
// - Retire player (retire, sure, what_to_do, leader_id logic):
//   API Route: POST /api/player/retire
//   Request Body: { sure: boolean, clanAction?: 'disband' | 'assign', newLeaderId?: number }
// - Change fleet links (chng_lnks, lnks logic):
//   API Route: POST /api/fleet/links
//   Request Body: { commandFleetId: number, linkedFleetIds: number[] }
// - Command different ship (command GET param - deprecated in PHP):
//   Likely superseded by new_command.
// - Command different fleet (new_command GET param):
//   API Route: POST /api/player/command-fleet
//   Request Body: { fleetId: number }
// - Toggle Ramscoop (ramfleet GET param):
//   API Route: POST /api/fleet/toggle-ramscoop
//   Request Body: { fleetId: number }
// - SS0 Bug Fix (sszero GET param):
//   This is likely a one-off admin/debug function. Could be a secured admin API endpoint if still needed.
//   API Route: POST /api/admin/fix-ss0 (requires admin auth)
//   Request Body: { userId: number }
// Logic within PHP functions that prepares data for Smarty ($tpl->assign(...))
// will be adapted into data fetching for React components or prop preparation.
