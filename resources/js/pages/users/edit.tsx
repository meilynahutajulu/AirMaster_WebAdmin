import { User, type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import UsersLayout from '@/layouts/users/layout';
import { userColumns } from '@/components/user-columns';
import { EditForm } from '@/layouts/users/update';
import { buttonVariants } from '@/components/ui/button';
import { User2Icon } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Edit User',
        href: '/users',
    },
];

export default function Users() {
    const { users } = usePage<{ users: User[] }>().props;


    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit User" />

            <UsersLayout>
                <EditForm/>
            </UsersLayout>
        </AppLayout>
    );
}
