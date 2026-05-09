import type { Metadata } from "next";
import { Geist, Geist_Mono } from "next/font/google";
import { cookies } from "next/headers";
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

  return (
    <html lang="en">
      <body
        className={`${geistSans.variable} ${geistMono.variable} antialiased bg-gray-50 text-gray-900 dark:bg-gray-950 dark:text-gray-100`}
      >
        {isLoggedIn && (
          <TopNav active="patients" username={session?.openemrUsername ?? undefined} />
        )}
        {children}
      </body>
    </html>
  );
}
