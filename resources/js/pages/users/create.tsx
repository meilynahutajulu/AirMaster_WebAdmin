import { User, type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import UsersLayout from '@/layouts/users/layout';
import { userColumns } from '@/components/user-columns';
import { InputForm } from '@/layouts/users/form';
import { buttonVariants } from '@/components/ui/button';
import { User2Icon } from 'lucide-react';
import { toast } from 'sonner'
import { Toaster } from "@/components/ui/sonner"
import { useEffect } from 'react';
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Add User',
        href: '/users',
    },
];

export default function Users() {
    const { flash } = usePage<SharedData>().props;
    const { users } = usePage<{ users: User[] }>().props;

    useEffect(() => {
        if (flash?.error) {
            toast.error(flash.error);
        }
        if (flash?.success) {
            toast.success(flash.success);
        }
    }, [flash])
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Add User" />
            <Toaster position="bottom-left" richColors />
            <UsersLayout>
                <InputForm />
            </UsersLayout>
        </AppLayout>
    );
}
