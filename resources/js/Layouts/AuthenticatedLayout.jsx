import ApplicationLogo from '@/Components/Core/ApplicationLogo.jsx';
import Dropdown from '@/Components/Core/Dropdown.jsx';
import NavLink from '@/Components/Core/NavLink.jsx';
import ResponsiveNavLink from '@/Components/Core/ResponsiveNavLink.jsx';
import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import Navbar from "@/Components/App/Navbar.jsx";

export default function AuthenticatedLayout({ header, children }) {
    const user = usePage().props.auth.user;

    const [showingNavigationDropdown, setShowingNavigationDropdown] =
        useState(false);

    return (
        <div className="min-h-screen bg-gray-100">
            <Navbar />
            {header && (
                <header className="bg-white shadow">
                    <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        {header}
                    </div>
                </header>
            )}

            <main>{children}</main>
        </div>
    );
}
