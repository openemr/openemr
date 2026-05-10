import type { Metadata } from "next";
import { Geist, Geist_Mono } from "next/font/google";
import { cookies, headers } from "next/headers";
import "./globals.css";
import { TopNav } from "@/components/TopNav";
import { getSessionUser } from "@/lib/auth/session";

const geistSans = Geist({
  variable: "--font-geist-sans",
  subsets: ["latin"],
});

const geistMono = Geist_Mono({
  variable: "--font-geist-mono",
  subsets: ["latin"],
});

export const metadata: Metadata = {
  title: "OpenEMR Patient Dashboard",
  description: "Modern Next.js dashboard over the OpenEMR FHIR API.",
};

const SESSION_COOKIE = "dashboard_session";

export default async function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  // Cookie presence is the cheap signal for "show the nav at all".
  // We then resolve the username via getSessionUser only if the cookie
  // exists, to avoid a token-store lookup on every public-page render.
  const store = await cookies();
  const isLoggedIn = store.has(SESSION_COOKIE);
  const session = isLoggedIn ? await getSessionUser() : null;

  // Detect iframe embedding via Sec-Fetch-Dest. When the dashboard is
  // launched from inside OpenEMR's main frame (chooser → Modern click),
  // the request arrives with Sec-Fetch-Dest: iframe and we hide our own
  // TopNav so it doesn't double up with OpenEMR's nav above. For
  // direct-URL access (Sec-Fetch-Dest: document or absent), we render
  // the simplified TopNav as the dashboard's own chrome.
  const hdrs = await headers();
  const inIframe = hdrs.get("sec-fetch-dest") === "iframe";

  return (
    <html lang="en">
      <body
        className={`${geistSans.variable} ${geistMono.variable} antialiased bg-gray-50 text-gray-900 dark:bg-gray-950 dark:text-gray-100`}
      >
        {isLoggedIn && !inIframe && (
          <TopNav active="patients" username={session?.openemrUsername ?? undefined} />
        )}
        {children}
      </body>
    </html>
  );
}
