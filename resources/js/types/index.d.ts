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
    ATTRIBUTE?: string;
    HUB: string;
    STATUS: string;
    ID_NO: number;
    NAME: string;
    LOA_NO: string;
    LICENSE_NO: string;
    TYPE: string;
    LICENSE_EXPIRY: {
        __datatype__: string;
        value: {
            _seconds: number;
            _nanoseconds: number;
        };
    };
    INSTRUCTOR: string[];
    PRIVILEGES: string[];
    RANK: string;
    EMAIL: string;
    PHOTOURL?: string;
    __collections__?: Record<string, unknown>;
    [key: string]: unknown; // Untuk properti tambahan
}

