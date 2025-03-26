import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import HeadingSmall from '@/components/heading-small';
import AppLayout from '@/layouts/app-layout';
import UsersLayout from '@/layouts/users/layout';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users Data',
        href: '/users/index',
    },
];

export default function Users() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Users Data" />

            <UsersLayout>
                
            </UsersLayout>
        </AppLayout>
    );
}
