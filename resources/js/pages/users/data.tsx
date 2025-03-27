import { User, type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import UsersLayout from '@/layouts/users/layout';
import { userColumns } from '@/components/user-columns';
import { DataTable } from '@/layouts/users/user-table';
import { buttonVariants } from '@/components/ui/button';
import { User2Icon } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users Data',
        href: '/users/index',
    },
];

export default function Users() {
    const { users } = usePage<{ users: User[] }>().props;
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Users Data" />

            <UsersLayout>
                <Link className={buttonVariants({ variant: "default", className: "mb-2" })} href={'users/add'}><User2Icon />Add User</Link>
                <DataTable columns={userColumns} data={users} />
            </UsersLayout>
        </AppLayout>
    );
}
