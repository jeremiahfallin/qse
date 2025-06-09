// Application Configuration

// General application settings
export const appConfig = {
  serverName: process.env.NEXT_PUBLIC_SERVER_NAME || "My Quantum Star Game",
  codeBase: process.env.NEXT_PUBLIC_CODE_BASE || "Quantum Star SE Next.js",
  forumLink: process.env.NEXT_PUBLIC_FORUM_LINK || "http://example.com/forum",
  adminEmail: process.env.ADMIN_EMAIL || "admin@localhost",

  // The adminPass from the PHP config was used for CRON jobs.
  // For a Node.js/Next.js app, API endpoints should be secured properly (e.g., with API keys or other auth mechanisms).
  // This is a placeholder and should be replaced with a secure method if needed for scripts.
  adminScriptPassword: process.env.ADMIN_SCRIPT_PASSWORD || "default_cron_password",

  // Operational flags from the original config
  // gameInstalled: process.env.GAME_INSTALLED === "true", // Controls setup/maintenance modes
  // Note: DATABASE_PERSISTENT is not directly applicable as Prisma manages connections.
  // sendmailValidationRequired: process.env.SENDMAIL_VALIDATION_REQUIRED === "true",

  // Paths - these are mostly irrelevant in Next.js context or handled differently
  // gameroot, map_path, sql_path from the original config are not used here.
  // Next.js has its own conventions for public assets and server-side files.

  // Features - example how you might control features via env vars
  // enableFeatureX: process.env.NEXT_PUBLIC_ENABLE_FEATURE_X === "true",
};

// It's good practice to validate critical environment variables at startup.
// For example, you could add checks here to ensure necessary env vars are set.

if (typeof window === "undefined") {
  // This code runs on the server-side
  if (!process.env.DATABASE_URL) {
    console.warn(
      "WARNING: DATABASE_URL environment variable is not set. Prisma will not be able to connect to the database."
    );
  }
  // Add other critical server-side env var checks here
}

export default appConfig;
