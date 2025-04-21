import { LucideIcon } from 'lucide-react';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    flash?: {
        success?: string;
        error?: string;
    };
    [key: string]: unknown;
}

export interface User {
    _id: string;
    attribute?: string;
    hub: string;
    status: string;
    id_number: string;
    name: string;
    loa_number: string;
    license_number: string;
    type: string;
    license_expiry: {
        __datatype__: string;
        value: {
            _seconds: number;
            _nanoseconds: number;
        };
    };
    instructor: string[];
    rank: string;
    email: string;
    photo_url?: string;
    __collections__?: Record<string, unknown>;
    [key: string]: unknown; // Untuk properti tambahan
}

