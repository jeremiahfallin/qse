# Project Structure

This document outlines the high-level directory structure of the Next.js application and the purpose of key directories.

## Root Directory (`nextjs-app/`)

-   **`.next/`**: (Generated) Next.js build output directory. Not version controlled.
-   **`docs/`**: Contains all project documentation, including API notes, conversion notes, security considerations, and this structure document.
-   **`node_modules/`**: (Generated) Stores all project dependencies. Not version controlled.
-   **`prisma/`**:
    -   `schema.prisma`: Defines the database schema and is used by Prisma ORM to generate the client and manage migrations.
    -   `migrations/`: (Generated) Contains SQL migration files created by Prisma Migrate.
-   **`public/`**: Static assets that are served directly from the root of the application (e.g., images, favicon).
-   **`src/`**: Main application source code.
    -   **`app/`**: Core directory for Next.js App Router.
        -   `api/`: Contains all API route handlers.
            -   `auth/`: Authentication-related API routes (e.g., `[...nextauth].ts`, `signup`).
            -   `admin/`: API routes for the Admin Control Panel.
            -   `player/`: API routes for player-specific data and actions.
            -   `universe/`: API routes for galaxy map and star system data.
            -   `combat/`: API routes for combat simulation.
            -   `forums/`: API routes for game and clan forums.
            -   (Other domain-specific API routes as needed).
        -   `(pages)/`: (Convention for page components if using App Router, e.g., `dashboard/page.tsx`). UI pages for the application.
        -   `layout.tsx`: Main root layout for the application.
        -   `globals.css`: Global stylesheets.
    -   **`components/`**: Shared React UI components used across multiple pages (e.g., buttons, modals, layout elements). Currently a placeholder.
    -   **`config/`**: Application configuration files, like `appConfig.ts`.
    -   **`lib/`**: Core library code, services, and major utilities that are not specific to one part of the UI or a single API route.
        -   `auth/`: Authentication-related utilities, including admin checks (e.g., `adminUtils.ts`).
        -   `combat/`: Core combat simulation logic (`combatService.ts`).
        -   (Placeholder for other core services, e.g., `PrismaClient` instance if not globally instantiated elsewhere, though it's often done in each route/service where needed or via a singleton helper).
    -   **`utils/`**: General utility functions that can be used across the application (client-side or server-side).
        -   `apiRouteNotes.md` (Moved to `docs/api_routes.md`)
        -   `commonUtils.ts`: General helper functions.
        -   `conversionNotes.md` (Moved to `docs/ui_component_conversion_notes.md`)
        -   `planetUtils.ts`: Utilities specific to planet logic.
        -   `securityNotes.md` (Moved to `docs/security_notes.md`)
        -   `securityUtils.ts`: Utilities for security-related tasks (e.g., token generation).
        -   `shipUtils.ts`: Utilities specific to ship logic.
        -   `upgradeUtils.ts`: Utilities for upgrade logic.
        -   `validationUtils.ts`: Input validation functions.
    -   **`themes/`**: (Placeholder) May contain theme definitions or styles if a theming solution like Radix UI Themes (already installed) is used extensively.
-   `.env.example`: Example environment variables. `.env.local` (not version controlled) would contain actual secrets.
-   `jest.config.js`: Jest configuration for unit testing.
-   `next.config.ts` (or `.js`): Next.js application configuration.
-   `package.json`: Project dependencies and scripts.
-   `tsconfig.json`: TypeScript configuration.

## Feature Organization (Conceptual)

-   **Authentication:**
    -   API logic: `src/app/api/auth/`
    -   Client-side hooks/context: (Would be in `src/lib/auth/` or `src/hooks/`)
    -   UI pages: (Would be in `src/app/(pages)/auth/` or similar)
-   **Game Logic:**
    -   Core services: `src/lib/combat/combatService.ts`, etc.
    -   API routes: Grouped by domain under `src/app/api/` (e.g., `player`, `universe`, `combat`, `forums`).
    -   Utilities: `src/utils/` (e.g., `planetUtils.ts`, `shipUtils.ts`).
-   **Admin Panel:**
    -   API logic: `src/app/api/admin/`
    -   UI pages/components: (Would be under `src/app/(pages)/admin/` and `src/components/admin/`)
-   **Shared UI:** `src/components/`
-   **Documentation:** `docs/`

This structure aims to follow Next.js conventions while organizing the application logic by feature and domain.
The `src/lib` directory is intended for more substantial, reusable pieces of code (like services or complex utilities), while `src/utils` is for smaller, general-purpose helper functions.
